<?php

namespace App\Services;

use App\Models\ApplicationAtsScore;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\SortingCriterion;
use App\Models\StageCriterion;
use Illuminate\Support\Facades\Log;

class AtsScoringService
{
    /**
     * Score an application for a specific sorting stage.
     */
    public function scoreApplication(JobApplication $application, PipelineStage $stage): void
    {
        if ($stage->type !== 'sorting') {
            return;
        }

        $parseRecord = $application->resumeParses()->latest()->first();
        if (! $parseRecord || $parseRecord->status !== 'done' || empty($parseRecord->parsed_json)) {
            Log::warning("Cannot score Application {$application->id}: Missing or incomplete resume parse.");
            $application->update(['ats_state' => 'failed']);

            return;
        }

        $sortingCriteria = SortingCriterion::where('pipeline_stage_id', $stage->id)
            ->where('is_active', true)
            ->get();

        $stageCriteria = StageCriterion::where('pipeline_stage_id', $stage->id)->get();

        if ($sortingCriteria->isEmpty() && $stageCriteria->isEmpty()) {
            $application->update(['ats_state' => 'scored']);

            return;
        }

        $parsedData = $parseRecord->parsed_json;
        $totalScore = 0;
        $criteriaBreakdown = [];
        $failedRequired = false;

        // Process SortingCriterion (Complex matching)
        foreach ($sortingCriteria as $criterion) {
            $result = $this->evaluateSortingCriterion($criterion, $parsedData);
            $this->addBreakdown($criteriaBreakdown, $criterion, $result);
            $totalScore += $result['score'];

            if ($criterion->is_required && ! $result['matched']) {
                $failedRequired = true;
            }
        }

        // Process StageCriterion (Simple keyword matching)
        foreach ($stageCriteria as $criterion) {
            $result = $this->evaluateStageCriterion($criterion, $parsedData);
            $this->addBreakdown($criteriaBreakdown, $criterion, $result);
            $totalScore += $result['score'];
        }

        $passingMarks = (float) ($stage->config['passing_marks'] ?? 0);
        $passed = ! $failedRequired && ($totalScore >= $passingMarks);
        $passReason = $passed ? 'Met passing marks and all required criteria.' : 'Failed to meet required criteria or passing marks.';

        if ($failedRequired) {
            $passReason = 'Failed one or more strictly required criteria.';
        }

        // Save Score
        $scoreRecord = ApplicationAtsScore::updateOrCreate(
            [
                'job_application_id' => $application->id,
                'pipeline_stage_id' => $stage->id,
            ],
            [
                'job_post_id' => $application->job_post_id,
                'total_score' => $totalScore,
                'passed' => $passed,
                'pass_reason' => $passReason,
                'criteria_breakdown' => $criteriaBreakdown,
                'scoring_version' => '1.0.0',
                'scored_at' => now(),
            ]
        );

        // Update Job Application cache fields
        $application->update([
            'ats_state' => 'scored',
            'ats_score_cached' => $totalScore,
            'ats_passed_cached' => $passed,
        ]);
    }

    /**
     * Evaluate a single criterion against parsed resume data.
     */
    protected function addBreakdown(array &$breakdown, $criterion, array $result): void
    {
        $breakdown[] = array_merge($criterion->toArray(), [
            'matched' => $result['matched'],
            'awarded_score' => $result['score'],
            'explanation' => $result['explanation'],
            'criterion_source' => get_class($criterion),
        ]);
    }

    /**
     * Evaluate a SortingCriterion (Complex).
     */
    protected function evaluateSortingCriterion(SortingCriterion $criterion, array $parsedData): array
    {
        $type = strtolower($criterion->type);
        $operator = strtolower($criterion->operator);
        $targetValue = strtolower($criterion->value);
        $weight = (float) $criterion->weight;

        $matched = false;
        $explanation = "Did not meet {$type} condition.";

        switch ($type) {
            case 'experience':
                $applicantYears = (float) ($parsedData['inferred_experience_years'] ?? 0);
                $targetYears = (float) $targetValue;

                if ($operator === 'gte' && $applicantYears >= $targetYears) {
                    $matched = true;
                } elseif ($operator === 'equals' && $applicantYears == $targetYears) {
                    $matched = true;
                }

                if ($matched) {
                    $explanation = "Candidate has {$applicantYears} years of experience, meeting requirement.";
                } else {
                    $explanation = "Candidate has {$applicantYears} years of experience. Required: {$operator} {$targetYears}.";
                }
                break;

            case 'skill':
                $skills = array_map('strtolower', $parsedData['skills'] ?? []);

                if (in_array($targetValue, $skills)) {
                    $matched = true;
                    $explanation = "Skill '{$targetValue}' was found in the resume.";
                } else {
                    $explanation = "Skill '{$targetValue}' was not found.";
                }
                break;

            case 'title':
                $titles = array_map('strtolower', $parsedData['job_titles'] ?? []);

                if ($operator === 'contains') {
                    foreach ($titles as $t) {
                        if (str_contains($t, $targetValue)) {
                            $matched = true;
                            $explanation = "Job title containing '{$targetValue}' was found.";
                            break;
                        }
                    }
                } elseif ($operator === 'equals' && in_array($targetValue, $titles)) {
                    $matched = true;
                    $explanation = "Exact job title '{$targetValue}' was found.";
                }
                break;

            default:
                // Fallback text-based matching if custom type
                $rawText = strtolower($parsedData['raw_normalized'] ?? '');
                if (str_contains($rawText, $targetValue)) {
                    $matched = true;
                    $explanation = "Found keyword metadata '{$targetValue}'.";
                }
                break;
        }

        return [
            'matched' => $matched,
            'score' => $matched ? $weight : 0,
            'explanation' => $explanation,
        ];
    }

    /**
     * Evaluate a StageCriterion (Simple keyword).
     */
    protected function evaluateStageCriterion(StageCriterion $criterion, array $parsedData): array
    {
        $target = strtolower($criterion->label);
        $weight = (float) $criterion->weight;
        $rawText = strtolower($parsedData['raw_normalized'] ?? '');
        $skills = array_map('strtolower', $parsedData['skills'] ?? []);
        $titles = array_map('strtolower', $parsedData['job_titles'] ?? []);

        $matched = false;
        $explanation = "Keyword '{$target}' not found.";

        if (str_contains($rawText, $target) || in_array($target, $skills) || in_array($target, $titles)) {
            $matched = true;
            $explanation = "Keyword '{$target}' matched in resume data.";
        }

        return [
            'matched' => $matched,
            'score' => $matched ? $weight : 0,
            'explanation' => $explanation,
        ];
    }
}

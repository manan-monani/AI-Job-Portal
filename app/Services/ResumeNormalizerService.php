<?php

namespace App\Services;

class ResumeNormalizerService
{
    /**
     * Map of common synonyms to their normalized form.
     */
    protected array $synonymMap = [
        'nodejs' => 'node.js',
        'reactjs' => 'react',
        'vuejs' => 'vue',
        'vue.js' => 'vue',
        'javascript' => 'js',
        'typescript' => 'ts',
        'aws' => 'amazon web services',
        'gcp' => 'google cloud platform',
        'k8s' => 'kubernetes',
    ];

    /**
     * Normalize the extracted resume text into structured JSON data.
     */
    public function parse(string $rawText): array
    {
        $normalizedText = $this->normalizeText($rawText);

        return [
            'raw_normalized' => $normalizedText,
            'skills' => $this->extractSkills($normalizedText),
            'inferred_experience_years' => $this->inferredExperienceYears($normalizedText),
            'job_titles' => $this->extractJobTitles($normalizedText),
            'locations' => [], // Advanced NLP required for robust location extraction, left blank for basic implementation
        ];
    }

    /**
     * Lowercase, trim, and clean punctuation.
     */
    protected function normalizeText(string $text): string
    {
        $text = strtolower($text);

        // Remove special characters except common ones used in IT like dot or plus (e.g., C++, Node.js)
        $text = preg_replace('/[^a-z0-9\s\.\+]/', ' ', $text);

        // Remove extra spaces
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Extract potential skills from the text.
     * This is a naïve approach matching against common tech keywords.
     * In a production environment, this would use a robust NLP tagging service or a large dictionary.
     */
    protected function extractSkills(string $text): array
    {
        $commonSkills = [
            'php', 'laravel', 'vue', 'react', 'node.js', 'js', 'ts', 'java', 'python',
            'docker', 'kubernetes', 'aws', 'mysql', 'postgresql', 'mongodb', 'redis',
            'html', 'css', 'tailwind', 'bootstrap', 'git', 'ci/cd', 'linux',
        ];

        $foundSkills = [];

        // Apply synonym mapping strictly on exact word boundaries
        foreach ($this->synonymMap as $synonym => $normalized) {
            if (preg_match("/\b".preg_quote($synonym, '/')."\b/", $text)) {
                $foundSkills[] = $normalized;
                // Replace the synonym with the normalized term for subsequent searching
                $text = preg_replace("/\b".preg_quote($synonym, '/')."\b/", $normalized, $text);
            }
        }

        foreach ($commonSkills as $skill) {
            if (preg_match("/\b".preg_quote($skill, '/')."\b/", $text)) {
                $foundSkills[] = $skill;
            }
        }

        return array_values(array_unique($foundSkills));
    }

    /**
     * Extremely naïve experience year inference.
     * Looks for "X years", "X+ years", etc.
     */
    protected function inferredExperienceYears(string $text): float
    {
        // Matches "5 years", "10+ years", "3.5 years"
        if (preg_match_all('/(\d+(?:\.\d+)?)\+?\s*years?/i', $text, $matches)) {
            $years = array_map('floatval', $matches[1]);
            // A simple heuristic is taking the maximum mentioned "years" if it's within a reasonable work limit
            $maxYears = max($years);

            return $maxYears < 40 ? $maxYears : 0;
        }

        return 0;
    }

    /**
     * Extremely naïve job title extraction.
     */
    protected function extractJobTitles(string $text): array
    {
        $titles = ['developer', 'engineer', 'manager', 'lead', 'architect', 'designer', 'analyst', 'administrator'];
        $found = [];

        foreach ($titles as $title) {
            if (str_contains($text, $title)) {
                $found[] = $title;
            }
        }

        return $found;
    }
}

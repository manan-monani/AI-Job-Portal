<?php

namespace App\Services;

use App\Models\PipelineStage;

class StageEmailService
{
    /**
     * Get the list of available tokens for a stage based on type/subtype.
     *
     * @return array<int, array{token: string, label: string}>
     */
    public function getAvailableTokens(PipelineStage $stage): array
    {
        $common = [
            ['token' => '{{candidate_name}}', 'label' => 'Candidate Name'],
            ['token' => '{{job_title}}', 'label' => 'Job Title'],
            ['token' => '{{company_name}}', 'label' => 'Company Name'],
            ['token' => '{{stage_title}}', 'label' => 'Stage Title'],
        ];

        if ($stage->type === 'assessment') {
            $extra = [
                ['token' => '{{assessment_type}}', 'label' => 'Assessment Type (Task/Exam/Quiz)'],
                ['token' => '{{deadline}}', 'label' => 'Deadline / Due Date'],
                ['token' => '{{duration}}', 'label' => 'Duration'],
                ['token' => '{{total_marks}}', 'label' => 'Total Marks'],
                ['token' => '{{passing_marks}}', 'label' => 'Passing Marks'],
                ['token' => '{{assessment_url}}', 'label' => 'Assessment Join URL (Button Link)'],
                ['token' => '{{instructions}}', 'label' => 'Instructions'],
            ];
        } elseif ($stage->type === 'interview') {
            $extra = [
                ['token' => '{{interview_type}}', 'label' => 'Interview Type (Onsite/Phone/Online)'],
                ['token' => '{{scheduled_at}}', 'label' => 'Interview Date & Time'],
                ['token' => '{{duration}}', 'label' => 'Duration'],
                ['token' => '{{location}}', 'label' => 'Location (Onsite)'],
                ['token' => '{{phone_details}}', 'label' => 'Phone Details'],
                ['token' => '{{meeting_link}}', 'label' => 'Meeting Link'],
                ['token' => '{{meeting_platform}}', 'label' => 'Meeting Platform'],
                ['token' => '{{instructions}}', 'label' => 'Instructions'],
            ];
        } else {
            $extra = [
                ['token' => '{{instructions}}', 'label' => 'Instructions'],
            ];
        }

        return array_merge($common, $extra);
    }

    /**
     * Generate a default email template for a stage based on type + subtype.
     *
     * @return array{subject: string, body: string}
     */
    public function generateDefaultTemplate(PipelineStage $stage): array
    {
        $type = $stage->type;
        $subtype = $stage->subtype;

        return match ($type) {
            'assessment' => $this->generateAssessmentTemplate($subtype),
            'interview' => $this->generateInterviewTemplate($subtype),
            default => $this->generateGenericTemplate(),
        };
    }

    /**
     * Replace token placeholders with actual data.
     *
     * @param  array<string, string>  $data
     */
    public function renderTemplate(string $subject, string $body, array $data): array
    {
        foreach ($data as $token => $value) {
            $placeholder = '{{'.$token.'}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return ['subject' => $subject, 'body' => $body];
    }

    /**
     * Build sample data for preview rendering.
     *
     * @return array<string, string>
     */
    public function getSampleData(PipelineStage $stage): array
    {
        $job = $stage->jobPost;
        $config = $stage->config ?? [];
        $companyName = business_config('business_name') ?? 'Your Company';

        $data = [
            'candidate_name' => 'John Doe',
            'job_title' => $job->title ?? 'Software Engineer',
            'company_name' => $companyName,
            'stage_title' => $stage->title,
            'instructions' => $stage->instructions ?? '',
        ];

        if ($stage->type === 'assessment') {
            $subtypeLabels = ['task' => 'Task', 'exam' => 'Exam', 'quiz' => 'Quiz', 'online_quiz' => 'Quiz'];
            $data['assessment_type'] = $subtypeLabels[$stage->subtype] ?? 'Assessment';
            $data['deadline'] = $config['due_date'] ?? 'TBD';
            $data['duration'] = $config['duration'] ? $config['duration'].' minutes' : 'N/A';
            $data['total_marks'] = (string) ($config['total_marks'] ?? 'N/A');
            $data['passing_marks'] = (string) ($config['passing_marks'] ?? 'N/A');
            $data['assessment_url'] = '#'; // Sample URL for preview
        }

        if ($stage->type === 'interview') {
            $subtypeLabels = ['onsite' => 'Onsite Interview', 'phone' => 'Phone Interview', 'online' => 'Online Meeting'];
            $data['interview_type'] = $subtypeLabels[$stage->subtype] ?? 'Interview';
            $data['scheduled_at'] = $config['scheduled_at'] ?? 'TBD';
            $data['duration'] = $config['duration'] ? $config['duration'].' minutes' : 'N/A';
            $data['location'] = $config['location'] ?? 'N/A';
            $data['phone_details'] = $config['phone_details'] ?? 'N/A';
            $data['meeting_link'] = $config['meeting_link'] ?? 'N/A';
            $data['meeting_platform'] = $config['meeting_platform'] ?? 'N/A';
        }

        return $data;
    }

    /**
     * @return array{subject: string, body: string}
     */
    private function generateAssessmentTemplate(?string $subtype): array
    {
        $subtypeLabel = match ($subtype) {
            'task' => 'Task',
            'exam' => 'Exam',
            'quiz' => 'Quiz',
            default => 'Assessment',
        };

        $subject = '{{company_name}} — {{assessment_type}} for {{job_title}}';

        $body = "Dear {{candidate_name}},\n\n";
        $body .= "Thank you for your interest in the {{job_title}} position at {{company_name}}.\n\n";
        $body .= "We are pleased to invite you to complete the following {$subtypeLabel} as part of your evaluation:\n\n";
        $body .= "**{{stage_title}}**\n\n";

        if ($subtype === 'exam' || $subtype === 'quiz') {
            $body .= "• Total Marks: {{total_marks}}\n";
            $body .= "• Passing Marks: {{passing_marks}}\n";
            $body .= "• Duration: {{duration}}\n";
        } elseif ($subtype === 'task') {
            $body .= "• Duration: {{duration}}\n";
        }

        $body .= "• Deadline: {{deadline}}\n\n";
        $body .= "**Take Assessment:**\n{{assessment_url}}\n\n";
        $body .= "**Instructions:**\n{{instructions}}\n\n";
        $body .= "Please complete this before the deadline. If you have any questions, feel free to reply to this email.\n\n";
        $body .= "Best regards,\n{{company_name}} Hiring Team";

        return ['subject' => $subject, 'body' => $body];
    }

    /**
     * @return array{subject: string, body: string}
     */
    private function generateInterviewTemplate(?string $subtype): array
    {
        $subject = '{{company_name}} — {{interview_type}} Invitation for {{job_title}}';

        $body = "Dear {{candidate_name}},\n\n";
        $body .= "We are pleased to invite you for a {{interview_type}} for the {{job_title}} position at {{company_name}}.\n\n";
        $body .= "**{{stage_title}}**\n\n";
        $body .= "• Date & Time: {{scheduled_at}}\n";
        $body .= "• Duration: {{duration}}\n";

        if ($subtype === 'onsite') {
            $body .= "• Location: {{location}}\n\n";
            $body .= "Please arrive 10 minutes early. Bring a valid photo ID.\n\n";
        } elseif ($subtype === 'phone') {
            $body .= "• Phone: {{phone_details}}\n\n";
            $body .= "Please ensure you are available at the number above. We will call you at the scheduled time.\n\n";
        } elseif ($subtype === 'online') {
            $body .= "• Platform: {{meeting_platform}}\n";
            $body .= "• Meeting Link: {{meeting_link}}\n\n";
            $body .= "Please join the meeting 5 minutes before the scheduled time. Ensure a stable internet connection.\n\n";
        }

        $body .= "**Instructions:**\n{{instructions}}\n\n";
        $body .= "If you need to reschedule, please reply to this email as soon as possible.\n\n";
        $body .= "Best regards,\n{{company_name}} Hiring Team";

        return ['subject' => $subject, 'body' => $body];
    }

    /**
     * @return array{subject: string, body: string}
     */
    private function generateGenericTemplate(): array
    {
        $subject = '{{company_name}} — Update on your application for {{job_title}}';

        $body = "Dear {{candidate_name}},\n\n";
        $body .= "Thank you for applying to the {{job_title}} position at {{company_name}}.\n\n";
        $body .= "**{{stage_title}}**\n\n";
        $body .= "{{instructions}}\n\n";
        $body .= "Best regards,\n{{company_name}} Hiring Team";

        return ['subject' => $subject, 'body' => $body];
    }
}

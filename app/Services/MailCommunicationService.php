<?php

namespace App\Services;

use App\Mail\PipelineMail;
use App\Models\JobApplication;
use App\Models\MailLog;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MailCommunicationService
{
    public function __construct(protected StageEmailService $stageEmailService) {}

    /**
     * Send a stage-transition email using the stage's configured template.
     *
     * @return array{success: bool, message: string, log_id: ?int}
     */
    public function sendStageEmail(
        JobApplication $application,
        PipelineStage $stage,
        string $mode = 'auto',
        ?int $adminUserId = null,
    ): array {
        // 1. Check mail ON/OFF
        if (! $this->isMailEnabled()) {
            return $this->logAndReturn($application, $stage, $mode, $adminUserId, 'skipped', 'Mail sending is disabled.');
        }

        // 2. Fetch template — use default if none configured
        $stage->loadMissing('emailTemplate');
        $template = $stage->emailTemplate;

        if (! $template) {
            // Generate a default template based on stage type
            $default = $this->getDefaultTemplate($stage);
            $subject = $default['subject'];
            $body = $default['body'];
        } else {
            $subject = $template->subject;
            $body = $template->body;
        }

        // 3. Build real token data
        $application->loadMissing('jobPost');
        $data = $this->buildTokenData($application, $stage);

        // 4. Render template
        $rendered = $this->stageEmailService->renderTemplate($subject, $body, $data);

        // 5. Validate recipient
        $validationResult = $this->validateRecipient($application->email);
        if (! $validationResult['valid']) {
            return $this->logAndReturn($application, $stage, $mode, $adminUserId, 'failed', $validationResult['message'], $rendered['subject']);
        }

        // 6. Check idempotency
        $idempotencyKey = "stage_{$application->id}_{$stage->id}_{$mode}";
        if ($this->isDuplicate($idempotencyKey)) {
            return ['success' => true, 'message' => 'Email already sent for this event.', 'log_id' => null];
        }

        // 7. Create log + dispatch
        return $this->createLogAndDispatch(
            recipientEmail: $application->email,
            subject: $rendered['subject'],
            body: $rendered['body'],
            applicationId: $application->id,
            stageId: $stage->id,
            jobPostId: $application->job_post_id,
            mode: $mode,
            triggeredBy: $adminUserId ? 'admin' : 'system',
            adminUserId: $adminUserId,
            idempotencyKey: $idempotencyKey,
        );
    }

    /**
     * Send an application confirmation email (built-in template, no stage).
     *
     * @return array{success: bool, message: string, log_id: ?int}
     */
    public function sendApplicationConfirmation(JobApplication $application): array
    {
        if (! $this->isMailEnabled()) {
            return $this->skipResult('Mail sending is disabled.');
        }

        $application->loadMissing('jobPost');
        $companyName = business_config('business_name', config('app.name'));

        $subject = "{$companyName} — Application Received for {$application->jobPost->title}";
        $body = "Dear {$application->name},\n\n"
            ."Thank you for applying to the {$application->jobPost->title} position at {$companyName}.\n\n"
            .'We have received your application and our team will review it shortly. '
            ."If your profile matches our requirements, we will reach out to you with next steps.\n\n"
            ."Best regards,\n{$companyName} Hiring Team";

        $validationResult = $this->validateRecipient($application->email);
        if (! $validationResult['valid']) {
            return ['success' => false, 'message' => $validationResult['message'], 'log_id' => null];
        }

        $idempotencyKey = "confirmation_{$application->id}";
        if ($this->isDuplicate($idempotencyKey)) {
            return ['success' => true, 'message' => 'Confirmation email already sent.', 'log_id' => null];
        }

        return $this->createLogAndDispatch(
            recipientEmail: $application->email,
            subject: $subject,
            body: $body,
            applicationId: $application->id,
            stageId: null,
            jobPostId: $application->job_post_id,
            mode: 'auto',
            triggeredBy: 'system',
            adminUserId: null,
            idempotencyKey: $idempotencyKey,
        );
    }

    /**
     * Send a custom email (for admin manual send with edited content).
     *
     * @return array{success: bool, message: string, log_id: ?int}
     */
    public function sendCustomEmail(
        string $recipientEmail,
        string $subject,
        string $body,
        ?int $applicationId = null,
        ?int $stageId = null,
        ?int $jobPostId = null,
        ?int $adminUserId = null,
    ): array {
        if (! $this->isMailEnabled()) {
            return $this->skipResult('Mail sending is disabled.');
        }

        $validationResult = $this->validateRecipient($recipientEmail);
        if (! $validationResult['valid']) {
            return ['success' => false, 'message' => $validationResult['message'], 'log_id' => null];
        }

        return $this->createLogAndDispatch(
            recipientEmail: $recipientEmail,
            subject: $subject,
            body: $body,
            applicationId: $applicationId,
            stageId: $stageId,
            jobPostId: $jobPostId,
            mode: 'manual',
            triggeredBy: 'admin',
            adminUserId: $adminUserId,
            idempotencyKey: null,
        );
    }

    /**
     * Actually send an email (called by the queued job).
     */
    public function dispatchMail(MailLog $mailLog, string $body): void
    {
        try {
            $this->applyDynamicMailConfig();

            Mail::to($mailLog->recipient_email)
                ->send(new PipelineMail($mailLog->subject, $body));

            $mailLog->markSent();
        } catch (\Throwable $e) {
            Log::error('MailCommunicationService: send failed', [
                'mail_log_id' => $mailLog->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw so the job can retry
        }
    }

    // ─── Private Helpers ─────────────────────────────────────────

    private function isMailEnabled(): bool
    {
        $enabled = \App\Models\BusinessSetting::where('key', 'mail_enabled')->value('value');

        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array{valid: bool, message: string}
     */
    private function validateRecipient(?string $email): array
    {
        if (! $email) {
            return ['valid' => false, 'message' => 'Recipient email is missing.'];
        }

        $validator = Validator::make(['email' => $email], ['email' => 'required|email']);
        if ($validator->fails()) {
            return ['valid' => false, 'message' => 'Recipient email is invalid: '.$email];
        }

        return ['valid' => true, 'message' => ''];
    }

    private function isDuplicate(string $key): bool
    {
        return MailLog::where('idempotency_key', $key)
            ->whereIn('status', ['queued', 'sent'])
            ->exists();
    }

    /**
     * Build real token data from application + stage for rendering.
     *
     * @return array<string, string>
     */
    private function buildTokenData(JobApplication $application, PipelineStage $stage): array
    {
        $config = $stage->config ?? [];
        $companyName = business_config('business_name', config('app.name'));

        $data = [
            'candidate_name' => $application->name,
            'job_title' => $application->jobPost->title ?? '',
            'company_name' => $companyName,
            'stage_title' => $stage->title,
            'instructions' => $stage->instructions ?? '',
        ];

        if ($stage->type === 'assessment') {
            $subtypeLabels = ['task' => 'Task', 'exam' => 'Exam', 'quiz' => 'Quiz', 'online_quiz' => 'Quiz'];
            $data['assessment_type'] = $subtypeLabels[$stage->subtype] ?? 'Assessment';
            $data['deadline'] = $config['due_date'] ?? 'TBD';
            $data['duration'] = isset($config['duration']) ? $config['duration'].' minutes' : 'N/A';
            $data['total_marks'] = (string) ($config['total_marks'] ?? 'N/A');
            $data['passing_marks'] = (string) ($config['passing_marks'] ?? 'N/A');

            // Assessment URL generation
            $routeName = match ($stage->subtype) {
                'quiz', 'online_quiz' => 'assessments.quiz.entry',
                default => 'assessments.task.show', // task and exam
            };

            $data['assessment_url'] = \Illuminate\Support\Facades\URL::signedRoute($routeName, [
                'application' => $application->id,
                'stage' => $stage->id,
            ]);
        }

        if ($stage->type === 'interview') {
            $subtypeLabels = ['onsite' => 'Onsite Interview', 'phone' => 'Phone Interview', 'online' => 'Online Meeting'];
            $data['interview_type'] = $subtypeLabels[$stage->subtype] ?? 'Interview';
            $data['scheduled_at'] = $config['scheduled_at'] ?? 'TBD';
            $data['duration'] = isset($config['duration']) ? $config['duration'].' minutes' : 'N/A';
            $data['location'] = $config['location'] ?? 'N/A';
            $data['phone_details'] = $config['phone_details'] ?? 'N/A';
            $data['meeting_link'] = $config['meeting_link'] ?? 'N/A';
            $data['meeting_platform'] = $config['meeting_platform'] ?? 'N/A';
        }

        return $data;
    }

    /**
     * Create a MailLog and dispatch the queued job.
     *
     * @return array{success: bool, message: string, log_id: int}
     */
    private function createLogAndDispatch(
        string $recipientEmail,
        string $subject,
        string $body,
        ?int $applicationId,
        ?int $stageId,
        ?int $jobPostId,
        string $mode,
        string $triggeredBy,
        ?int $adminUserId,
        ?string $idempotencyKey,
    ): array {
        $log = MailLog::create([
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'job_application_id' => $applicationId,
            'pipeline_stage_id' => $stageId,
            'job_post_id' => $jobPostId,
            'send_mode' => $mode,
            'status' => 'queued',
            'triggered_by' => $triggeredBy,
            'admin_user_id' => $adminUserId,
            'idempotency_key' => $idempotencyKey,
        ]);

        \App\Jobs\SendPipelineEmailJob::dispatch($log->id, $body);

        return ['success' => true, 'message' => 'Email queued successfully.', 'log_id' => $log->id];
    }

    /**
     * Log a skipped/failed send before dispatching.
     *
     * @return array{success: bool, message: string, log_id: int}
     */
    private function logAndReturn(
        JobApplication $application,
        PipelineStage $stage,
        string $mode,
        ?int $adminUserId,
        string $status,
        string $message,
        ?string $subject = null,
    ): array {
        $log = MailLog::create([
            'recipient_email' => $application->email ?? '',
            'subject' => $subject ?? '',
            'job_application_id' => $application->id,
            'pipeline_stage_id' => $stage->id,
            'job_post_id' => $application->job_post_id,
            'send_mode' => $mode,
            'status' => $status,
            'error_message' => $message,
            'triggered_by' => $adminUserId ? 'admin' : 'system',
            'admin_user_id' => $adminUserId,
        ]);

        return ['success' => $status === 'skipped', 'message' => $message, 'log_id' => $log->id];
    }

    /**
     * @return array{success: bool, message: string, log_id: null}
     */
    private function skipResult(string $message): array
    {
        return ['success' => false, 'message' => $message, 'log_id' => null];
    }

    /**
     * Apply dynamic SMTP configuration from business settings at runtime.
     */
    private function applyDynamicMailConfig(): void
    {
        $host = business_config('mail_host');
        $port = business_config('mail_port');
        $username = business_config('mail_username');
        $password = business_config('mail_password');
        $encryption = business_config('mail_encryption');

        if ($host) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port ?: 587,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.mailers.smtp.encryption' => $encryption ?: 'tls',
            ]);
        }
    }

    /**
     * Generate a default email template for a stage when no custom template exists.
     *
     * @return array{subject: string, body: string}
     */
    private function getDefaultTemplate(PipelineStage $stage): array
    {
        $subject = '{{company_name}} — {{stage_title}} Invitation for {{job_title}}';

        $body = match ($stage->type) {
            'assessment' => "Dear {{candidate_name}},\n\n"
                ."We are pleased to invite you to the {{stage_title}} for the {{job_title}} position at {{company_name}}.\n\n"
                ."**{{stage_title}}**\n\n"
                ."• Assessment Type: {{assessment_type}}\n"
                ."• Duration: {{duration}}\n"
                ."• Total Marks: {{total_marks}}\n"
                ."• Passing Marks: {{passing_marks}}\n"
                ."• Deadline: {{deadline}}\n\n"
                ."**Take Assessment:**\n"
                ."{{assessment_url}}\n\n"
                ."**Instructions:**\n"
                ."{{instructions}}\n\n"
                ."Best regards,\n{{company_name}} Hiring Team",

            'interview' => "Dear {{candidate_name}},\n\n"
                ."We are pleased to invite you to an {{interview_type}} for the {{job_title}} position at {{company_name}}.\n\n"
                ."**{{stage_title}}**\n\n"
                ."• Date & Time: {{scheduled_at}}\n"
                ."• Duration: {{duration}}\n"
                ."• Location: {{location}}\n"
                ."• Platform: {{meeting_platform}}\n"
                ."• Meeting Link: {{meeting_link}}\n"
                ."• Phone Details: {{phone_details}}\n\n"
                ."Please join or be ready 5 minutes before the scheduled time.\n\n"
                ."**Instructions:**\n"
                ."{{instructions}}\n\n"
                ."If you need to reschedule, please reply to this email as soon as possible.\n\n"
                ."Best regards,\n{{company_name}} Hiring Team",

            default => "Dear {{candidate_name}},\n\n"
                ."We are pleased to inform you that you have been moved to the {{stage_title}} phase for the {{job_title}} position at {{company_name}}.\n\n"
                ."**Instructions:**\n"
                ."{{instructions}}\n\n"
                ."Best regards,\n{{company_name}} Hiring Team",
        };

        return ['subject' => $subject, 'body' => $body];
    }
}

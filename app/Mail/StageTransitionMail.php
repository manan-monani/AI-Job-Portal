<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StageTransitionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public \App\Models\JobApplication $application,
        public \App\Models\StageEmailTemplate $template
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->template->subject;
        $subject = str_replace('{job_title}', $this->application->jobPost->title ?? 'New Opportunity', $subject);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $body = $this->template->body;
        $body = str_replace('{candidate_name}', $this->application->name, $body);
        $body = str_replace('{job_title}', $this->application->jobPost->title ?? '', $body);

        return new Content(
            htmlString: nl2br($body),
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

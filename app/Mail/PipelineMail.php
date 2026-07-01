<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PipelineMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $renderedSubject,
        public string $renderedBody,
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = business_config('mail_from_address', config('mail.from.address'));
        $fromName = business_config('mail_from_name', config('mail.from.name'));

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromAddress, $fromName),
            subject: $this->renderedSubject,
        );
    }

    public function content(): Content
    {
        // Safely escape the body to prevent XSS, but allow specific markdown-like formatting
        $escapedBody = e($this->renderedBody);

        // Convert **bold** to <strong> (matching the frontend preview logic)
        $htmlBody = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $escapedBody);

        // Convert newlines to <br> tags
        $htmlBody = nl2br($htmlBody);

        $fullHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #334155; max-width: 600px; margin: 0 auto; padding: 20px;">
    {$htmlBody}
</body>
</html>
HTML;

        return new Content(
            htmlString: $fullHtml,
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

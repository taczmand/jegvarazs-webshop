<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminBulkEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $subjectLine;
    protected string $htmlBody;

    public function __construct(string $subjectLine, string $htmlBody)
    {
        $this->subjectLine = $subjectLine;
        $this->htmlBody = $htmlBody;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-bulk-email',
            with: [
                'htmlBody' => $this->htmlBody,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

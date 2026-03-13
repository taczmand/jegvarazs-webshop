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
    protected string $html;

    public function __construct(string $subjectLine, string $html)
    {
        $this->subjectLine = $subjectLine;
        $this->html = $html;
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
                'html' => $this->html,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

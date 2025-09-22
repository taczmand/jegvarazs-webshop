<?php

namespace App\Mail;

use App\Models\BasicData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $messageData;
    public function __construct(array $messageData)
    {
        $this->messageData = $messageData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Új üzenet érkezett az "Írjon nekünk" űrlapról',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $basic_data = BasicData::pluck('value', 'key')->toArray();

        return new Content(
            view: 'emails.contact-message',
            with: [
                'message_data' => $this->messageData,
                'basic_data' => $basic_data,
            ],
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

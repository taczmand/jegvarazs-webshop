<?php

namespace App\Mail;

use App\Models\BasicData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewAppointment extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $appointment;
    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Új időpontfoglalás rögzítésre került',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $basic_data = BasicData::pluck('value', 'key')->toArray();

        return new Content(
            view: 'emails.add-appointment',
            with: [
                'appointment' => $this->appointment,
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

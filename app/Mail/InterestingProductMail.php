<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterestingProductMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;
    public $product;
    public $contactMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($customer, $product, $contactMessage)
    {
        $this->customer = $customer;
        $this->product = $product;
        $this->contactMessage = $contactMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Termék érdeklődés: ' . $this->product,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.product-interesting',
            with: [
                'customer' => $this->customer,
                'product' => $this->product,
                'contactMessage' => $this->contactMessage,
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

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class InterestingProductMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;
    public $product;
    public $messageText;

    /**
     * Create a new message instance.
     */
    public function __construct($customer, $product, $messageText)
    {
        $this->customer = $customer;
        $this->product = $product;
        $this->messageText = $messageText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@jegvarazsbolt.hu', 'Jégvarázsbolt'),
            replyTo: [new Address($this->customer->email, $this->customer->last_name." ".$this->customer->first_name)],
            subject: 'Termék érdeklődés: ' . $this->product->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.interesting-product',
            with: [
                'customer' => $this->customer,
                'product' => $this->product,
                'messageText' => $this->messageText,
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

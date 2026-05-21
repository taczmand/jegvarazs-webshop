<?php

namespace App\Mail;

use App\Models\PartnerOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PartnerOfferMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PartnerOffer $offer)
    {
    }

    public function build(): self
    {
        $this->offer->loadMissing('customer');

        $partnerEmail = (string) ($this->offer->customer?->email ?? '');
        $partnerEmail = trim($partnerEmail);
        $partnerName = trim((string) ($this->offer->customer?->last_name ?? '') . ' ' . (string) ($this->offer->customer?->first_name ?? ''));

        if ($partnerName === '') {
            $partnerName = 'Partner';
        }

        $mail = $this
            ->subject('Árajánlat')
            ->when(filter_var($partnerEmail, FILTER_VALIDATE_EMAIL), function (self $m) use ($partnerEmail, $partnerName) {
                $m->from($partnerEmail, $partnerName);
                $m->replyTo($partnerEmail, $partnerName);
            })
            ->view('emails.partner_offer', [
                'offer' => $this->offer,
            ]);

        if ($this->offer->pdf_path && Storage::exists($this->offer->pdf_path)) {
            $mail->attach(Storage::path($this->offer->pdf_path), [
                'as' => 'ajanlat.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}

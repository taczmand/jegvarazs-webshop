<?php

namespace App\Mail;

use App\Models\BasicData;
use App\Models\BasicMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PromotionApplication extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;

        $this->withSymfonyMessage(function ($message) {
            $image = $this->data['coupon_image'] ?? null;
            if (!$image) {
                return;
            }
            $path = public_path('static_media/' . basename($image));
            if (!file_exists($path)) {
                return;
            }
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = $ext === 'png' ? 'image/png' : 'image/webp';
            $message->embedFromPath($path, 'coupon', $mime);
        });
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Promóció jelentkezésed megerősítve',
        );
    }

    public function content(): Content
    {
        $basic_data = BasicData::pluck('value', 'key')->toArray();
        $basic_media = BasicMedia::all()->pluck('file_path', 'key')->toArray();

        return new Content(
            view: 'emails.promotion-application',
            with: [
                'data' => $this->data,
                'basic_data' => $basic_data,
                'logo_url' => isset($basic_media['default_logo'])
                    ? asset('storage/' . $basic_media['default_logo'])
                    : null,
                'header_image_url' => 'storage/app/public/' . $this->data['coupon_image']
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use App\Models\AutomatedEmail;
use App\Models\BasicData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class AutomatedEmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $automation;
    public $configTemplate;

    public function __construct(AutomatedEmail $automation)
    {
        $this->automation = $automation;

        // Config betöltése title alapján
        $this->configTemplate = collect(config('automated_email_templates'))
            ->firstWhere('title', $automation->email_template);

        if (!$this->configTemplate) {
            throw new \Exception("Nem található sablon ehhez az email_template-hez: {$automation->email_template}");
        }
    }

    /**
     * Email metaadat (subject, from, stb.)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->configTemplate['title']
        );
    }

    /**
     * Email tartalom (view és változók)
     */
    public function content(): Content
    {
        $basic_data = BasicData::pluck('value', 'key')->toArray();

        return new Content(
            view: $this->configTemplate['view'],
            with: [
                'automation' => $this->automation,
                'vars'       => $this->configTemplate['variables'] ?? [],
                'basic_data' => $basic_data,
            ]
        );
    }

    /**
     * Mellékletek (opcionális)
     */
    public function attachments(): array
    {
        return [];
    }
}

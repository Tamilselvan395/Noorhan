<?php

namespace App\Mail;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quotation $quotation,
        public string $publicUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->rendered()['subject']
        );
    }

    public function content(): Content
    {
        $rendered = $this->rendered();

        return $rendered['templated']
            ? new Content(
                view: 'emails.templated',
                with: [
                    'bodyText' => $rendered['body'],
                    'unsubscribeUrl' => null,
                ],
            )
            : new Content(
                view: 'emails.quotation'
            );
    }

    private function rendered(): array
    {
        $template = \App\Models\EmailTemplate::query()
            ->where('key', 'quotation_cover')
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return [
                'templated' => false,
                'subject' => "Quotation {$this->quotation->reference} from Noorhan Group",
                'body' => '',
            ];
        }

        $rendered = app(
            \App\Services\Communications\TemplateRendererService::class
        )->render($template, [
            'customer' => [
                'name' => $this->quotation->customer?->name ?? 'Valued Customer',
            ],
            'quotation' => [
                'reference' => $this->quotation->reference,
                'total' => number_format(
                    (float) $this->quotation->total,
                    2
                ),
                'valid_until' => $this->quotation->valid_until?->format('M d, Y') ?? '—',
            ],
            'link' => $this->publicUrl,
        ]);

        return $rendered + ['templated' => true];
    }
}
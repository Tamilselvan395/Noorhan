<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
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
                    'bodyText'       => $rendered['body'],
                    'unsubscribeUrl' => null,
                ],
            )
            : new Content(
                view: 'emails.invoice',
                with: [
                    'invoice'   => $this->invoice,
                    'publicUrl' => $this->publicUrl,
                ],
            );
    }

    private function rendered(): array
    {
        $template = \App\Models\EmailTemplate::query()
            ->where('key', 'invoice_cover')
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return [
                'templated' => false,
                'subject'   => "Invoice {$this->invoice->reference} from Noorhan Group",
                'body'      => '',
            ];
        }

        $rendered = app(
            \App\Services\Communications\TemplateRendererService::class
        )->render($template, [
            'customer' => [
                'name' => $this->invoice->customer?->name ?? 'Valued Customer',
            ],
            'invoice' => [
                'reference' => $this->invoice->reference,
                'total'     => number_format((float) $this->invoice->total, 2),
                'due_date'  => $this->invoice->due_date?->format('M d, Y') ?? '—',
            ],
            'link' => $this->publicUrl,
        ]);

        return $rendered + ['templated' => true];
    }
}
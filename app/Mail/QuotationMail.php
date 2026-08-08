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

    public function __construct(public Quotation $quotation, public string $publicUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Quotation {$this->quotation->reference} from Noorhan Group");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quotation');
    }
}
<?php

namespace App\Actions\Quotations;

use App\Enums\CommunicationChannel;
use App\Enums\QuotationStatus;
use App\Events\Quotations\QuotationSent;
use App\Mail\QuotationMail;
use App\Models\Quotation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class SendQuotationAction
{
    public function execute(Quotation $quotation, CommunicationChannel $via): ?string
    {
        $sendable = in_array($quotation->status(), [QuotationStatus::Approved, QuotationStatus::Draft], true)
            && ($quotation->status() === QuotationStatus::Approved || ! $quotation->requires_approval);

        if (! $sendable) {
            throw new RuntimeException('Quotation must be approved before sending (approval required).');
        }

        $publicUrl = URL::temporarySignedRoute(
            'quotations.public',
            now()->addDays((int) config('noorhan.quotation.default_valid_days', 15) + 15),
            ['quotation' => $quotation->id],
        );

        $quotation->update([
            'status' => QuotationStatus::Sent->value,
            'sent_via' => $via->value,
            'sent_at' => now(),
        ]);

        if ($via === CommunicationChannel::Email && $quotation->customer?->email) {
            Mail::to($quotation->customer->email)->queue(new QuotationMail($quotation, $publicUrl));
        }

        // WhatsApp channel is delivered by the WhatsApp CRM module listening to this event.
        $quotation->logActivity("sent the quotation via {$via->label()}");

        event(new QuotationSent($quotation, $via, $publicUrl));

        return $publicUrl;
    }
}
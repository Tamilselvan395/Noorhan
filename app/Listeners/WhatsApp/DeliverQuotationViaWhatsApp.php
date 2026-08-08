<?php

namespace App\Listeners\WhatsApp;

use App\Enums\CommunicationChannel;
use App\Events\Quotations\QuotationSent;
use App\Services\WhatsApp\WhatsAppAutomationService;

class DeliverQuotationViaWhatsApp
{
    public function __construct(private WhatsAppAutomationService $automation) {}

    public function handle(QuotationSent $event): void
    {
        if (! config('whatsapp.enabled') || $event->via !== CommunicationChannel::WhatsApp) return;

        $target = $event->quotation->customer ?? $event->quotation->lead;

        if ($target) {
            $this->automation->quotationReminder($target, $event->quotation->reference, $event->publicUrl);
        }
    }
}
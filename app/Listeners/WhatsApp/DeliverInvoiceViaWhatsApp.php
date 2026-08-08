<?php

namespace App\Listeners\WhatsApp;

use App\Enums\CommunicationChannel;
use App\Events\Invoices\InvoiceSent;
use App\Services\WhatsApp\WhatsAppAutomationService;

class DeliverInvoiceViaWhatsApp
{
    public function __construct(private WhatsAppAutomationService $automation) {}

    public function handle(InvoiceSent $event): void
    {
        if (! config('whatsapp.enabled') || $event->via !== CommunicationChannel::WhatsApp) return;

        if ($event->invoice->customer) {
            $this->automation->quotationReminder($event->invoice->customer, $event->invoice->reference, $event->publicUrl);
        }
    }
}
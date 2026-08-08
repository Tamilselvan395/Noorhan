<?php

namespace App\Listeners\WhatsApp;

use App\Events\Invoices\InvoicePaid;
use App\Services\WhatsApp\WhatsAppAutomationService;

class SendCrossSellOnPaidInvoice
{
    public function __construct(private WhatsAppAutomationService $automation) {}

    public function handle(InvoicePaid $event): void
    {
        if (! config('whatsapp.enabled')) return;

        $customer = $event->invoice->customer;

        if ($customer && ! $this->automation->recentlySent($customer, 'cross_sell', 30)) {
            $this->automation->crossSell($customer);
        }
    }
}
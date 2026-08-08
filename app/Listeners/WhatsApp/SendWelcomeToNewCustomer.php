<?php

namespace App\Listeners\WhatsApp;

use App\Events\Customers\CustomerCreated;
use App\Services\WhatsApp\WhatsAppAutomationService;

class SendWelcomeToNewCustomer
{
    public function __construct(private WhatsAppAutomationService $automation) {}

    public function handle(CustomerCreated $event): void
    {
        if (! config('whatsapp.enabled')) return;

        $this->automation->welcome($event->customer);
    }
}
<?php

namespace App\Listeners\WhatsApp;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Events\WhatsApp\WhatsAppMessageReceived;
use App\Models\Customer;

class HandleInboundWhatsApp
{
    /** Logs inbound messages and enforces STOP opt-out compliance. */
    public function handle(WhatsAppMessageReceived $event): void
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $event->from);

        $customer = Customer::query()
            ->where(fn ($q) => $q->where('whatsapp', 'like', "%{$digits}")->orWhere('phone', 'like', "%{$digits}"))
            ->first();

        if (! $customer) {
            return; // unknown numbers are handled by Lead Capture (Module 5)
        }

        $customer->communications()->create([
            'channel' => CommunicationChannel::WhatsApp->value,
            'direction' => CommunicationDirection::Inbound->value,
            'subject' => 'WhatsApp inbound',
            'body' => $event->body,
            'occurred_at' => now(),
            'metadata' => ['scenario' => 'inbound'],
        ]);

        if (str_contains(strtolower((string) $event->body), 'stop')) {
            $customer->update(['whatsapp_opted_out' => true]);
            $customer->logActivity('opted out of WhatsApp messaging (STOP)');
        }
    }
}
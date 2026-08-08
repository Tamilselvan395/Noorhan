<?php

namespace App\Events\WhatsApp;

use Illuminate\Foundation\Events\Dispatchable;

class WhatsAppMessageReceived
{
    use Dispatchable;

    public function __construct(public string $from, public ?string $body) {}
}
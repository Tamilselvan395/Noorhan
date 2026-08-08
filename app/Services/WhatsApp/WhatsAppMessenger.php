<?php

namespace App\Services\WhatsApp;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Exceptions\WhatsAppApiException;
use App\Models\Customer;
use App\Models\Lead;

class WhatsAppMessenger
{
    public function __construct(private WhatsAppClient $client) {}

    /** @param Customer|Lead $entity */
    public function sendText($entity, string $body, ?string $scenario = null): ?array
    {
        $number = $this->numberFor($entity);

        if (! $number || $this->isOptedOut($entity)) {
            return null;
        }

        $response = $this->client->sendText($number, $body);

        $this->log($entity, $number, $scenario, ['body' => $body], $response);

        return $response;
    }

    /** @param Customer|Lead $entity */
    public function sendTemplate($entity, string $templateKey, array $params = [], ?string $scenario = null): ?array
    {
        $number = $this->numberFor($entity);

        if (! $number || $this->isOptedOut($entity)) {
            return null;
        }

        $response = $this->client->sendTemplate($number, $templateKey, $params);

        $this->log($entity, $number, $scenario, ['template' => $templateKey, 'params' => $params], $response);

        return $response;
    }

    /** @param Customer|Lead $entity */
    public function sendMedia($entity, string $url, string $kind, ?string $caption, ?string $scenario = null): ?array
    {
        $number = $this->numberFor($entity);

        if (! $number || $this->isOptedOut($entity)) {
            return null;
        }

        $response = $this->client->sendMedia($number, $url, $kind, $caption);

        $this->log($entity, $number, $scenario, ['media' => $url, 'kind' => $kind], $response);

        return $response;
    }

    private function numberFor($entity): ?string
    {
        $raw = $entity instanceof Customer ? ($entity->whatsapp ?: $entity->phone) : $entity->phone;

        if (! $raw) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $raw);
    }

    private function isOptedOut($entity): bool
    {
        return $entity instanceof Customer && $entity->whatsapp_opted_out;
    }

    private function log($entity, string $number, ?string $scenario, array $detail, array $response): void
    {
        $entity->communications()->create([
            'channel' => CommunicationChannel::WhatsApp->value,
            'direction' => CommunicationDirection::Outbound->value,
            'subject' => $scenario ? 'WhatsApp: '.ucwords(str_replace('_', ' ', $scenario)) : 'WhatsApp message',
            'body' => $detail['body'] ?? ($detail['caption'] ?? null),
            'user_id' => auth()->id(),
            'occurred_at' => now(),
            'metadata' => ['scenario' => $scenario, 'to' => $number, 'detail' => $detail, 'message_id' => $response['messages'][0]['id'] ?? null],
        ]);

        if ($entity instanceof Customer) {
            $entity->update(['whatsapp_last_messaged_at' => now(), 'last_activity_at' => now()]);
        } else {
            $entity->update(['last_contacted_at' => now()]);
        }
    }
}
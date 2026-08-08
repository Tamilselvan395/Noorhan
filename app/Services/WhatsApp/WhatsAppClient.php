<?php

namespace App\Services\WhatsApp;

use App\Exceptions\WhatsAppApiException;
use Illuminate\Support\Facades\Http;

class WhatsAppClient
{
    private function endpoint(): string
    {
        return config('whatsapp.api_url').'/'.config('whatsapp.api_version')
            .'/'.config('whatsapp.phone_number_id').'/messages';
    }

    public function sendText(string $to, string $body): array
    {
        return $this->send([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['preview_url' => true, 'body' => $body],
        ]);
    }

    /** @param array<int, string> $params body-component text parameters */
    public function sendTemplate(string $to, string $templateKey, array $params = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => config('whatsapp.templates.'.$templateKey, $templateKey),
                'language' => ['code' => config('whatsapp.language', 'en')],
            ],
        ];

        if ($params !== []) {
            $payload['template']['components'] = [[
                'type' => 'body',
                'parameters' => collect($params)->map(fn ($text) => ['type' => 'text', 'text' => $text])->all(),
            ]];
        }

        return $this->send($payload);
    }

    public function sendMedia(string $to, string $url, string $kind = 'document', ?string $caption = null, ?string $filename = null): array
    {
        $media = ['link' => $url];

        if ($caption) $media['caption'] = $caption;
        if ($kind === 'document') $media['filename'] = $filename ?? 'noorhan.pdf';

        return $this->send([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => $kind,
            $kind => $media,
        ]);
    }

    private function send(array $payload): array
    {
        $response = Http::withToken((string) config('whatsapp.access_token'))
            ->acceptJson()
            ->post($this->endpoint(), $payload);

        if ($response->failed()) {
            throw new WhatsAppApiException(
                'WhatsApp API error ('.$response->status().'): '.($response->json('error.message') ?? $response->body()),
            );
        }

        return $response->json() ?? [];
    }
}
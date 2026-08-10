<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;

class OpenAiProvider implements AiProviderInterface
{
    public function name(): string
    {
        return 'openai';
    }

    public function complete(string $prompt, string $context): string
    {
        $response = Http::withToken((string) config('noorhan.ai.openai.key'))
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('noorhan.ai.openai.model'),
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $context],
                ],
                'temperature' => 0.2,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI error: '.$response->status());
        }

        return trim($response->json('choices.0.message.content', ''));
    }
}
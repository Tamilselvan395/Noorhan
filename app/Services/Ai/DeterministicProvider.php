<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;

class DeterministicProvider implements AiProviderInterface
{
    public function name(): string
    {
        return 'deterministic';
    }

    public function complete(string $prompt, string $context): string
    {
        return 'Offline deterministic engine — configure OPENAI_API_KEY for LLM completions.';
    }
}
<?php

namespace App\Contracts;

interface AiProviderInterface
{
    /** Free-text completion (classification, summarization). Deterministic fallback when offline. */
    public function complete(string $prompt, string $context): string;

    public function name(): string;
}
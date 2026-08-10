<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Models\Customer;

class SummaryService
{
    public function __construct(private AiProviderInterface $provider) {}

    /** @return array{total: int, last_contact: ?string, channels: array, topics: array, tone: string, llm: string} */
    public function summarizeCustomer(Customer $customer): array
    {
        $comms = $customer->communications()->get();

        $topics = $comms->pluck('body')->filter()
            ->flatMap(fn ($body) => preg_split('/\W+/', Str::lower($body)))
            ->filter(fn ($w) => strlen($w) > 4 && ! in_array($w, ['about', 'would', 'there', 'their', 'please', 'thanks']))
            ->countBy()->sortDesc()->take(5)->keys()->all();

        $negative = $comms->filter(fn ($c) => Str::contains(Str::lower((string) $c->body), ['issue', 'problem', 'delay', 'complaint', 'wrong']))->count();

        return [
            'total' => $comms->count(),
            'last_contact' => $comms->max('occurred_at')?->diffForHumans(),
            'channels' => $comms->groupBy('channel')->map->count()->all(),
            'topics' => $topics,
            'tone' => $negative > 0 ? "{$negative} concern(s) detected — handle with care" : 'positive / neutral',
            'llm' => $this->provider->complete(
                'Summarize this customer relationship in two sentences.',
                $comms->take(20)->pluck('body')->implode("\n"),
            ),
        ];
    }
}
<?php

namespace App\Console\Commands;

use App\Services\Ai\DailyBriefingService;
use Illuminate\Console\Command;

class GenerateAiBriefing extends Command
{
    protected $signature = 'ai:briefing';
    protected $description = 'Generate today\'s AI executive briefing.';

    public function handle(DailyBriefingService $service): int
    {
        $service->generate();

        $this->info('Briefing ready.');

        return self::SUCCESS;
    }
}
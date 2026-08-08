<?php

namespace App\Console\Commands;

use App\Jobs\Accounting\ProcessZohoSyncJob;
use App\Models\ZohoSyncLog;
use Illuminate\Console\Command;

class RetryFailedZohoSyncs extends Command
{
    protected $signature = 'zoho:retry-failed';
    protected $description = 'Re-queue failed Zoho sync logs that have not exceeded max attempts.';

    public function handle(): int
    {
        $logs = ZohoSyncLog::query()
            ->where('status', 'failed')
            ->where('attempts', '<', 5)
            ->get();

        $logs->each(fn (ZohoSyncLog $log) => ProcessZohoSyncJob::dispatch($log));

        $this->info("Re-queued {$logs->count()} failed sync(s).");

        return self::SUCCESS;
    }
}
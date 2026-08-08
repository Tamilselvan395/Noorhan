<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneSystemLogs extends Command
{
    protected $signature = 'system:prune-logs';
    protected $description = 'Prune activity & audit logs beyond configured retention windows.';

    public function handle(): int
    {
        $auditDays = (int) config('noorhan.audit.retention_days', 365);
        $activityDays = (int) config('noorhan.audit.activity_retention_days', 180);

        $prunedAudit = AuditLog::query()->where('created_at', '<', now()->subDays($auditDays))->delete();
        $prunedActivity = Activity::query()->where('created_at', '<', now()->subDays($activityDays))->delete();

        $this->info("Pruned {$prunedAudit} audit rows (>{$auditDays}d) and {$prunedActivity} activity rows (>{$activityDays}d).");

        return self::SUCCESS;
    }
}
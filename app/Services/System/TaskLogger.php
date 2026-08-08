<?php

namespace App\Services\System;

use App\Models\SchedulerLog;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TaskLogger
{
    /** Direct log entry (manual runs, ad-hoc). */
    public static function log(string $task, string $status, ?string $output = null, string $trigger = 'manual'): SchedulerLog
    {
        return SchedulerLog::create([
            'task' => $task,
            'status' => $status,
            'trigger' => $trigger,
            'output' => Str::limit((string) $output, 2000),
            'started_at' => now(),
            'finished_at' => now(),
        ]);
    }

    /** Wrap a scheduled event so every cron run is recorded with duration. */
    public static function wrap(Event $event, string $label): Event
    {
        $startKey = 'scheduler.start.'.Str::slug($label);

        return $event
            ->before(fn () => Cache::put($startKey, now(), 600))
            ->onSuccess(function () use ($label, $startKey) {
                SchedulerLog::create([
                    'task' => $label,
                    'status' => 'success',
                    'trigger' => 'system',
                    'started_at' => Cache::get($startKey),
                    'finished_at' => now(),
                ]);
            })
            ->onFailure(function () use ($label, $startKey) {
                SchedulerLog::create([
                    'task' => $label,
                    'status' => 'failed',
                    'trigger' => 'system',
                    'started_at' => Cache::get($startKey),
                    'finished_at' => now(),
                ]);
            });
    }
}
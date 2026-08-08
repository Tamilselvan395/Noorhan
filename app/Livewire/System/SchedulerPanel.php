<?php

namespace App\Livewire\System;

use App\Models\SchedulerLog;
use App\Services\System\TaskLogger;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Livewire\Component;

class SchedulerPanel extends Component
{
    public function tasks(): array
    {
        return collect(config('noorhan.scheduler.tasks', []))
            ->map(function (array $task) {
                $last = SchedulerLog::query()->where('task', $task['label'])->latest('id')->first();

                return $task + [
                    'last_status' => $last?->status,
                    'last_run' => $last?->finished_at?->diffForHumans(),
                ];
            })
            ->all();
    }

    public function run(string $key): void
    {
        $task = collect(config('noorhan.scheduler.tasks', []))->firstWhere('key', $key);

        abort_unless($task !== null, 404);

        Artisan::call($task['command']);

        TaskLogger::log($task['label'], 'success', Artisan::output(), 'manual');

        $this->dispatch('notify', message: "{$task['label']} executed.", type: 'success');
    }

    public function render(): View
    {
        return view('livewire.system.scheduler-panel', [
            'logs' => SchedulerLog::query()->latest('id')->limit(20)->get(),
        ]);
    }
}
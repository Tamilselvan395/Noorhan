<?php

namespace App\Livewire\System;

use App\Helpers\AuditDiffHelper;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class AuditExplorer extends Component
{
    use WithPagination;

    public string $user = 'all';
    public string $event = 'all';
    public string $type = 'all';
    public string $from = '';
    public string $to = '';
    public ?int $expandedId = null;

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function toggle(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function stats(): array
    {
        return [
            'today' => AuditLog::query()->whereDate('created_at', today())->count(),
            'updates' => AuditLog::query()->where('event', 'updated')->whereBetween('created_at', [now()->startOfMonth(), now()])->count(),
            'deletes' => AuditLog::query()->where('event', 'deleted')->whereBetween('created_at', [now()->startOfMonth(), now()])->count(),
        ];
    }

    public function render(): View
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($this->user !== 'all', fn ($q) => $q->user((int) $this->user))
            ->when($this->event !== 'all', fn ($q) => $q->event($this->event))
            ->when($this->type !== 'all', fn ($q) => $q->auditableType($this->type))
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->latest()
            ->paginate(20);

        return view('livewire.system.audit-explorer', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
            'types' => collect(config('noorhan.audit.audited_models', []))
                ->mapWithKeys(fn ($class) => [$class => class_basename($class)])->all(),
        ]);
    }
}
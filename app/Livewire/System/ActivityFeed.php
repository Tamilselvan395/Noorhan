<?php

namespace App\Livewire\System;

use App\Models\Activity;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityFeed extends Component
{
    use WithPagination;

    public string $user = 'all';
    public string $type = 'all';
    public string $search = '';
    public string $from = '';
    public string $to = '';

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function stats(): array
    {
        return [
            'today' => Activity::query()->whereDate('created_at', today())->count(),
            'week' => Activity::query()->whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
            'most_active' => User::query()->withCount(['activities' => fn ($q) => $q->whereBetween('created_at', [now()->startOfMonth(), now()])])
                ->orderByDesc('activities_count')->first()?->name ?? '—',
        ];
    }

    public function render(): View
    {
        $activities = Activity::query()
            ->with(['user', 'subject'])
            ->when($this->user !== 'all', fn ($q) => $q->user((int) $this->user))
            ->when($this->type !== 'all', fn ($q) => $q->subjectType($this->type))
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->latest()
            ->paginate(20);

        return view('livewire.system.activity-feed', [
            'activities' => $activities,
            'users' => User::orderBy('name')->get(),
            'types' => collect(config('noorhan.audit.audited_models', []))
                ->mapWithKeys(fn ($class) => [$class => class_basename($class)])->all(),
        ]);
    }
}
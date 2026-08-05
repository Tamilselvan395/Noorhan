<?php

namespace App\Livewire\Dashboard;

use App\Enums\DashboardPeriod;
use App\Enums\LoginHistoryType;
use App\Models\LoginHistory;
use App\Models\SecurityLog;
use App\Services\Dashboard\ChartDataService;
use App\Services\Dashboard\WidgetRegistry;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class Overview extends Component
{
    public string $period = DashboardPeriod::Month->value;

    public function __construct(
        private WidgetRegistry $registry,
        private ChartDataService $charts,
    ) {}

    public function setPeriod(string $period): void
    {
        abort_unless(collect(DashboardPeriod::cases())->pluck('value')->contains($period), 422);

        $this->period = $period;

        $this->dispatch('dashboard:charts', $this->chartPayload());
    }

    /** @return Collection<int, \App\DTOs\Dashboard\WidgetData> */
    public function widgets(): Collection
    {
        $period = DashboardPeriod::from($this->period);

        return $this->registry->widgets()->map(fn ($widget) => $widget->data($period));
    }

    /** Chart payload consumed by ApexCharts (also dispatched on period change). */
    public function chartPayload(): array
    {
        $period = DashboardPeriod::from($this->period);
        [$from, $to] = $period->range();

        $success = $this->charts->counts(
            LoginHistory::query()->where('type', LoginHistoryType::Login->value)->where('successful', true),
            $from, $to, 'created_at', $period->isHourly(),
        );

        $failed = $this->charts->counts(
            LoginHistory::query()->where('type', LoginHistoryType::Login->value)->where('successful', false),
            $from, $to, 'created_at', $period->isHourly(),
        );

        $platforms = LoginHistory::query()
            ->where('type', LoginHistoryType::Login->value)
            ->where('successful', true)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy('platform')
            ->map(fn ($group, $name) => ['name' => $name, 'value' => $group->count()])
            ->values()
            ->all();

        return [
            'activity' => [
                'labels' => $success['labels'],
                'success' => $success['values'],
                'failed' => $failed['values'],
            ],
            'platforms' => $platforms,
        ];
    }

    public function recentLogins(): Collection
    {
        return LoginHistory::query()->with('user')->latest()->limit(8)->get();
    }

    public function recentSecurity(): Collection
    {
        return SecurityLog::query()->with('user')->latest()->limit(6)->get();
    }

    public function render(): View
    {
        return view('livewire.dashboard.overview');
    }
}
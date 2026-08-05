<div class="space-y-6" x-data="dashboardCharts()" @dashboard:charts.window="renderAll($event.detail)">

    {{-- Header + Period Selector --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ now()->format('l, M d, Y') }} · Noorhan Group Overview</p>
        </div>
        <div class="inline-flex self-start rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-1">
            @foreach (\App\Enums\DashboardPeriod::cases() as $p)
                <button wire:click="setPeriod('{{ $p->value }}')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $period === $p->value ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    {{ $p->label() }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- KPI Stat Cards (Widget Registry driven) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($this->widgets() as $widget)
            <x-dashboard.stat-card
                :label="$widget->label"
                :value="$widget->value"
                :delta="$widget->delta"
                :hint="$widget->hint"
                :icon="$widget->icon"
                :accent="$widget->accent"
                wire:key="{{ $widget->key }}" />
        @endforeach
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Sign-in Activity</h3>
                <span class="text-xs text-gray-400">successful vs failed</span>
            </div>
            <div id="activityChart" class="h-72" wire:ignore></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Platforms</h3>
                <span class="text-xs text-gray-400">by sign-in</span>
            </div>
            <div id="platformChart" class="h-72" wire:ignore></div>
        </div>
    </div>

    {{-- Activity Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <x-slot:header><h3 class="font-semibold">Recent Sign-ins</h3></x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($this->recentLogins() as $login)
                    <li class="flex items-center justify-between p-4" wire:key="rl-{{ $login->id }}">
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $login->user?->name ?? 'Unknown user' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $login->browser }} · {{ $login->platform }} · {{ $login->ip_address }}</p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ \App\Enums\LoginHistoryType::from($login->type)->badgeClasses() }}">
                                {{ \App\Enums\LoginHistoryType::from($login->type)->label() }}
                            </span>
                            <p class="text-xs text-gray-400 mt-1">{{ $login->created_at->diffForHumans() }}</p>
                        </div>
                    </li>
                @empty
                    <li class="p-8 text-center text-sm text-gray-400">No sign-in activity yet.</li>
                @endforelse
            </ul>
        </x-card>

        <x-card>
            <x-slot:header><h3 class="font-semibold">Security Events</h3></x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($this->recentSecurity() as $log)
                    <li class="flex items-center justify-between p-4" wire:key="sl-{{ $log->id }}">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ \App\Enums\SecurityEvent::from($log->event)->label() }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $log->user?->name ?? 'System' }} · {{ $log->ip_address }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="p-8 text-center text-sm text-gray-400">No security events yet.</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    {{-- Future Divisional Dashboards (activate as modules ship) --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Divisional Dashboards</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
            @foreach (['CEO Analytics', 'Sales', 'Marketing', 'Finance', 'Supplier', 'Customer'] as $name)
                <div class="flex items-center justify-between p-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 text-sm text-gray-400 dark:text-gray-500">
                    <span class="font-medium">{{ $name }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ApexCharts: swap for npm-bundled import in air-gapped production --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
<script>
window.dashboardCharts = () => ({
    payload: null,
    charts: [],
    dark: document.documentElement.classList.contains('dark'),

    init() {
        this.$wire.chartPayload().then(p => this.renderAll(p));

        // Re-theme charts automatically when the user toggles dark mode.
        new MutationObserver(() => {
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark !== this.dark) {
                this.dark = isDark;
                if (this.payload) this.renderAll(this.payload);
            }
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    },

    renderAll(payload) {
        this.payload = payload;
        this.charts.forEach(c => c.destroy());
        this.charts = [];
        this.renderActivity(payload.activity);
        this.renderPlatforms(payload.platforms);
    },

    theme() {
        return {
            foreColor: this.dark ? '#9ca3af' : '#6b7280',
            grid: { borderColor: this.dark ? '#374151' : '#e5e7eb' },
            tooltip: { theme: this.dark ? 'dark' : 'light' },
        };
    },

    renderActivity(activity) {
        const el = document.getElementById('activityChart');
        if (!el || !window.ApexCharts) return;

        const chart = new ApexCharts(el, {
            chart: { type: 'area', height: '100%', toolbar: { show: false }, zoom: { enabled: false }, background: 'transparent' },
            series: [
                { name: 'Sign-ins', data: activity.success },
                { name: 'Failed', data: activity.failed },
            ],
            colors: ['#2563eb', '#dc2626'],
            xaxis: { categories: activity.labels, labels: { style: { colors: this.theme().foreColor } }, axisBorder: { show: false } },
            yaxis: { labels: { style: { colors: this.theme().foreColor } }, forceNiceScale: true },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.25, opacityTo: 0.02 } },
            grid: { borderColor: this.theme().grid.borderColor },
            legend: { labels: { colors: this.theme().foreColor } },
            tooltip: { theme: this.dark ? 'dark' : 'light' },
        });

        chart.render();
        this.charts.push(chart);
    },

    renderPlatforms(list) {
        const el = document.getElementById('platformChart');
        if (!el || !window.ApexCharts) return;

        if (!list.length) {
            el.innerHTML = '<div class="h-full flex items-center justify-center text-sm text-gray-400">No data for this period.</div>';
            return;
        }

        const chart = new ApexCharts(el, {
            chart: { type: 'donut', height: '100%', background: 'transparent' },
            series: list.map(i => i.value),
            labels: list.map(i => i.name),
            colors: ['#2563eb', '#7c3aed', '#059669', '#d97706', '#dc2626', '#0891b2'],
            dataLabels: { enabled: false },
            stroke: { colors: [this.dark ? '#1f2937' : '#ffffff'], width: 2 },
            legend: { position: 'bottom', labels: { colors: this.theme().foreColor } },
            states: { hover: { filter: { type: 'lighten', value: 0.04 } } },
        });

        chart.render();
        this.charts.push(chart);
    },
});
</script>
@endpush
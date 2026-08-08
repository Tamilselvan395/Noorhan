<div class="space-y-6" x-data="marketingCharts()" >
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Marketing</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Campaigns, attribution & lead-source intelligence.</p>
        </div>
        <button wire:click="$dispatch('open-marketing-form')" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">+ New Campaign</button>
    </div>

    @php $stats = $this->stats(app(\App\Services\Marketing\MarketingMetricsService::class)); @endphp
    <div class="grid grid-cols-2 xl:grid-cols-5 gap-4">
        <x-dashboard.stat-card label="Campaigns" :value="number_format($stats['campaigns'])" icon="chart" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Active" :value="number_format($stats['active'])" icon="bolt" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Attributed Leads" :value="number_format($stats['attributed_leads'])" icon="users" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Total Spend" :value="\App\Helpers\CurrencyHelper::format($stats['total_spent'])" icon="shield" accent="bg-amber-500/10 text-amber-600 dark:text-amber-400" />
        <x-dashboard.stat-card label="Avg Cost / Lead" :value="\App\Helpers\CurrencyHelper::format($stats['avg_cpl'])" icon="chart" accent="bg-cyan-500/10 text-cyan-600 dark:text-cyan-400" />
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Lead Sources</h3>
            <div id="sourceChart" class="h-64" wire:ignore></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Leads — Last 6 Months</h3>
            <div id="monthlyChart" class="h-64" wire:ignore></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Top Campaigns</h3>
            <div id="topChart" class="h-64" wire:ignore></div>
        </div>
    </div>

    {{-- Campaign table --}}
    <x-card>
        <x-slot:header><h3 class="font-semibold">Campaigns</h3></x-slot:header>
        <x-table>
            <x-slot:head>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Campaign</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Channel</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Budget / Spent</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Leads</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
            </x-slot:head>
            <x-slot:body>
                @forelse ($campaigns as $campaign)
                    <tr wire:key="mc-{{ $campaign->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('marketing.show', $campaign) }}'">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $campaign->name }}</p>
                            <p class="text-xs text-gray-400">utm: {{ $campaign->utm_campaign }} · {{ $campaign->division()->label() }}</p>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $campaign->channel()->label() }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ number_format((float) $campaign->spent, 2) }} / {{ number_format((float) $campaign->budget, 2) }}
                            <div class="w-24 h-1.5 mt-1 rounded-full bg-gray-200 dark:bg-gray-700"><div class="h-1.5 rounded-full {{ $campaign->budgetUtilization() > 100 ? 'bg-red-500' : 'bg-blue-500' }}" style="width: {{ min($campaign->budgetUtilization(), 100) }}%"></div></div>
                        </td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $campaign->leads_count }}</td>
                        <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $campaign->status()->badge() }}">{{ $campaign->status()->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">No marketing campaigns yet.</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>
    </x-card>

    {{-- Form modal --}}
    @if ($formOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('formOpen', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Marketing Campaign</h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                        <input wire:model="name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division</label>
                            <select wire:model="division" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Channel</label>
                            <select wire:model="channel" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                @foreach (\App\Enums\MarketingChannel::cases() as $c) <option value="{{ $c->value }}">{{ $c->label() }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select wire:model="status" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                @foreach (\App\Enums\MarketingCampaignStatus::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">UTM Campaign *</label>
                            <input wire:model="utm_campaign" placeholder="e.g. summer-brakes" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            @error('utm_campaign') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Budget</label>
                            <input wire:model="budget" type="number" step="0.01" min="0" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Spent</label>
                            <input wire:model="spent" type="number" step="0.01" min="0" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start</label>
                            <input wire:model="start_date" type="date" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End</label>
                            <input wire:model="end_date" type="date" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Goals</label>
                        <textarea wire:model="goals" rows="2" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="$set('formOpen', false)" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                        <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Create Campaign</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
<script>
window.marketingCharts = () => ({
    charts: [],
    dark: document.documentElement.classList.contains('dark'),

    init() {
        this.$wire.chartPayload().then(p => this.renderAll(p));

        new MutationObserver(() => {
            const d = document.documentElement.classList.contains('dark');
            if (d !== this.dark) { this.dark = d; this.$wire.chartPayload().then(p => this.renderAll(p)); }
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    },

    renderAll(p) {
        this.charts.forEach(c => c.destroy());
        this.charts = [];
        if (!window.ApexCharts) return;

        const theme = { foreColor: this.dark ? '#9ca3af' : '#6b7280' };

        // Lead sources donut
        if (p.sources.length) {
            const c1 = new ApexCharts(document.getElementById('sourceChart'), {
                chart: { type: 'donut', background: 'transparent' },
                series: p.sources.map(s => s.value),
                labels: p.sources.map(s => s.name),
                colors: ['#2563eb','#7c3aed','#059669','#d97706','#dc2626','#0891b2','#db2777'],
                legend: { position: 'bottom', labels: { colors: theme.foreColor } },
                dataLabels: { enabled: false },
                stroke: { colors: [this.dark ? '#1f2937' : '#ffffff'] },
            });
            c1.render(); this.charts.push(c1);
        }

        // Monthly leads area
        const c2 = new ApexCharts(document.getElementById('monthlyChart'), {
            chart: { type: 'area', background: 'transparent', toolbar: { show: false } },
            series: [{ name: 'Leads', data: p.monthly.values }],
            xaxis: { categories: p.monthly.labels, labels: { style: { colors: theme.foreColor } } },
            colors: ['#2563eb'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: .25, opacityTo: 0 } },
            grid: { borderColor: this.dark ? '#374151' : '#e5e7eb' },
        });
        c2.render(); this.charts.push(c2);

        // Top campaigns bar
        const c3 = new ApexCharts(document.getElementById('topChart'), {
            chart: { type: 'bar', background: 'transparent', toolbar: { show: false } },
            series: [{ name: 'Leads', data: p.top.map(t => t.leads) }],
            xaxis: { categories: p.top.map(t => t.name), labels: { style: { colors: theme.foreColor } } },
            colors: ['#7c3aed'],
            plotOptions: { bar: { borderRadius: 4 } },
            dataLabels: { enabled: false },
            grid: { borderColor: this.dark ? '#374151' : '#e5e7eb' },
        });
        c3.render(); this.charts.push(c3);
    },
});
</script>
@endpush
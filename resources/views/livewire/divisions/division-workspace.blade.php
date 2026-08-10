<div class="space-y-6" x-data="divisionCharts(@js(['categories' => $categories]))">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $division->label() }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Division workspace — sales, channel partners & production planning.</p>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <x-dashboard.stat-card label="Revenue" :value="\App\Helpers\CurrencyHelper::format($m['revenue'])" icon="chart" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Orders" :value="number_format($m['orders'])" icon="bolt" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Open Leads" :value="number_format($m['open_leads'])" hint="\App\Helpers\CurrencyHelper::format($m['pipeline']).' pipeline'" icon="users" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Customers" :value="number_format($m['customers'])" icon="users" accent="bg-cyan-500/10 text-cyan-600 dark:text-cyan-400" />
        <x-dashboard.stat-card label="Outstanding" :value="\App\Helpers\CurrencyHelper::format($m['outstanding'])" icon="shield" accent="bg-amber-500/10 text-amber-600 dark:text-amber-400" />
        <x-dashboard.stat-card label="Active Partners" :value="number_format(collect($partners)->where('dormant', false)->count())" hint="channel" icon="chart" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Category mix --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Revenue by Product Line</h3>
            <div id="categoryChart" class="h-64" wire:ignore></div>
        </div>

        {{-- Top products --}}
        <x-card>
            <x-slot:header><h3 class="font-semibold">Top Products</h3></x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($topProducts as $p)
                    <li class="p-3 flex justify-between text-sm" wire:key="tp-{{ $p['sku'] }}">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ $p['name'] }}</p>
                            <p class="text-xs text-gray-400">{{ $p['sku'] }} · {{ $p['qty'] }} units</p>
                        </div>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($p['revenue'], 2) }}</span>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-gray-400">No sales yet.</li>
                @endforelse
            </ul>
        </x-card>

        {{-- Top customers --}}
        <x-card>
            <x-slot:header><h3 class="font-semibold">Top Customers</h3></x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($topCustomers as $c)
                    <li class="p-3 flex justify-between text-sm" wire:key="tc-{{ $c['name'] }}">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ $c['name'] }}</p>
                            <p class="text-xs text-gray-400">{{ $c['type'] }} · {{ $c['orders'] }} orders</p>
                        </div>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($c['revenue'], 2) }}</span>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-gray-400">No customers yet.</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    {{-- Channel partners --}}
    <x-card>
        <x-slot:header><h3 class="font-semibold">Channel Partners (Distributors · Dealers · Garages)</h3></x-slot:header>
        <x-table>
            <x-slot:head>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Partner</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Orders</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Revenue</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Health</th>
            </x-slot:head>
            <x-slot:body>
                @forelse ($partners as $partner)
                    <tr wire:key="cp-{{ $partner['id'] }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('customers.show', $partner['id']) }}'">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $partner['name'] }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $partner['type'] }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $partner['orders'] }}</td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ number_format($partner['revenue'], 2) }}</td>
                        <td class="px-6 py-3">
                            @if ($partner['dormant'])
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Dormant 90d+</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">Active</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">No channel partners in this division yet.</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>
    </x-card>

    {{-- AI reorder plan --}}
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">🤖 AI Reorder / Production Plan</h3>
                <span class="text-xs text-gray-400">3-month demand average + 20% buffer</span>
            </div>
        </x-slot:header>
        <x-table>
            <x-slot:head>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">SKU</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Avg Monthly</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Trend</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Suggested Order</th>
            </x-slot:head>
            <x-slot:body>
                @forelse ($reorder as $row)
                    <tr wire:key="ro-{{ $row['id'] }}">
                        <td class="px-6 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ $row['sku'] }}</td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $row['avg_monthly'] }}</td>
                        <td class="px-6 py-3 text-sm {{ $row['trend'] === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">{{ $row['trend'] === 'up' ? '▲ rising' : '▼ falling' }}</td>
                        <td class="px-6 py-3 text-sm font-bold text-blue-600 dark:text-blue-400">{{ $row['suggested_order'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">No demand history yet — suggestions appear after the first sales.</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>
    </x-card>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
<script>
window.divisionCharts = (payload) => ({
    chart: null,
    dark: document.documentElement.classList.contains('dark'),

    init() {
        this.render();
        new MutationObserver(() => {
            const d = document.documentElement.classList.contains('dark');
            if (d !== this.dark) { this.dark = d; this.render(); }
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    },

    render() {
        if (!window.ApexCharts) return;
        const el = document.getElementById('categoryChart');
        const entries = Object.entries(payload.categories || {});

        if (this.chart) { this.chart.destroy(); this.chart = null; }
        if (!entries.length) { el.innerHTML = '<div class="h-full flex items-center justify-center text-sm text-gray-400">No sales data yet.</div>'; return; }

        this.chart = new ApexCharts(el, {
            chart: { type: 'donut', background: 'transparent' },
            series: entries.map(([, v]) => v),
            labels: entries.map(([k]) => k),
            colors: ['#2563eb', '#7c3aed', '#059669', '#d97706', '#dc2626', '#0891b2'],
            legend: { position: 'bottom', labels: { colors: this.dark ? '#9ca3af' : '#6b7280' } },
            dataLabels: { enabled: false },
            stroke: { colors: [this.dark ? '#1f2937' : '#ffffff'] },
        });
        this.chart.render();
    },
});
</script>
@endpush
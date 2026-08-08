<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sales Orders</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Confirmed business — from quotation conversion or counter sales.</p>
        </div>
        <button wire:click="$dispatch('open-order-form')" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">+ New Order</button>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <x-dashboard.stat-card label="Open Orders" :value="number_format($stats['open'])" icon="chart" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Open Value" :value="\App\Helpers\CurrencyHelper::format($stats['open_value'])" icon="bolt" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Delivered This Month" :value="number_format($stats['delivered_month'])" icon="shield" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Pending Confirmation" :value="number_format($stats['pending'])" icon="users" accent="bg-amber-500/10 text-amber-600 dark:text-amber-400" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search reference…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="status" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Statuses</option>
            @foreach (\App\Enums\SalesOrderStatus::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
        </select>
    </div>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Reference</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Source</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Delivery</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($orders as $order)
                <tr wire:key="{{ $order->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('sales-orders.show', $order) }}'">
                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $order->reference }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $order->customer?->displayName() ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $order->quotation?->reference ?? 'Manual' }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ \App\Helpers\CurrencyHelper::format((float) $order->total) }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status()->badge() }}">{{ $order->status()->label() }}</span></td>
                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $order->delivered_at?->format('M d') ?? ($order->expected_delivery_date?->format('M d (exp)') ?? '—') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No sales orders yet.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
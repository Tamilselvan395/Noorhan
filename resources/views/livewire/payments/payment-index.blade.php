<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payments</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Accounts receivable ledger and collections.</p>
        </div>
        <button wire:click="$dispatch('open-payment-form')" class="py-2 px-4 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold shadow-sm">+ Record Payment</button>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <x-dashboard.stat-card label="Total Collected" :value="\App\Helpers\CurrencyHelper::format($stats['total_collected'])" icon="chart" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Collected This Month" :value="\App\Helpers\CurrencyHelper::format($stats['this_month'])" icon="bolt" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Unallocated Credits" :value="\App\Helpers\CurrencyHelper::format($stats['unallocated_credits'])" icon="shield" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Total Transactions" :value="number_format($stats['total_transactions'])" icon="users" accent="bg-cyan-500/10 text-cyan-600 dark:text-cyan-400" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search reference or customer…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="method" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Methods</option>
            @foreach (\App\Enums\PaymentMethod::cases() as $m) <option value="{{ $m->value }}">{{ $m->label() }}</option> @endforeach
        </select>
    </div>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Reference</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Amount</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Method</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($payments as $payment)
                <tr wire:key="{{ $payment->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $payment->reference }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $payment->customer?->displayName() ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-green-600 dark:text-green-400">+{{ \App\Helpers\CurrencyHelper::format((float) $payment->amount) }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $payment->method()->label() }} <span class="text-xs text-gray-400">{{ $payment->reference_number ? '('.e($payment->reference_number).')' : '' }}</span></td>
                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_date?->format('M d, Y') }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $payment->status()->badge() }}">{{ $payment->status()->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No payments recorded yet.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>
    <div class="mt-4">{{ $payments->links() }}</div>
</div>
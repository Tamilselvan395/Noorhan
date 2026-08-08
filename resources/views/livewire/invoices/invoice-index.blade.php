<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoices</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Billing & accounts receivable.</p>
        </div>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <x-dashboard.stat-card label="Total Invoiced" :value="\App\Helpers\CurrencyHelper::format($stats['total_invoiced'])" icon="chart" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Outstanding" :value="\App\Helpers\CurrencyHelper::format($stats['outstanding'])" icon="bolt" accent="bg-amber-500/10 text-amber-600 dark:text-amber-400" />
        <x-dashboard.stat-card label="Overdue" :value="\App\Helpers\CurrencyHelper::format($stats['overdue'])" icon="shield" accent="bg-red-500/10 text-red-600 dark:text-red-400" />
        <x-dashboard.stat-card label="Collected This Month" :value="\App\Helpers\CurrencyHelper::format($stats['paid_month'])" icon="users" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search reference…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="status" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Statuses</option>
            @foreach (\App\Enums\InvoiceStatus::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
        </select>
    </div>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Reference</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Balance Due</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Due Date</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($invoices as $invoice)
                <tr wire:key="{{ $invoice->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('invoices.show', $invoice) }}'">
                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice->reference }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $invoice->customer?->displayName() ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ \App\Helpers\CurrencyHelper::format((float) $invoice->total) }}</td>
                    <td class="px-6 py-3 text-sm font-bold {{ (float) $invoice->balance_due > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}">{{ \App\Helpers\CurrencyHelper::format((float) $invoice->balance_due) }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $invoice->status()->badge() }}">{{ $invoice->status()->label() }}</span></td>
                    <td class="px-6 py-3 text-sm {{ $invoice->isOverdue() ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No invoices yet. Generate one from a Sales Order.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>
    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
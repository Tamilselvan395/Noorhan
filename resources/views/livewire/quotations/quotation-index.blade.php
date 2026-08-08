<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Quotations</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Professional quotations with margin control & approvals.</p>
        </div>
        <a href="{{ route('quotations.create') }}" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">+ New Quotation</a>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <x-dashboard.stat-card label="Open Quotations" :value="number_format($stats['open'])" icon="chart" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Open Value" :value="\App\Helpers\CurrencyHelper::format($stats['open_value'])" icon="bolt" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Win Rate" :value="$stats['win_rate'].'%'" hint="{$stats['accepted']} accepted" icon="shield" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Awaiting Approval" :value="number_format($stats['pending_approval'])" icon="users" accent="bg-amber-500/10 text-amber-600 dark:text-amber-400" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search reference…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="status" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Statuses</option>
            @foreach (\App\Enums\QuotationStatus::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
        </select>
    </div>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Reference</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer / Lead</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Margin</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Valid Until</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($quotations as $quotation)
                <tr wire:key="{{ $quotation->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('quotations.show', $quotation) }}'">
                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $quotation->reference }} <span class="text-xs text-gray-400">v{{ $quotation->version }}</span></td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $quotation->customer?->name ?? ($quotation->lead ? 'Lead: '.$quotation->lead->name : '—') }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ \App\Helpers\CurrencyHelper::format((float) $quotation->total) }}</td>
                    <td class="px-6 py-3 text-sm {{ (float) $quotation->margin_percent < (float) config('noorhan.quotation.min_margin') ? 'text-red-500 font-medium' : 'text-green-600 dark:text-green-400' }}">{{ $quotation->margin_percent }}%</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $quotation->status()->badge() }}">{{ $quotation->status()->label() }}</span></td>
                    <td class="px-6 py-3 text-sm {{ $quotation->isExpired() ? 'text-red-500' : 'text-gray-500 dark:text-gray-400' }}">{{ $quotation->valid_until?->format('M d, Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No quotations yet.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>
    <div class="mt-4">{{ $quotations->links() }}</div>
</div>
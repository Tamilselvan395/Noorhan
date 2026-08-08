<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Supplier Enquiries</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">RFQs raised with suppliers — responses feed the Quotation Builder.</p>
        </div>
        <button wire:click="$dispatch('open-enquiry-form', { supplierId: null, leadId: null })" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">+ New Enquiry</button>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <x-dashboard.stat-card label="Total RFQs" :value="number_format($stats['total'])" icon="chart" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Awaiting Response" :value="number_format($stats['open'])" icon="bolt" accent="bg-amber-500/10 text-amber-600 dark:text-amber-400" />
        <x-dashboard.stat-card label="Fully Quoted" :value="number_format($stats['quoted'])" icon="shield" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Avg Response" :value="$stats['avg_response_h'] !== null ? $stats['avg_response_h'].'h' : '—'" icon="users" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search reference…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="status" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Statuses</option>
            @foreach (\App\Enums\SupplierEnquiryStatus::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
        </select>
        <select wire:model.live="supplierId" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Suppliers</option>
            @foreach ($suppliers as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
        </select>
    </div>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Reference</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Supplier</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Lead</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Items</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Response Time</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($enquiries as $enquiry)
                <tr wire:key="{{ $enquiry->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('supplier-enquiries.show', $enquiry) }}'">
                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $enquiry->reference }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $enquiry->supplier->name }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $enquiry->lead ? '#'.$enquiry->lead->id.' '.$enquiry->lead->name : '—' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $enquiry->items_count }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $enquiry->status()->badge() }}">{{ $enquiry->status()->label() }}</span></td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $enquiry->responseTimeHours() !== null ? $enquiry->responseTimeHours().'h' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No enquiries yet. Raise one from a Lead or Supplier page.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>
    <div class="mt-4">{{ $enquiries->links() }}</div>
</div>
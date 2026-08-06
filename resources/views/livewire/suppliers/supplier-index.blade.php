<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Suppliers</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Global sourcing directory with price lists & performance ratings.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('suppliers.compare') }}" class="py-2 px-4 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Compare</a>
            <button wire:click="$dispatch('open-supplier-form')" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">+ New Supplier</button>
        </div>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <x-dashboard.stat-card label="Total Suppliers" :value="number_format($stats['total'])" icon="chart" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Active" :value="number_format($stats['active'])" icon="shield" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Rated" :value="number_format($stats['rated'])" icon="bolt" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Countries" :value="number_format($stats['countries'])" icon="users" accent="bg-cyan-500/10 text-cyan-600 dark:text-cyan-400" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search suppliers…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="division" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Divisions</option>
            @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
        </select>
        <select wire:model.live="status" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Statuses</option>
            @foreach (\App\Enums\CustomerStatus::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
        </select>
    </div>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Supplier</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Division</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Country</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Payment Terms</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Rating</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($suppliers as $supplier)
                <tr wire:key="{{ $supplier->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('suppliers.show', $supplier) }}'">
                    <td class="px-6 py-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $supplier->name }}</p>
                        <p class="text-xs text-gray-400">{{ $supplier->contact_person ?? $supplier->email }}</p>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $supplier->division()->label() }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $supplier->country ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $supplier->payment_terms ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $supplier->overallRating() !== null ? '★ '.$supplier->overallRating() : '—' }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $supplier->status()->badge() }}">{{ $supplier->status()->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No suppliers yet.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>
    <div class="mt-4">{{ $suppliers->links() }}</div>
</div>
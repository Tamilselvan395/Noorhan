<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customers</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Shared across all divisions, orders, invoices and campaigns.</p>
        </div>
        <button wire:click="$dispatch('open-customer-form')" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">+ New Customer</button>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <x-dashboard.stat-card label="Total Customers" :value="number_format($stats['total'])" icon="users" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Active" :value="number_format($stats['active'])" icon="shield" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="New This Month" :value="number_format($stats['new_this_month'])" icon="bolt" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Blacklisted" :value="number_format($stats['blacklisted'])" icon="shield" accent="bg-red-500/10 text-red-600 dark:text-red-400" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search customers…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="type" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Types</option>
            @foreach (\App\Enums\CustomerType::cases() as $t) <option value="{{ $t->value }}">{{ $t->label() }}</option> @endforeach
        </select>
        <select wire:model.live="status" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Statuses</option>
            @foreach (\App\Enums\CustomerStatus::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
        </select>
        <select wire:model.live="division" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Divisions</option>
            @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
        </select>
    </div>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Division</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Outstanding</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Owner</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($customers as $customer)
                <tr wire:key="{{ $customer->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('customers.show', $customer) }}'">
                    <td class="px-6 py-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $customer->name }}</p>
                        <p class="text-xs text-gray-400">{{ $customer->company_name ?? $customer->email ?? $customer->phone }}</p>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $customer->type()->label() }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $customer->division()->label() }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $customer->status()->badge() }}">{{ $customer->status()->label() }}</span></td>
                    <td class="px-6 py-3 text-sm font-medium {{ (float) $customer->outstanding_balance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500' }}">
                        {{ \App\Helpers\CurrencyHelper::format((float) $customer->outstanding_balance) }}
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $customer->owner?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No customers yet. Convert a lead or create one manually.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>
    <div class="mt-4">{{ $customers->links() }}</div>
</div>
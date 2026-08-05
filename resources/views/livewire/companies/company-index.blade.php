<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Companies</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">B2B organizations — garages, distributors, dealers, workshops & corporates.</p>
        </div>
        <button wire:click="$dispatch('open-company-form')" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">+ New Company</button>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <x-dashboard.stat-card label="Total Companies" :value="number_format($stats['total'])" icon="chart" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Active" :value="number_format($stats['active'])" icon="shield" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Channel Partners" :value="number_format($stats['partners'])" hint="distributors & dealers" icon="bolt" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Linked Contacts" :value="number_format($stats['contacts'])" icon="users" accent="bg-cyan-500/10 text-cyan-600 dark:text-cyan-400" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search companies…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="type" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Types</option>
            @foreach (\App\Enums\CustomerType::cases() as $t) <option value="{{ $t->value }}">{{ $t->label() }}</option> @endforeach
        </select>
        <select wire:model.live="status" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Statuses</option>
            @foreach (\App\Enums\CustomerStatus::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
        </select>
    </div>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Division</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Contacts</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Owner</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($companies as $company)
                <tr wire:key="{{ $company->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('companies.show', $company) }}'">
                    <td class="px-6 py-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $company->name }}</p>
                        <p class="text-xs text-gray-400">{{ $company->city ?? $company->email ?? '—' }}</p>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $company->type()->label() }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $company->division()->label() }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $company->contacts_count }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $company->status()->badge() }}">{{ $company->status()->label() }}</span></td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $company->owner?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No companies yet.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>
    <div class="mt-4">{{ $companies->links() }}</div>
</div>
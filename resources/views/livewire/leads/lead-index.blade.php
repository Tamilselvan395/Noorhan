<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Leads</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pipeline across all Noorhan Group divisions.</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-1">
                <button wire:click="toggleView('table')" class="px-3 py-1.5 text-xs font-semibold rounded-md {{ $view === 'table' ? 'bg-blue-600 text-white' : 'text-gray-600 dark:text-gray-300' }}">Table</button>
                <button wire:click="toggleView('kanban')" class="px-3 py-1.5 text-xs font-semibold rounded-md {{ $view === 'kanban' ? 'bg-blue-600 text-white' : 'text-gray-600 dark:text-gray-300' }}">Kanban</button>
            </div>
            <button wire:click="$dispatch('open-lead-form')" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">+ New Lead</button>
        </div>
    </div>

    @php $stats = $this->stats(); @endphp

    {{-- KPI Row --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <x-dashboard.stat-card label="Open Leads" :value="number_format($stats['open_count'])" icon="bolt" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Pipeline Value" :value="\App\Helpers\CurrencyHelper::format($stats['pipeline_value'])" icon="chart" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Won This Month" :value="number_format($stats['won_this_month'])" icon="shield" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Conversion" :value="$stats['conversion_rate'].'%'" icon="chart" accent="bg-cyan-500/10 text-cyan-600 dark:text-cyan-400" />
        <x-dashboard.stat-card label="Follow-ups Due" :value="number_format($stats['follow_ups_due'])" icon="bolt" accent="bg-amber-500/10 text-amber-600 dark:text-amber-400" />
        <div class="cursor-pointer" wire:click="$set('triageOnly', {{ $triageOnly ? 'false' : 'true' }})">
            <x-dashboard.stat-card label="Triage Queue" :value="number_format($stats['triage_count'])" icon="shield" accent="{{ $triageOnly ? 'bg-red-500/20 text-red-600 dark:text-red-400' : 'bg-red-500/10 text-red-600 dark:text-red-400' }}" />
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search leads…"
               class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="status" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Stages</option>
            @foreach (\App\Enums\LeadStatus::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
        </select>
        <select wire:model.live="division" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Divisions</option>
            @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
        </select>
        <select wire:model.live="source" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Sources</option>
            @foreach (\App\Enums\LeadSource::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
        </select>
        <select wire:model.live="priority" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Priorities</option>
            @foreach (\App\Enums\LeadPriority::cases() as $p) <option value="{{ $p->value }}">{{ $p->label() }}</option> @endforeach
        </select>
        <select wire:model.live="assignment" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">Everyone</option>
            <option value="mine">My Leads</option>
            <option value="unassigned">Unassigned</option>
        </select>
    </div>

    {{-- Table / Kanban --}}
    @if ($view === 'table')
        <x-table>
            <x-slot:head>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Lead</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Division</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Stage</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Priority</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Value</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Assignee</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Follow-up</th>
            </x-slot:head>
            <x-slot:body>
                @forelse ($leads as $lead)
                    <tr wire:key="{{ $lead->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('leads.show', $lead) }}'">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $lead->name }}
                                @if ($lead->needs_triage) <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">TRIAGE</span> @endif
                            </p>
                            <p class="text-xs text-gray-400">{{ $lead->company_name ?? $lead->email ?? $lead->phone }}</p>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $lead->division()->label() }}</td>
                        <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $lead->status()->badge() }}">{{ $lead->status()->label() }}</span></td>
                        <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $lead->priority()->badge() }}">{{ $lead->priority()->label() }}</span></td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $lead->estimated_value ? \App\Helpers\CurrencyHelper::format((float) $lead->estimated_value) : '—' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $lead->assignee?->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm {{ $lead->next_follow_up_at?->isPast() && $lead->isOpen() ? 'text-red-500 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $lead->next_follow_up_at?->format('M d, h:i A') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400">No leads found. Create your first lead to get started.</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>
        <div class="mt-4">{{ $leads->links() }}</div>
    @else
        <livewire:leads.lead-kanban />
    @endif
</div>
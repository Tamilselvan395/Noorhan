<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Trail</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Immutable data mutations with field-level diffs. Retention: {{ config('noorhan.audit.retention_days') }} days.</p>
        </div>
        <a href="{{ route('system.audit.export', ['from' => $from, 'to' => $to]) }}" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">Export CSV</a>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-3 gap-4">
        <x-dashboard.stat-card label="Events Today" :value="number_format($stats['today'])" icon="shield" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Updates (Month)" :value="number_format($stats['updates'])" icon="chart" accent="bg-amber-500/10 text-amber-600 dark:text-amber-400" />
        <x-dashboard.stat-card label="Deletes (Month)" :value="number_format($stats['deletes'])" icon="bolt" accent="bg-red-500/10 text-red-600 dark:text-red-400" />
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-2 md:grid-cols-5 gap-3">
        <select wire:model.live="user" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Users</option>
            @foreach ($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
        </select>
        <select wire:model.live="event" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Events</option>
            <option value="created">Created</option>
            <option value="updated">Updated</option>
            <option value="deleted">Deleted</option>
        </select>
        <select wire:model.live="type" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Entities</option>
            @foreach ($types as $class => $label) <option value="{{ $class }}">{{ $label }}</option> @endforeach
        </select>
        <input type="date" wire:model.live="from" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
        <input type="date" wire:model.live="to" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
    </div>

    <x-card>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($logs as $log)
                <li wire:key="audit-{{ $log->id }}">
                    <button wire:click="toggle({{ $log->id }})" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <div class="flex items-center space-x-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $log->event === 'created' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : ($log->event === 'deleted' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400') }}">
                                {{ strtoupper($log->event) }}
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $log->user?->name ?? 'System' }} · {{ $log->ip_address }} · {{ $log->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transform transition {{ $expandedId === $log->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    @if ($expandedId === $log->id)
                        <div class="px-4 pb-4 bg-gray-50 dark:bg-gray-700/30">
                            @php $changes = \App\Helpers\AuditDiffHelper::changes($log); @endphp
                            @if ($changes !== [])
                                <table class="min-w-full text-sm mt-2">
                                    <thead><tr class="text-left text-xs text-gray-400 uppercase">
                                        <th class="py-1 pr-4">Field</th><th class="py-1 pr-4">Old</th><th class="py-1">New</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($changes as $field => $change)
                                            <tr>
                                                <td class="py-1.5 pr-4 font-medium text-gray-700 dark:text-gray-200">{{ $field }}</td>
                                                <td class="py-1.5 pr-4 text-red-600 dark:text-red-400">{{ var_export($change['old'], true) }}</td>
                                                <td class="py-1.5 text-green-600 dark:text-green-400">{{ var_export($change['new'], true) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @elseif ($log->event === 'created')
                                <p class="text-xs text-gray-400 mt-2">Record created with {{ count($log->new_values ?? []) }} attributes.</p>
                            @elseif ($log->event === 'deleted')
                                <p class="text-xs text-gray-400 mt-2">Record deleted (snapshot of {{ count($log->old_values ?? []) }} attributes stored).</p>
                            @endif
                        </div>
                    @endif
                </li>
            @empty
                <li class="p-10 text-center text-sm text-gray-400">No audit events match the filters.</li>
            @endforelse
        </ul>
        <div class="p-4">{{ $logs->links() }}</div>
    </x-card>
</div>
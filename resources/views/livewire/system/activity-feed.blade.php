<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Activity Log</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Every human action across the CRM, in one feed.</p>
        </div>
        <a href="{{ route('system.activity.export', ['from' => $from, 'to' => $to]) }}" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">Export CSV</a>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-3 gap-4">
        <x-dashboard.stat-card label="Today" :value="number_format($stats['today'])" icon="bolt" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="This Week" :value="number_format($stats['week'])" icon="chart" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Most Active (Month)" :value="$stats['most_active']" icon="users" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-2 md:grid-cols-5 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search description…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="user" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Users</option>
            @foreach ($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
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
            @forelse ($activities as $activity)
                <li class="flex space-x-3 p-4" wire:key="act-{{ $activity->id }}">
                    <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-800 dark:text-gray-200">
                            <span class="font-semibold">{{ $activity->user?->name ?? 'System' }}</span> {{ $activity->description }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ class_basename((string) $activity->subject_type) }} #{{ $activity->subject_id }} · {{ $activity->created_at->format('M d, Y h:i A') }} · {{ $activity->created_at->diffForHumans() }}
                        </p>
                    </div>
                </li>
            @empty
                <li class="p-10 text-center text-sm text-gray-400">No activity matches the filters.</li>
            @endforelse
        </ul>
        <div class="p-4">{{ $activities->links() }}</div>
    </x-card>
</div>
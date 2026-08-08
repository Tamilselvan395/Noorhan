<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Task Scheduler</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">All recurring jobs — digests, automations, expiries, retries & pruning — with execution history.</p>
    </div>

    <x-card>
        <x-slot:header><h3 class="font-semibold">Registered Tasks</h3></x-slot:header>
        <x-table>
            <x-slot:head>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Task</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Command</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Frequency</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Last Run</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Action</th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($this->tasks() as $task)
                    <tr wire:key="task-{{ $task['key'] }}">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $task['label'] }}</p>
                            @if ($task['last_status'])
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $task['last_status'] === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' }}">{{ ucfirst($task['last_status']) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-xs font-mono text-gray-500 dark:text-gray-400">php artisan {{ $task['command'] }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $task['frequency'] }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $task['last_run'] ?? 'Never' }}</td>
                        <td class="px-6 py-3 text-right">
                            <button wire:click="run('{{ $task['key'] }})" wire:confirm="Run {{ $task['label'] }} now?" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold">Run Now</button>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </x-card>

    <x-card>
        <x-slot:header><h3 class="font-semibold">Run History</h3></x-slot:header>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($logs as $log)
                <li class="flex items-center justify-between p-4" wire:key="sl-{{ $log->id }}">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $log->task }}
                            <span class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold {{ $log->trigger === 'manual' ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">{{ strtoupper($log->trigger) }}</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $log->finished_at?->format('M d, Y h:i A') }}
                            @if ($log->started_at && $log->finished_at) · {{ $log->started_at->diffInSeconds($log->finished_at) }}s @endif
                            @if ($log->output) · <span class="font-mono">{{ \Illuminate\Support\Str::limit($log->output, 80) }}</span> @endif
                        </p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $log->status === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' }}">{{ ucfirst($log->status) }}</span>
                </li>
            @empty
                <li class="p-8 text-center text-sm text-gray-400">No runs recorded yet.</li>
            @endforelse
        </ul>
    </x-card>
</div>
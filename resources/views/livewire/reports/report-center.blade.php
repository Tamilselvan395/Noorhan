<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Report Center</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Executive analytics across all divisions — exportable to CSV.</p>
        </div>
        <div class="flex items-center space-x-2">
            <input type="date" wire:model.live="from" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <span class="text-gray-400 text-sm">→</span>
            <input type="date" wire:model.live="to" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <a href="{{ route('reports.export', ['key' => $report->key(), 'from' => $from, 'to' => $to]) }}"
               class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">Export CSV</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Report picker --}}
        <div class="space-y-4">
            @foreach ($grouped as $group => $reports)
                <x-card>
                    <x-slot:header><h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $group }}</h3></x-slot:header>
                    <ul class="p-2 space-y-1">
                        @foreach ($reports as $r)
                            <li>
                                <button wire:click="selectReport('{{ $r->key() }}')"
                                        class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition {{ $report->key() === $r->key() ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                    {{ $r->label() }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endforeach
        </div>

        {{-- Report body --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Summary KPIs --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($summary as $label => $value)
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $label }}</p>
                        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Table --}}
            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold">{{ $report->label() }}</h3>
                        <span class="text-xs text-gray-400">{{ count($rows) }} rows · {{ \Illuminate\Support\Carbon::parse($from)->format('M d') }} – {{ \Illuminate\Support\Carbon::parse($to)->format('M d, Y') }}</span>
                    </div>
                </x-slot:header>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                @foreach ($columns as $column)
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($rows as $row)
                                <tr>
                                    @foreach ($row as $cell)
                                        <td class="px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($columns) }}" class="px-4 py-10 text-center text-sm text-gray-400">No data in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</div>
<x-card>
    <x-slot:header><h3 class="font-semibold">Login History</h3></x-slot:header>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Event</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Device</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">IP Address</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">When</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($histories as $h)
                <tr wire:key="{{ $h->id }}">
                    <td class="px-6 py-3 text-sm">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ \App\Enums\LoginHistoryType::from($h->type)->badgeClasses() }}">
                            {{ \App\Enums\LoginHistoryType::from($h->type)->label() }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $h->browser }} · {{ $h->platform }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $h->ip_address }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $h->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">No login activity yet.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>

    <div class="p-4">{{ $histories->links() }}</div>
</x-card>
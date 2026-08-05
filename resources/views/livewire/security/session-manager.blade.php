<x-card>
    <x-slot:header>
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold">Active Sessions</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Last login: {{ auth()->user()->last_login_at?->diffForHumans() }} · {{ auth()->user()->last_login_ip }}
                </p>
            </div>
            @if ($sessions->count() > 1)
                <button wire:click="revokeOthers" wire:confirm="Sign out all other devices?"
                        class="text-xs font-medium text-red-600 dark:text-red-400 hover:underline">Sign out all others</button>
            @endif
        </div>
    </x-slot:header>

    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
        @foreach ($sessions as $session)
            <li class="flex items-center justify-between p-4" wire:key="{{ $session->id }}">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-300">
                        @if ($session->agent['device'] === 'Mobile')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium">
                            {{ $session->agent['browser'] }} on {{ $session->agent['platform'] }}
                            @if ($session->is_current)
                                <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">This device</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-400">{{ $session->ip_address }} · {{ $session->last_activity->diffForHumans() }}</p>
                    </div>
                </div>
                @unless ($session->is_current)
                    <button wire:click="revoke('{{ $session->id }}')" wire:confirm="Revoke this session?"
                            class="text-xs font-medium text-red-600 dark:text-red-400 hover:underline">Revoke</button>
                @endunless
            </li>
        @endforeach
    </ul>
</x-card>
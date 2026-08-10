<x-card>
    <x-slot:header><h3 class="font-semibold">Personal Access Tokens</h3></x-slot:header>

    @if ($newToken)
        <div class="mx-4 mt-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800">
            <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 mb-1">Copy now — shown only once:</p>
            <code class="text-xs text-gray-800 dark:text-gray-200 break-all">{{ $newToken }}</code>
        </div>
    @endif

    <form wire:submit="createToken" class="p-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Token Name</label>
            <input wire:model="name" placeholder="e.g. Zapier integration" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center text-sm text-gray-700 dark:text-gray-300 pb-2">
            <input type="checkbox" wire:model="canRead" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span class="ml-2">Read</span>
        </label>
        <div class="flex items-center justify-between">
            <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" wire:model="canWrite" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span class="ml-2">Write</span>
            </label>
            <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Create</button>
        </div>
    </form>

    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse ($tokens as $token)
            <li class="flex items-center justify-between p-4" wire:key="tok-{{ $token->id }}">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $token->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Abilities: {{ implode(', ', $token->abilities) }} ·
                        Last used: {{ $token->last_used_at?->diffForHumans() ?? 'never' }} ·
                        Created: {{ $token->created_at->format('M d, Y') }}
                    </p>
                </div>
                <button wire:click="revoke({{ $token->id }})" wire:confirm="Revoke this token?" class="text-xs font-medium text-red-600 dark:text-red-400 hover:underline">Revoke</button>
            </li>
        @empty
            <li class="p-8 text-center text-sm text-gray-400">No tokens issued.</li>
        @endforelse
    </ul>
</x-card>
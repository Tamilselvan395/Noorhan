<div class="space-y-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-3 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search subject or body…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="channel" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Channels</option>
            @foreach (['email' => 'Email', 'phone' => 'Phone', 'whatsapp' => 'WhatsApp', 'meeting' => 'Meeting', 'sms' => 'SMS'] as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="direction" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">Inbound + Outbound</option>
            <option value="inbound">Inbound</option>
            <option value="outbound">Outbound</option>
        </select>
    </div>

    <x-card>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($communications as $communication)
                <li class="p-4" wire:key="cc-{{ $communication->id }}">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $communication->direction === 'inbound' ? 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' }}">{{ $communication->channel }}</span>
                            {{ $communication->subject ?? '(no subject)' }}
                        </p>
                        <span class="text-xs text-gray-400">{{ $communication->occurred_at?->diffForHumans() }}</span>
                    </div>
                    @if ($communication->body)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $communication->body }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-400">
                        {{ $communication->user?->name ?? 'System' }} ·
                        @php $link = $this->entityLink($communication); @endphp
                        @if ($link)
                            <a href="{{ $link }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ class_basename($communication->communicable_type) }}: {{ $communication->communicable?->name }}</a>
                        @endif
                    </p>
                </li>
            @empty
                <li class="p-10 text-center text-sm text-gray-400">No communications match the filters.</li>
            @endforelse
        </ul>
        <div class="p-4">{{ $communications->links() }}</div>
    </x-card>
</div>
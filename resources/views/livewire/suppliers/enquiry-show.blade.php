<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('supplier-enquiries.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $enquiry->reference }}</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $enquiry->status()->badge() }}">{{ $enquiry->status()->label() }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $enquiry->supplier->name }}
                @if ($enquiry->lead) · Lead: <a href="{{ route('leads.show', $enquiry->lead) }}" class="text-blue-600 dark:text-blue-400 hover:underline">#{{ $enquiry->lead->id }} {{ $enquiry->lead->name }}</a> @endif
                · Raised by {{ $enquiry->creator?->name ?? 'System' }}
            </p>
        </div>
        <div class="flex items-center space-x-2">
            @if ($enquiry->status()->value === 'draft')
                <select wire:model="sendVia" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                    <option value="email">Email</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="phone">Phone</option>
                </select>
                <button wire:click="send" class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold">Mark Sent</button>
            @endif
            @if (! in_array($enquiry->status()->value, ['closed', 'cancelled']))
                <button wire:click="close(false)" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Close</button>
                <button wire:click="close(true)" wire:confirm="Cancel this enquiry?" class="px-3 py-2 rounded-lg text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">Cancel</button>
            @endif
        </div>
    </div>

    {{-- Meta strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"><p class="text-gray-400 text-xs">Sent</p><p class="font-medium mt-1 text-gray-900 dark:text-white">{{ $enquiry->sent_at?->format('M d, h:i A') ?? '—' }} {{ $enquiry->sent_via ? 'via '.ucfirst($enquiry->sent_via) : '' }}</p></div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"><p class="text-gray-400 text-xs">First Response</p><p class="font-medium mt-1 text-gray-900 dark:text-white">{{ $enquiry->responded_at?->diffForHumans() ?? '—' }}</p></div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"><p class="text-gray-400 text-xs">Response Time</p><p class="font-medium mt-1 text-gray-900 dark:text-white">{{ $enquiry->responseTimeHours() !== null ? $enquiry->responseTimeHours().' hours' : '—' }}</p></div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"><p class="text-gray-400 text-xs">Supplier Currency</p><p class="font-medium mt-1 text-gray-900 dark:text-white">{{ $enquiry->supplier->currency }}</p></div>
    </div>

    {{-- Items + response capture --}}
    <x-card>
        <x-slot:header><h3 class="font-semibold">Requested Items & Supplier Responses</h3></x-slot:header>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Item</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Offered Price</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Lead / Valid</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Record Response</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($items as $item)
                        <tr wire:key="ei-{{ $item->id }}">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->description }}</p>
                                <p class="text-xs text-gray-400">{{ $item->product?->sku ?? 'Custom item' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $item->quantity }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $item->status()->badge() }}">{{ $item->status()->label() }}</span></td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $item->offered_price !== null ? $item->offered_currency.' '.number_format((float) $item->offered_price, 2) : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $item->lead_time_days !== null ? $item->lead_time_days.'d' : '—' }} · {{ $item->valid_until?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if (! in_array($enquiry->status()->value, ['closed', 'cancelled']))
                                    <div class="space-y-2 min-w-[220px]">
                                        <div class="flex space-x-2">
                                            <input wire:model="responses.{{ $item->id }}.price" type="number" step="0.01" min="0" placeholder="Price" class="w-24 px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                                            <input wire:model="responses.{{ $item->id }}.lead" type="number" min="0" placeholder="Days" class="w-16 px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                                            <select wire:model="responses.{{ $item->id }}.status" class="px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                                                <option value="quoted">Quoted</option>
                                                <option value="declined">Declined</option>
                                            </select>
                                        </div>
                                        <div class="flex space-x-2">
                                            <input wire:model="responses.{{ $item->id }}.valid" type="date" class="px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                                            <button wire:click="recordResponse({{ $item->id }})" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold">Save</button>
                                        </div>
                                        @error("responses.{$item->id}.price") <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($enquiry->notes) <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ $enquiry->notes }}</p> @endif
    </x-card>

    {{-- Timeline --}}
    <x-card>
        <x-slot:header><h3 class="font-semibold">Activity Timeline</h3></x-slot:header>
        <ul class="space-y-4">
            @forelse ($timeline as $activity)
                <li class="flex space-x-3" wire:key="a-{{ $activity->id }}">
                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-800 dark:text-gray-200"><span class="font-medium">{{ $activity->user?->name ?? 'System' }}</span> {{ $activity->description }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </li>
            @empty
                <li class="text-sm text-gray-400">No activity yet.</li>
            @endforelse
        </ul>
    </x-card>
</div>
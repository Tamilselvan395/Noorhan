<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('suppliers.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $supplier->name }}</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $supplier->status()->badge() }}">{{ $supplier->status()->label() }}</span>
                @if ($supplier->overallRating()) <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">★ {{ $supplier->overallRating() }}</span> @endif
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $supplier->division()->label() }} · {{ $supplier->country ?? '—' }} · {{ $supplier->payment_terms ?? 'No terms' }}</p>
        </div>
        <button wire:click="$dispatch('open-enquiry-form', { supplierId: {{ $supplier->id }}, leadId: null })" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">+ New Enquiry</button>
    </div>

    <div class="flex space-x-1 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        @foreach (['overview' => 'Overview', 'contacts' => 'Contacts', 'prices' => 'Price Lists', 'ratings' => 'Ratings & Performance', 'documents' => 'Documents', 'timeline' => 'Timeline'] as $key => $label)
            <button wire:click="switchTab('{{ $key }})" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px whitespace-nowrap transition {{ $tab === $key ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($tab === 'overview')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Contact</h3></x-slot:header>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-400">Email</dt><dd class="text-gray-800 dark:text-gray-200">{{ $supplier->email ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Phone</dt><dd class="text-gray-800 dark:text-gray-200">{{ $supplier->phone ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">WhatsApp</dt><dd class="text-gray-800 dark:text-gray-200">{{ $supplier->whatsapp ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Website</dt><dd class="text-gray-800 dark:text-gray-200">{{ $supplier->website ?? '—' }}</dd></div>
                </dl>
            </x-card>
            <x-card>
                <x-slot:header><h3 class="font-semibold">Commercials</h3></x-slot:header>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-400">Currency</dt><dd class="text-gray-800 dark:text-gray-200">{{ $supplier->currency }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Payment Terms</dt><dd class="text-gray-800 dark:text-gray-200">{{ $supplier->payment_terms ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Avg Lead Time</dt><dd class="text-gray-800 dark:text-gray-200">{{ $supplier->averageLeadTime() !== null ? $supplier->averageLeadTime().' days' : '—' }}</dd></div>
                </dl>
            </x-card>
            <x-card>
                <x-slot:header><h3 class="font-semibold">Performance Snapshot</h3></x-slot:header>
                @php $breakdown = $supplier->ratingBreakdown(); @endphp
                @if ($breakdown['quality'] !== null)
                    <dl class="text-sm space-y-2">
                        @foreach ($breakdown as $dimension => $score)
                            <div class="flex justify-between items-center"><dt class="text-gray-400 capitalize">{{ $dimension }}</dt>
                                <dd class="w-2/3"><div class="h-2 rounded-full bg-gray-200 dark:bg-gray-700"><div class="h-2 rounded-full bg-amber-500" style="width: {{ ($score / 5) * 100 }}%"></div></div></dd>
                                <dd class="text-gray-800 dark:text-gray-200">{{ $score }}/5</dd></div>
                        @endforeach
                    </dl>
                @else
                    <p class="text-sm text-gray-400">No ratings yet.</p>
                @endif
            </x-card>
        </div>

    @elseif ($tab === 'contacts')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Add Contact</h3></x-slot:header>
                <form wire:submit="addContact" class="space-y-3">
                    <input wire:model="contact_name" placeholder="Name *" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('contact_name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    <input wire:model="contact_position" placeholder="Position" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <input wire:model="contact_email" placeholder="Email" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <input wire:model="contact_phone" placeholder="Phone" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="contact_primary" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2">Primary contact</span>
                    </label>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Add</button>
                </form>
            </x-card>
            <div class="lg:col-span-2">
                <x-card>
                    <x-slot:header><h3 class="font-semibold">Contacts</h3></x-slot:header>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($contacts as $contact)
                            <li class="flex items-center justify-between p-4" wire:key="sc-{{ $contact->id }}">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $contact->name }}
                                        @if ($contact->is_primary) <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">PRIMARY</span> @endif
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $contact->position ?? '' }} · {{ $contact->email ?? $contact->phone ?? '—' }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="p-8 text-center text-sm text-gray-400">No contacts yet.</li>
                        @endforelse
                    </ul>
                </x-card>
            </div>
        </div>

    @elseif ($tab === 'prices')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Add / Update Price</h3></x-slot:header>
                <form wire:submit="addPrice" class="space-y-3">
                    <select wire:model="price_product_id" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        <option value="">Select product…</option>
                        @foreach ($products as $p) <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option> @endforeach
                    </select>
                    @error('price_product_id') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    <div class="grid grid-cols-2 gap-3">
                        <input wire:model="price" type="number" step="0.01" min="0" placeholder="Price *" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <select wire:model="price_currency" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            @foreach (['USD','AED','EUR','CNY'] as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <input wire:model="min_qty" type="number" min="1" placeholder="Min Qty" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <input wire:model="lead_time_days" type="number" min="0" placeholder="Lead days" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <input wire:model="valid_until" type="date" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save Price</button>
                </form>
            </x-card>
            <div class="lg:col-span-2">
                <x-card>
                    <x-slot:header><h3 class="font-semibold">Current Price List</h3></x-slot:header>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($prices as $entry)
                            <li class="flex items-center justify-between p-4" wire:key="pl-{{ $entry->id }}">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $entry->product?->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $entry->product?->sku }} · MOQ {{ $entry->min_qty }} · {{ $entry->lead_time_days !== null ? $entry->lead_time_days.'d lead' : '—' }} · {{ $entry->valid_until ? 'valid until '.$entry->valid_until->format('M d, Y') : 'open-ended' }}</p>
                                </div>
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $entry->currency }} {{ number_format((float) $entry->price, 2) }}</span>
                            </li>
                        @empty
                            <li class="p-8 text-center text-sm text-gray-400">No prices recorded.</li>
                        @endforelse
                    </ul>
                </x-card>
            </div>
        </div>

    @elseif ($tab === 'ratings')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Rate Supplier</h3></x-slot:header>
                <form wire:submit="submitRating" class="space-y-3">
                    @foreach (['quality' => 'Quality', 'price' => 'Price Competitiveness', 'delivery' => 'Delivery', 'service' => 'Service'] as $field => $label)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}: {{ $this->{'r_'.$field} }}/5</label>
                            <input type="range" min="1" max="5" wire:model.live="r_{{ $field }}" class="w-full accent-blue-600">
                        </div>
                    @endforeach
                    <textarea wire:model="r_comment" rows="2" placeholder="Comment (optional)" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Submit Rating</button>
                </form>
            </x-card>
            <div class="lg:col-span-2">
                <x-card>
                    <x-slot:header><h3 class="font-semibold">Rating History</h3></x-slot:header>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($ratings as $rating)
                            <li class="p-4" wire:key="sr-{{ $rating->id }}">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">★ {{ $rating->overall() }}/5</p>
                                    <span class="text-xs text-gray-400">{{ $rating->user?->name ?? 'System' }} · {{ $rating->created_at->format('M d, Y') }}</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Q {{ $rating->quality }} · P {{ $rating->price }} · D {{ $rating->delivery }} · S {{ $rating->service }}</p>
                                @if ($rating->comment) <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $rating->comment }}</p> @endif
                            </li>
                        @empty
                            <li class="p-8 text-center text-sm text-gray-400">No ratings yet.</li>
                        @endforelse
                    </ul>
                </x-card>
            </div>
        </div>

    @elseif ($tab === 'documents')
        @include('documents._panel', ['entity' => $supplier])

    @else
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
    @endif
</div>
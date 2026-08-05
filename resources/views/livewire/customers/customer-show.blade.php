<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('customers.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $customer->displayName() }}</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $customer->status()->badge() }}">{{ $customer->status()->label() }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $customer->type()->label() }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $customer->division()->label() }} · Owner: {{ $customer->owner?->name ?? 'Unassigned' }}</p>
        </div>
        <button wire:click="$dispatch('open-customer-form', { customerId: {{ $customer->id }} })" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Edit</button>
    </div>

    {{-- Tabs --}}
    <div class="flex space-x-1 border-b border-gray-200 dark:border-gray-700">
        @foreach (['overview' => 'Overview', 'communications' => 'Communications', 'documents' => 'Documents', 'timeline' => 'Timeline', 'transactions' => 'Orders & Invoices'] as $key => $label)
            <button wire:click="switchTab('{{ $key }}')" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition {{ $tab === $key ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($tab === 'overview')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Contact</h3></x-slot:header>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-400">Email</dt><dd class="text-gray-800 dark:text-gray-200">{{ $customer->email ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Phone</dt><dd class="text-gray-800 dark:text-gray-200">{{ $customer->phone ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">WhatsApp</dt><dd class="text-gray-800 dark:text-gray-200">{{ $customer->whatsapp ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">City</dt><dd class="text-gray-800 dark:text-gray-200">{{ $customer->city ?? '—' }}, {{ $customer->country ?? '—' }}</dd></div>
                </dl>
            </x-card>
            <x-card>
                <x-slot:header><h3 class="font-semibold">Financial</h3></x-slot:header>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-400">Credit Limit</dt><dd class="text-gray-800 dark:text-gray-200">{{ $customer->credit_limit ? \App\Helpers\CurrencyHelper::format((float) $customer->credit_limit) : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Outstanding</dt><dd class="font-semibold {{ (float) $customer->outstanding_balance > 0 ? 'text-amber-600' : 'text-green-600' }}">{{ \App\Helpers\CurrencyHelper::format((float) $customer->outstanding_balance) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Last Activity</dt><dd class="text-gray-800 dark:text-gray-200">{{ $customer->last_activity_at?->diffForHumans() ?? '—' }}</dd></div>
                </dl>
                <p class="mt-3 text-[11px] text-gray-400">Balances sync automatically once the Zoho Books module is active.</p>
            </x-card>
            <x-card>
                <x-slot:header><h3 class="font-semibold">Origin</h3></x-slot:header>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-400">Source Lead</dt>
                        <dd>@if ($customer->lead) <a href="{{ route('leads.show', $customer->lead) }}" class="text-blue-600 dark:text-blue-400 hover:underline">#{{ $customer->lead->id }} {{ $customer->lead->name }}</a> @else — @endif</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Created</dt><dd class="text-gray-800 dark:text-gray-200">{{ $customer->created_at->format('M d, Y') }}</dd></div>
                </dl>
                @if ($customer->notes) <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ $customer->notes }}</p> @endif
            </x-card>
        </div>
    @elseif ($tab === 'communications')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Log Communication</h3></x-slot:header>
                <form wire:submit="addCommunication" class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <select wire:model="channel" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            @foreach (\App\Enums\CommunicationChannel::cases() as $c) <option value="{{ $c->value }}">{{ $c->label() }}</option> @endforeach
                        </select>
                        <select wire:model="direction" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            @foreach (\App\Enums\CommunicationDirection::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                        </select>
                    </div>
                    <input wire:model="subject" placeholder="Subject (optional)" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <textarea wire:model="body" rows="3" placeholder="Summary of the conversation…" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    @error('body') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Log</button>
                </form>
            </x-card>
            <div class="lg:col-span-2">
                <x-card>
                    <x-slot:header><h3 class="font-semibold">Communication History</h3></x-slot:header>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($communications as $comm)
                            <li class="p-4" wire:key="c-{{ $comm->id }}">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        {{ $comm->channel()->label() }}
                                        <span class="ml-1 text-xs {{ $comm->direction->value === 'inbound' ? 'text-cyan-600 dark:text-cyan-400' : 'text-gray-400' }}">({{ $comm->direction()->label() }})</span>
                                    </p>
                                    <span class="text-xs text-gray-400">{{ $comm->occurred_at?->diffForHumans() }}</span>
                                </div>
                                @if ($comm->subject) <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $comm->subject }}</p> @endif
                                @if ($comm->body) <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ $comm->body }}</p> @endif
                                <p class="mt-1 text-xs text-gray-400">by {{ $comm->user?->name ?? 'System' }}</p>
                            </li>
                        @empty
                            <li class="p-8 text-center text-sm text-gray-400">No communications logged yet.</li>
                        @endforelse
                    </ul>
                </x-card>
            </div>
        </div>
    @elseif ($tab === 'documents')
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold">Documents</h3>
                    <input type="file" wire:model="file" class="text-sm">
                </div>
                @error('file') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($documents as $doc)
                    <li class="flex items-center justify-between p-4" wire:key="d-{{ $doc->id }}">
                        <div>
                            <a href="{{ $doc->url() }}" target="_blank" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ $doc->name }}</a>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $doc->humanSize() }} · uploaded by {{ $doc->uploader?->name ?? 'System' }} · {{ $doc->created_at->format('M d, Y') }}</p>
                        </div>
                    </li>
                @empty
                    <li class="p-8 text-center text-sm text-gray-400">No documents uploaded.</li>
                @endforelse
            </ul>
        </x-card>
    @elseif ($tab === 'timeline')
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
    @else
        <x-card>
            <div class="p-8 text-center">
                <p class="text-sm text-gray-400">Sales Orders, Invoices and Payments will appear here once the Sales & Accounting modules ship.</p>
            </div>
        </x-card>
    @endif
</div>
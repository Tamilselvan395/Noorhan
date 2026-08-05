<div class="space-y-6">
    {{-- Triage banner --}}
    @if ($lead->needs_triage)
        <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-400">
            This lead is in the <strong>Triage Queue</strong> — classification incomplete. Assign an owner or complete the profile to release it.
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('leads.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $lead->name }}</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $lead->status()->badge() }}">{{ $lead->status()->label() }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $lead->priority()->badge() }}">{{ $lead->priority()->label() }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $lead->company_name }} · {{ $lead->division()->label() }} · {{ $lead->source()->label() }}</p>
        </div>
        <div class="flex items-center space-x-2">
            @foreach ($this->allowedTransitions() as $next)
                @if ($next !== \App\Enums\LeadStatus::Lost)
                    <button wire:click="moveStage('{{ $next->value }}')" class="px-3 py-2 rounded-lg text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">→ {{ $next->label() }}</button>
                @endif
            @endforeach
            @if (in_array(\App\Enums\LeadStatus::Lost, $this->allowedTransitions()))
                <button wire:click="openLostModal" class="px-3 py-2 rounded-lg text-xs font-semibold bg-red-600 hover:bg-red-700 text-white transition">Mark Lost</button>
            @endif
            @if (! $lead->customer_id)
                <button wire:click="convertToCustomer" class="px-3 py-2 rounded-lg text-xs font-semibold bg-green-600 hover:bg-green-700 text-white transition">Convert to Customer</button>
            @else
                <a href="{{ route('customers.show', $lead->customer_id) }}" class="px-3 py-2 rounded-lg text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">View Customer</a>
            @endif

            <button wire:click="$dispatch('open-lead-form', { leadId: {{ $lead->id }} })" class="px-3 py-2 rounded-lg text-xs font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Edit</button>
            <button wire:click="deleteLead" wire:confirm="Delete this lead permanently?" class="px-3 py-2 rounded-lg text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">Delete</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Enquiry Details</h3></x-slot:header>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-400">Email</dt><dd class="mt-0.5 font-medium text-gray-800 dark:text-gray-200">{{ $lead->email ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Phone</dt><dd class="mt-0.5 font-medium text-gray-800 dark:text-gray-200">{{ $lead->phone ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Vehicle Brand</dt><dd class="mt-0.5 font-medium text-gray-800 dark:text-gray-200">{{ $lead->vehicle_brand_category ? \App\Enums\VehicleBrandCategory::from($lead->vehicle_brand_category)->label() : '—' }}</dd></div>
                    <div><dt class="text-gray-400">Customer Type</dt><dd class="mt-0.5 font-medium text-gray-800 dark:text-gray-200">{{ $lead->customer_type ? \App\Enums\CustomerType::from($lead->customer_type)->label() : '—' }}</dd></div>
                    <div><dt class="text-gray-400">Estimated Value</dt><dd class="mt-0.5 font-medium text-gray-800 dark:text-gray-200">{{ $lead->estimated_value ? \App\Helpers\CurrencyHelper::format((float) $lead->estimated_value) : '—' }}</dd></div>
                    <div><dt class="text-gray-400">Next Follow-up</dt><dd class="mt-0.5 font-medium {{ $lead->next_follow_up_at?->isPast() && $lead->isOpen() ? 'text-red-500' : 'text-gray-800 dark:text-gray-200' }}">{{ $lead->next_follow_up_at?->format('M d, Y h:i A') ?? '—' }}</dd></div>
                    @if ($lead->lost_reason)
                        <div class="col-span-2"><dt class="text-gray-400">Lost Reason</dt><dd class="mt-0.5 font-medium text-red-600 dark:text-red-400">{{ $lead->lost_reason }}</dd></div>
                    @endif
                </dl>
                @if ($lead->subject || $lead->requirements)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-sm">
                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ $lead->subject }}</p>
                        <p class="mt-1 text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ $lead->requirements }}</p>
                    </div>
                @endif
            </x-card>

            {{-- Notes --}}
            <x-card>
                <x-slot:header><h3 class="font-semibold">Add Note</h3></x-slot:header>
                <form wire:submit="addNote" class="space-y-3">
                    <textarea wire:model="note" rows="2" placeholder="Log a call, meeting or update…" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    @error('note') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save Note</button>
                </form>
            </x-card>

            {{-- Timeline --}}
            <x-card>
                <x-slot:header><h3 class="font-semibold">Activity Timeline</h3></x-slot:header>
                <ul class="space-y-4">
                    @forelse ($timeline as $activity)
                        <li class="flex space-x-3" wire:key="act-{{ $activity->id }}">
                            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-800 dark:text-gray-200"><span class="font-medium">{{ $activity->user?->name ?? 'System' }}</span> {{ $activity->description }}</p>
                                @if (!empty($activity->properties['note']))
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/40 rounded-lg p-2">{{ $activity->properties['note'] }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400">No activity yet.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>

        {{-- Side column --}}
        <div class="space-y-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Assignment</h3></x-slot:header>
                <div class="space-y-3">
                    <select wire:model="assignTo" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        <option value="">— Unassigned —</option>
                        @foreach ($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                    </select>
                    <button wire:click="assign" class="w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Apply Assignment</button>
                </div>
            </x-card>

            <x-card>
                <x-slot:header><h3 class="font-semibold">Meta</h3></x-slot:header>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-400">Created</dt><dd class="text-gray-700 dark:text-gray-200">{{ $lead->created_at->format('M d, Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Created By</dt><dd class="text-gray-700 dark:text-gray-200">{{ $lead->creator?->name ?? 'System' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Last Contacted</dt><dd class="text-gray-700 dark:text-gray-200">{{ $lead->last_contacted_at?->diffForHumans() ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Closed</dt><dd class="text-gray-700 dark:text-gray-200">{{ $lead->closed_at?->format('M d, Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">AI Score</dt><dd class="text-gray-700 dark:text-gray-200">{{ $lead->score ?? '— (AI Engine pending)' }}</dd></div>
                </dl>
            </x-card>
        </div>
    </div>

    {{-- Lost modal --}}
    @if ($lostModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('lostModal', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Mark as Lost</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Optionally record why this lead was lost.</p>
                <input wire:model="lostReason" placeholder="e.g. Price too high, went with competitor…" class="mt-4 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <div class="mt-4 flex justify-end space-x-3">
                    <button wire:click="$set('lostModal', false)" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                    <button wire:click="confirmLost" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">Confirm</button>
                </div>
            </div>
        </div>
    @endif
</div>
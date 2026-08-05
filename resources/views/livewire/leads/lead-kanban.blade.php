<div x-data="{ dragId: null }">
    <div class="mb-4">
        <select wire:model.live="division" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Divisions</option>
            @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
        </select>
    </div>

    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach (\App\Enums\LeadStatus::cases() as $status)
            <div class="w-72 shrink-0 bg-gray-100 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col max-h-[68vh]"
                 @dragover.prevent
                 @drop.prevent="if (dragId) { $wire.move(dragId, '{{ $status->value }}'); dragId = null; }">

                <header class="px-4 py-3 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center">
                        <span class="w-2 h-2 rounded-full {{ $status->color() }} mr-2"></span>{{ $status->label() }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $columns[$status->value]->count() }}</span>
                </header>

                <div class="p-2 space-y-2 overflow-y-auto">
                    @foreach ($columns[$status->value] as $lead)
                        <div draggable="true"
                             @dragstart="dragId = {{ $lead->id }}"
                             @dragend="dragId = null"
                             onclick="window.location='{{ route('leads.show', $lead) }}'"
                             class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 shadow-sm hover:shadow-md transition cursor-grab">
                            <div class="flex items-start justify-between">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $lead->name }}</p>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $lead->priority()->badge() }}">{{ $lead->priority()->label() }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $lead->company_name ?? $lead->division()->label() }}</p>
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400">{{ $lead->estimated_value ? \App\Helpers\CurrencyHelper::format((float) $lead->estimated_value) : '—' }}</span>
                                <span class="text-gray-400">{{ $lead->assignee?->name ? Str::limit($lead->assignee->name, 12) : 'Unassigned' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
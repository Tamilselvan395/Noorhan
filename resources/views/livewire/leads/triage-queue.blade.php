<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Triage Queue</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Unclassified enquiries. The AI recommends routing — humans confirm.</p>
    </div>

    @forelse ($this->queue() as $lead)
        @php $ai = $this->suggestion($lead); @endphp
        <x-card wire:key="tq-{{ $lead->id }}">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('leads.show', $lead) }}" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-blue-600">{{ $lead->name }}</a>
                        <span class="text-xs text-gray-400">{{ $lead->division()->label() }} · {{ $lead->source()->label() }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $lead->subject ?? '' }} {{ \Illuminate\Support\Str::limit($lead->requirements ?? '', 140) }}</p>

                    @if ($ai->hasSuggestions())
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                            <span class="text-gray-400">AI suggests:</span>
                            @if ($ai->vehicle_brand_category) <span class="px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400">Brand: {{ ucfirst($ai->vehicle_brand_category) }}</span> @endif
                            @if ($ai->division) <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">Division: {{ \App\Enums\Division::from($ai->division)->label() }}</span> @endif
                            @if ($ai->customer_type) <span class="px-2 py-0.5 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400">Type: {{ \App\Enums\CustomerType::from($ai->customer_type)->label() }}</span> @endif
                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ round($ai->confidence * 100) }}% confidence</span>
                        </div>
                    @else
                        <p class="mt-2 text-xs text-gray-400">No AI signal — manual classification required.</p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if ($ai->hasSuggestions())
                        <button wire:click="applyAndRoute({{ $lead->id }})" class="px-3 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold">Apply AI + Route</button>
                    @endif
                    <button wire:click="routeOnly({{ $lead->id }})" class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold">Route Rules Only</button>
                    <select wire:model="manual.{{ $lead->id }}" class="px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                        <option value="">Assign to…</option>
                        @foreach ($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                    </select>
                    <button wire:click="assignManual({{ $lead->id }})" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Assign</button>
                </div>
            </div>
        </x-card>
    @empty
        <x-card>
            <div class="p-6 text-center text-sm text-gray-400">Triage queue is empty — every enquiry is classified and owned. 🎉</div>
        </x-card>
    @endforelse
</div>
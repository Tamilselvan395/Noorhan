<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Wiperex Intelligence</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Fitment mix, replenishment prospects & seasonal planning.</p>
    </div>

    {{-- Seasonal suggestion --}}
    <div class="bg-gradient-to-r from-cyan-600 to-blue-700 rounded-xl p-5 text-white shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-cyan-100">{{ $season['season'] }} · Focus: {{ $season['focus'] }}</p>
                <p class="mt-1 text-sm text-cyan-50">{{ $season['message'] }}</p>
            </div>
            <button wire:click="createDraftCampaign" class="shrink-0 px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur text-sm font-semibold border border-white/20">
                Create Draft Campaign
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Blade size mix --}}
        <x-card>
            <x-slot:header><h3 class="font-semibold">Blade Size Mix (Revenue)</h3></x-slot:header>
            @if ($sizeMix)
                <ul class="p-4 space-y-3">
                    @php $max = max($sizeMix); @endphp
                    @foreach ($sizeMix as $size => $revenue)
                        <li wire:key="sz-{{ $size }}">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $size }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ number_format($revenue, 2) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-2 rounded-full bg-cyan-500" style="width: {{ ($revenue / $max) * 100 }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-6 text-center text-sm text-gray-400">No size-attributed blade sales yet.</div>
            @endif
        </x-card>

        {{-- Replenishment candidates --}}
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold">🔁 Replenishment Prospects</h3>
                    <span class="text-xs text-gray-400">repeat consumable buyers</span>
                </div>
            </x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($candidates as $candidate)
                    <li class="p-3 flex items-center justify-between text-sm" wire:key="rc-{{ $candidate['customer_id'] }}">
                        <div>
                            <a href="{{ route('customers.show', $candidate['customer_id']) }}" class="font-medium text-gray-800 dark:text-gray-200 hover:text-cyan-600">{{ $candidate['name'] }}</a>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $candidate['orders'] }} consumable orders · last {{ $candidate['last_order'] }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400">Offer standing order</span>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-gray-400">No repeat consumable buyers detected yet.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</div>
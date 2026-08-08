<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('marketing.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $campaign->name }}</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $campaign->status()->badge() }}">{{ $campaign->status()->label() }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $campaign->channel()->label() }} · {{ $campaign->division()->label() }} · utm_campaign=<code class="text-xs bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ $campaign->utm_campaign }}</code>
            </p>
        </div>
    </div>

    {{-- Funnel KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-4">
        <x-dashboard.stat-card label="Leads" :value="number_format($perf['leads'])" icon="users" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Converted" :value="number_format($perf['converted'])" icon="shield" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Won" :value="number_format($perf['won'])" icon="bolt" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Conv. Rate" :value="$perf['conversion_rate'].'%'" icon="chart" accent="bg-cyan-500/10 text-cyan-600 dark:text-cyan-400" />
        <x-dashboard.stat-card label="Pipeline" :value="\App\Helpers\CurrencyHelper::format($perf['pipeline_value'])" icon="chart" accent="bg-amber-500/10 text-amber-600 dark:text-amber-400" />
        <x-dashboard.stat-card label="Spent" :value="\App\Helpers\CurrencyHelper::format($perf['spent'])" icon="shield" accent="bg-red-500/10 text-red-600 dark:text-red-400" />
        <x-dashboard.stat-card label="Cost / Lead" :value="\App\Helpers\CurrencyHelper::format($perf['cost_per_lead'])" icon="chart" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="WA Delivered" :value="number_format($perf['wa_sent'])" hint="{$perf['wa_failed']} failed" icon="bolt" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Attributed leads --}}
        <x-card>
            <x-slot:header><h3 class="font-semibold">Attributed Leads (UTM)</h3></x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($leads as $lead)
                    <li class="flex items-center justify-between p-4" wire:key="al-{{ $lead->id }}">
                        <div>
                            <a href="{{ route('leads.show', $lead) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600">{{ $lead->name }}</a>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $lead->source()->label() }} · {{ $lead->created_at->format('M d, Y') }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $lead->status()->badge() }}">{{ $lead->status()->label() }}</span>
                    </li>
                @empty
                    <li class="p-8 text-center text-sm text-gray-400">No leads attributed yet. Tag your ads & links with <code>utm_campaign={{ $campaign->utm_campaign }}</code>.</li>
                @endforelse
            </ul>
        </x-card>

        {{-- Linked WhatsApp broadcasts --}}
        <x-card>
            <x-slot:header><h3 class="font-semibold">Linked WhatsApp Broadcasts</h3></x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($whatsapp as $wa)
                    <li class="flex items-center justify-between p-4" wire:key="wa-{{ $wa->id }}">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $wa->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $wa->created_at->format('M d, Y') }} · {{ ucfirst($wa->message_type) }}</p>
                        </div>
                        <span class="text-sm text-green-600 dark:text-green-400 font-medium">{{ $wa->sent_count }} sent</span>
                    </li>
                @empty
                    <li class="p-8 text-center text-sm text-gray-400">No broadcasts linked. Set marketing_campaign_id when launching WhatsApp campaigns.</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    @if ($campaign->goals)
        <x-card>
            <x-slot:header><h3 class="font-semibold">Goals</h3></x-slot:header>
            <p class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line">{{ $campaign->goals }}</p>
        </x-card>
    @endif
</div>
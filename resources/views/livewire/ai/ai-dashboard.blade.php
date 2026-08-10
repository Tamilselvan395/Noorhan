<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Engine</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scores, predictions, recommendations & natural-language search.</p>
    </div>

    {{-- Natural language search --}}
    <x-card>
        <x-slot:header><h3 class="font-semibold">Ask the CRM</h3></x-slot:header>
        <form wire:submit="search" class="flex space-x-2 p-4">
            <input wire:model="query" placeholder='e.g. "overdue invoices", "garage customers", "leads from facebook above 5000"'
                   class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-violet-500 outline-none">
            <button class="px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold">Search</button>
        </form>
        @if ($searchResults)
            <div class="px-4 pb-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                @foreach (['leads' => 'Leads', 'customers' => 'Customers', 'invoices' => 'Invoices'] as $key => $label)
                    @if (! empty($searchResults[$key]))
                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400 mb-1">{{ $label }}</p>
                            <ul class="space-y-1">
                                @foreach ($searchResults[$key] as $row)
                                    <li class="text-gray-700 dark:text-gray-200">{{ implode(' — ', array_map(fn($v) => (string) $v, $row)) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </x-card>

    {{-- Briefing --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-card>
            <x-slot:header><h3 class="font-semibold"> Daily Briefing</h3></x-slot:header>
            <dl class="p-4 text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-gray-400">Follow-ups due</dt><dd class="font-bold">{{ $brief['follow_ups_due'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Overdue invoices</dt><dd class="font-bold text-red-500">{{ $brief['overdue_invoices']['count'] }} ({{ number_format($brief['overdue_invoices']['value'], 2) }})</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Dormant customers</dt><dd class="font-bold text-amber-500">{{ $brief['dormant_customers'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Next-month forecast</dt><dd class="font-bold text-green-600">{{ number_format($brief['sales_forecast']['forecast'], 2) }} ({{ $brief['sales_forecast']['trend'] }})</dd></div>
            </dl>
        </x-card>

        <x-card>
            <x-slot:header><h3 class="font-semibold">🎯 Hottest Open Leads</h3></x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($brief['top_leads'] as $lead)
                    <li class="p-3 flex justify-between text-sm">
                        <a href="{{ route('leads.show', $lead['id']) }}" class="text-gray-800 dark:text-gray-200 hover:text-blue-600">{{ $lead['name'] }}</a>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $lead['score'] >= 70 ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : ($lead['score'] >= 40 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300') }}">{{ $lead['score'] }}</span>
                    </li>
                @endforeach
            </ul>
        </x-card>

        <x-card>
            <x-slot:header><h3 class="font-semibold">⚠️ Churn Risk</h3></x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($brief['at_risk_customers'] as $c)
                    <li class="p-3 text-sm">
                        <div class="flex justify-between">
                            <button wire:click="selectCustomer({{ $c['id'] }})" class="text-gray-800 dark:text-gray-200 hover:text-violet-600 font-medium">{{ $c['name'] }}</button>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $c['level'] === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' }}">{{ $c['level'] }} {{ $c['score'] }}</span>
                        </div>
                        @if ($c['reasons']) <p class="text-xs text-gray-400 mt-1">{{ implode(' · ', $c['reasons']) }}</p> @endif
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-gray-400">No elevated churn risk.</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    {{-- Customer intelligence --}}
    @if ($customer)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">🧠 {{ $customer->name }} — Summary</h3></x-slot:header>
                <div class="p-4 text-sm space-y-2">
                    <p class="text-gray-600 dark:text-gray-300">{{ $custSummary['total'] }} communications · last {{ $custSummary['last_contact'] ?? 'never' }} · tone: {{ $custSummary['tone'] }}</p>
                    @if ($custSummary['topics']) <p class="text-gray-400">Topics: {{ implode(', ', $custSummary['topics']) }}</p> @endif
                    <p class="text-xs text-gray-400 italic">{{ $custSummary['llm'] }}</p>
                </div>
            </x-card>
            <x-card>
                <x-slot:header><h3 class="font-semibold">🛒 Recommended For {{ $customer->name }}</h3></x-slot:header>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($recs as $rec)
                        <li class="p-3 text-sm">
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ $rec['product']->name }}</p>
                            <p class="text-xs text-gray-400">{{ $rec['reason'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        </div>
    @else
        <x-card><div class="p-6 text-center text-sm text-gray-400">Select a customer above (or from Churn Risk) for summaries & cross-sell recommendations.</div></x-card>
    @endif
</div>
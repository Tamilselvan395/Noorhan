<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('products.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $product->sku }} · {{ $product->division()->label() }} · {{ $product->category?->name ?? 'Uncategorised' }}</p>
        </div>
        <button wire:click="$dispatch('open-product-form', { productId: {{ $product->id }} })" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Edit</button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-card>
            <x-slot:header><h3 class="font-semibold">Pricing</h3></x-slot:header>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-gray-400">Cost</dt><dd class="text-gray-800 dark:text-gray-200">{{ \App\Helpers\CurrencyHelper::format((float) $product->cost_price) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Sale</dt><dd class="font-semibold text-gray-900 dark:text-white">{{ \App\Helpers\CurrencyHelper::format((float) $product->sale_price) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Incl. Tax</dt><dd class="text-gray-800 dark:text-gray-200">{{ \App\Helpers\CurrencyHelper::format($product->priceWithTax()) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Margin</dt><dd><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $product->marginBadge() }}">{{ $product->margin() !== null ? $product->margin().'%' : '—' }}</span></dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Tax Rate</dt><dd class="text-gray-800 dark:text-gray-200">{{ $product->tax_rate }}%</dd></div>
            </dl>
        </x-card>

        <x-card>
            <x-slot:header><h3 class="font-semibold">Attributes</h3></x-slot:header>
            @if ($product->attributes && count($product->attributes))
                <dl class="text-sm space-y-2">
                    @foreach ($product->attributes as $key => $value)
                        <div class="flex justify-between"><dt class="text-gray-400">{{ ucfirst($key) }}</dt><dd class="text-gray-800 dark:text-gray-200">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            @else
                <p class="text-sm text-gray-400">No attributes defined.</p>
            @endif
        </x-card>

        <x-card>
            <x-slot:header><h3 class="font-semibold">Details</h3></x-slot:header>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-gray-400">Brand</dt><dd class="text-gray-800 dark:text-gray-200">{{ $product->brand ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Unit</dt><dd class="text-gray-800 dark:text-gray-200">{{ $product->unit()->label() }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Created</dt><dd class="text-gray-800 dark:text-gray-200">{{ $product->created_at->format('M d, Y') }}</dd></div>
            </dl>
            @if ($product->description) <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ $product->description }}</p> @endif
        </x-card>
    </div>

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
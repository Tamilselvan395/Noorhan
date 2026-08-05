<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Products</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Catalog across Automotive, Swiftec, Wiperex & Otozaar.</p>
        </div>
        <button wire:click="$dispatch('open-product-form')" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">+ New Product</button>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <x-dashboard.stat-card label="Total SKUs" :value="number_format($stats['total'])" icon="chart" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Active" :value="number_format($stats['active'])" icon="shield" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Categories" :value="number_format($stats['categories'])" icon="users" accent="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
        <x-dashboard.stat-card label="Low Margin (<10%)" :value="number_format($stats['low_margin'])" icon="bolt" accent="bg-red-500/10 text-red-600 dark:text-red-400" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search name, SKU, brand…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="division" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Divisions</option>
            @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
        </select>
        <select wire:model.live="categoryId" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Categories</option>
            @foreach ($categories as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
        </select>
        <select wire:model.live="active" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All States</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Cost</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sale</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Margin</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($products as $product)
                <tr wire:key="{{ $product->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer" onclick="window.location='{{ route('products.show', $product) }}'">
                    <td class="px-6 py-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $product->sku }} · {{ $product->division()->label() }}</p>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $product->category?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ \App\Helpers\CurrencyHelper::format((float) $product->cost_price) }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ \App\Helpers\CurrencyHelper::format((float) $product->sale_price) }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $product->marginBadge() }}">
                            {{ $product->margin() !== null ? $product->margin().'%' : '—' }}
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No products found. Run the seeders or create one.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>
    <div class="mt-4">{{ $products->links() }}</div>
</div>
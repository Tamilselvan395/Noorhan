<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Supplier Comparison</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pick a product to rank suppliers by price, lead time and rating.</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <select wire:model.live="productId" class="w-full max-w-md px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="">Select a product…</option>
            @foreach ($products as $p) <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option> @endforeach
        </select>
    </div>

    @if ($productId)
        @php $results = $this->results(); @endphp
        @if ($results->isEmpty())
            <x-card><div class="p-8 text-center text-sm text-gray-400">No supplier has a current price for this product yet.</div></x-card>
        @else
            <x-table>
                <x-slot:head>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">MOQ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Lead Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Rating</th>
                </x-slot:head>
                <x-slot:body>
                    @foreach ($results as $i => $row)
                        <tr wire:key="cmp-{{ $row['supplier']->id }}" class="{{ $i === 0 ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                            <td class="px-6 py-3 text-sm font-bold {{ $i === 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">{{ $i + 1 }}</td>
                            <td class="px-6 py-3">
                                <a href="{{ route('suppliers.show', $row['supplier']) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600">
                                    {{ $row['supplier']->name }}
                                    @if ($i === 0) <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">BEST PRICE</span> @endif
                                </a>
                            </td>
                            <td class="px-6 py-3 text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $row['currency'] }} {{ number_format($row['price'], 2) }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $row['min_qty'] }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $row['lead_time'] !== null ? $row['lead_time'].' days' : '—' }}</td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $row['rating'] !== null ? '★ '.$row['rating'] : '—' }}</td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
        @endif
    @endif
</div>
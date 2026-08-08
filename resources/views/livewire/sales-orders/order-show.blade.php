<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('sales-orders.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $order->reference }}</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status()->badge() }}">{{ $order->status()->label() }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $order->customer?->displayName() ?? '—' }}
                @if ($order->quotation) · From: <a href="{{ route('quotations.show', $order->quotation) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $order->quotation->reference }}</a> @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @foreach ($this->nextStatuses() as $next)
                <button wire:click="advance('{{ $next->value }}')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold {{ $next->value === 'cancelled' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white' }}">
                    {{ $next->value === 'cancelled' ? 'Cancel Order' : 'Mark '.$next->label() }}
                </button>
            @endforeach

            @if (in_array($order->status->value, ['confirmed', 'processing', 'delivered']) && ! $order->invoice_id)
                <button
                    wire:click="generateInvoice"
                    class="px-3 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold">
                    Generate Invoice
                </button>
            @elseif ($order->invoice_id)
                <a
                    href="{{ route('invoices.show', $order->invoice_id) }}"
                    class="px-3 py-2 rounded-lg text-xs font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400">
                    View Invoice
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"><p class="text-gray-400 text-xs">Expected Delivery</p><p class="font-medium mt-1 text-gray-900 dark:text-white">{{ $order->expected_delivery_date?->format('M d, Y') ?? '—' }}</p></div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"><p class="text-gray-400 text-xs">Delivered At</p><p class="font-medium mt-1 text-gray-900 dark:text-white">{{ $order->delivered_at?->format('M d, Y h:i A') ?? '—' }}</p></div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"><p class="text-gray-400 text-xs">Order Value</p><p class="font-medium mt-1 text-blue-600 dark:text-blue-400">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</p></div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"><p class="text-gray-400 text-xs">Margin</p><p class="font-medium mt-1 text-green-600 dark:text-green-400">{{ $order->margin_percent }}%</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Items</h3></x-slot:header>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead><tr>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Item</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Qty</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Price</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($items as $item)
                            <tr wire:key="i-{{ $item->id }}">
                                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $item->description }}</td>
                                <td class="px-4 py-2 text-sm text-right text-gray-600 dark:text-gray-300">{{ $item->quantity }}</td>
                                <td class="px-4 py-2 text-sm text-right text-gray-600 dark:text-gray-300">{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="px-4 py-2 text-sm text-right font-medium text-gray-800 dark:text-gray-200">{{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4 flex justify-end text-sm font-bold">
                    Total: <span class="ml-2 text-blue-600 dark:text-blue-400">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</span>
                </div>
            </x-card>

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

        <div class="space-y-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Delivery</h3></x-slot:header>
                <dl class="text-sm space-y-2">
                    <div><dt class="text-gray-400">Address</dt><dd class="mt-0.5 text-gray-800 dark:text-gray-200">{{ $order->delivery_address ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Notes</dt><dd class="mt-0.5 text-gray-800 dark:text-gray-200">{{ $order->delivery_notes ?? $order->notes ?? '—' }}</dd></div>
                </dl>
                <p class="mt-3 text-[11px] text-gray-400">→ Invoicing & payments arrive with Modules 14–15; Zoho sync with the Accounting module.</p>
            </x-card>
        </div>
    </div>
</div>
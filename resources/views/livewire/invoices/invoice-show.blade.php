<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('invoices.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $invoice->reference }}</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $invoice->status()->badge() }}">{{ $invoice->status()->label() }}</span>
                @if ($invoice->isOverdue()) <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">OVERDUE</span> @endif
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $invoice->customer?->displayName() ?? '—' }}
                @if ($invoice->salesOrder) · Order: <a href="{{ route('sales-orders.show', $invoice->salesOrder) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $invoice->salesOrder->reference }}</a> @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if ($invoice->status()->value === 'draft')
                <select wire:model="sendVia" class="px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                    <option value="email">Email</option>
                    <option value="whatsapp">WhatsApp</option>
                </select>
                <button wire:click="send" class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold">Send Invoice</button>
            @endif
            <a href="{{ route('invoices.public', $invoice) . '?signature=preview' }}" target="_blank" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Print / PDF</a>
        </div>
    </div>

    @if ($publicUrl)
        <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-400 break-all">
            Customer link: <a href="{{ $publicUrl }}" target="_blank" class="underline">{{ $publicUrl }}</a>
        </div>
    @endif

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
                <div class="p-4 flex justify-end">
                    <dl class="w-56 text-sm space-y-1">
                        <div class="flex justify-between"><dt class="text-gray-400">Subtotal</dt><dd>{{ number_format((float) $invoice->subtotal, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Tax</dt><dd>{{ number_format((float) $invoice->tax_amount, 2) }}</dd></div>
                        <div class="flex justify-between font-bold text-base"><dt>Total</dt><dd class="text-blue-600 dark:text-blue-400">{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</dd></div>
                        <div class="flex justify-between text-amber-600 dark:text-amber-400 font-bold"><dt>Balance Due</dt><dd>{{ $invoice->currency }} {{ number_format((float) $invoice->balance_due, 2) }}</dd></div>
                    </dl>
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
                <x-slot:header><h3 class="font-semibold">Billing Details</h3></x-slot:header>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-400">Issue Date</dt><dd class="text-gray-800 dark:text-gray-200">{{ $invoice->issue_date?->format('M d, Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Due Date</dt><dd class="{{ $invoice->isOverdue() ? 'text-red-500 font-semibold' : 'text-gray-800 dark:text-gray-200' }}">{{ $invoice->due_date?->format('M d, Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Paid</dt><dd class="text-green-600 dark:text-green-400">{{ $invoice->currency }} {{ number_format((float) $invoice->paid_amount, 2) }}</dd></div>
                </dl>
            </x-card>

            {{-- Replace the "Record Payment" card with this: --}}
            @if ((float) $invoice->balance_due > 0 && ! in_array($invoice->status()->value, ['draft', 'cancelled']))
                <x-card>
                    <x-slot:header><h3 class="font-semibold">Collect Payment</h3></x-slot:header>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Balance due: <strong class="text-amber-600 dark:text-amber-400">{{ $invoice->currency }} {{ number_format((float) $invoice->balance_due, 2) }}</strong></p>
                    <button wire:click="$dispatch('open-payment-form', { customerId: {{ $invoice->customer_id }}, invoiceId: {{ $invoice->id } })" class="w-full py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">Record Payment</button>
                </x-card>
            @endif
        </div>
    </div>
</div>
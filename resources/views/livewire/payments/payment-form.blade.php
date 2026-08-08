<div x-data="{ open: @entangle('open') }">
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50" @click="open = false"></div>

        <div x-show="open" x-transition class="relative mx-auto my-8 w-full max-w-4xl bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Record Payment</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
            </div>

            <form wire:submit="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer *</label>
                        <select wire:model.live="customer_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">— Select —</option>
                            @foreach ($customers as $c) <option value="{{ $c->id }}">{{ $c->displayName() }}</option> @endforeach
                        </select>
                        @error('customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Amount Received *</label>
                        <input wire:model.live="amount" type="number" step="0.01" min="0" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date</label>
                        <input wire:model="payment_date" type="date" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Method</label>
                        <select wire:model="method" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            @foreach (\App\Enums\PaymentMethod::cases() as $m) <option value="{{ $m->value }}">{{ $m->label() }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference / Cheque #</label>
                        <input wire:model="reference_number" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                    </div>
                </div>

                @if ($customer_id && $outstandingInvoices->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Allocate to Outstanding Invoices</h4>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Invoice</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Balance Due</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Allocate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($outstandingInvoices as $inv)
                                        <tr wire:key="inv-{{ $inv->id }}">
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $inv->reference }}</td>
                                            <td class="px-4 py-2 text-sm text-right text-gray-500">{{ number_format((float) $inv->total, 2) }}</td>
                                            <td class="px-4 py-2 text-sm text-right font-semibold text-amber-600 dark:text-amber-400">{{ number_format((float) $inv->balance_due, 2) }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <input wire:model.live="allocations.{{ $inv->id }}" type="number" step="0.01" min="0" max="{{ $inv->balance_due }}" class="w-28 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-right focus:ring-2 focus:ring-green-500 outline-none">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @php 
                            $totalAllocated = collect($allocations)->sum(fn($v) => (float) $v);
                            $unallocated = (float) $amount - $totalAllocated;
                        @endphp
                        <div class="mt-2 flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Total Allocated: <strong class="text-gray-800 dark:text-gray-200">{{ number_format($totalAllocated, 2) }}</strong></span>
                            <span class="{{ $unallocated > 0 ? 'text-violet-600 dark:text-violet-400' : 'text-gray-500' }}">
                                Unallocated (Customer Credit): <strong>{{ number_format(max($unallocated, 0), 2) }}</strong>
                            </span>
                        </div>
                    </div>
                @elseif ($customer_id)
                    <p class="text-sm text-gray-400 italic">This customer has no outstanding invoices. The full amount will be recorded as customer credit.</p>
                @endif

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
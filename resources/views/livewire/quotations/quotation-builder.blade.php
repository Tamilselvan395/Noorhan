<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $quotationId ? 'Edit Quotation' : 'Quotation Builder' }}</h1>
        <a href="{{ route('quotations.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Back</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: details + items --}}
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Quotation Details</h3></x-slot:header>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                        <select wire:model.live="customer_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">—</option>
                            @foreach ($customers as $c) <option value="{{ $c->id }}">{{ $c->displayName() }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Or Lead</label>
                        <select wire:model.live="lead_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">—</option>
                            @foreach ($leads as $l) <option value="{{ $l->id }}">#{{ $l->id }} {{ $l->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division</label>
                        <select wire:model="division" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valid Until</label>
                        <input wire:model="valid_until" type="date" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold">Line Items</h3>
                        <button wire:click="addItemRow" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">+ Add Item</button>
                    </div>
                </x-slot:header>
                <div class="space-y-3">
                    @foreach ($items as $index => $item)
                        <div class="grid grid-cols-12 gap-2 items-start" wire:key="qi-{{ $index }}">
                            <select wire:model.live="items.{{ $index }}.product_id" class="col-span-3 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                                <option value="">Custom…</option>
                                @foreach ($products as $p) <option value="{{ $p->id }}">{{ $p->sku }}</option> @endforeach
                            </select>
                            <input wire:model="items.{{ $index }}.description" placeholder="Description *" class="col-span-3 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                            <input wire:model.live="items.{{ $index }}.quantity" type="number" min="1" class="col-span-1 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs" title="Qty">
                            <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" placeholder="Price" class="col-span-2 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                            <input wire:model.live="items.{{ $index }}.cost_price" type="number" step="0.01" min="0" placeholder="Cost" class="col-span-1 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                            <input wire:model.live="items.{{ $index }}.discount_percent" type="number" step="0.01" min="0" max="100" placeholder="Disc %" class="col-span-1 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                            <button wire:click="removeItemRow({{ $index }})" class="col-span-1 text-red-500 hover:text-red-700">&times;</button>
                            @error("items.{$index}.description") <p class="col-span-12 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card>
                <x-slot:header><h3 class="font-semibold">Notes & Terms</h3></x-slot:header>
                <div class="space-y-3">
                    <textarea wire:model="notes" rows="2" placeholder="Notes to customer…" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    <textarea wire:model="terms" rows="2" placeholder="Terms & conditions…" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>
            </x-card>
        </div>

        {{-- Right: commercial summary --}}
        <div class="space-y-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Commercials</h3></x-slot:header>
                <div class="space-y-3 text-sm">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tax Rate %</label>
                            <input wire:model.live="tax_rate" type="number" step="0.01" min="0" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Discount</label>
                            <div class="mt-1 flex space-x-2">
                                <select wire:model.live="discount_type" class="w-1/3 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                    <option value="percent">%</option>
                                    <option value="fixed">Fix</option>
                                </select>
                                <input wire:model.live="discount_value" type="number" step="0.01" min="0" class="w-full px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            </div>
                        </div>
                    </div>

                    <dl class="pt-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
                        <div class="flex justify-between"><dt class="text-gray-400">Subtotal</dt><dd class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($totals['subtotal'], 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Discount</dt><dd class="text-red-500">-{{ number_format($totals['discount'], 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Tax</dt><dd class="text-gray-800 dark:text-gray-200">{{ number_format($totals['tax'], 2) }}</dd></div>
                        <div class="flex justify-between text-base font-bold"><dt class="text-gray-900 dark:text-white">Total</dt><dd class="text-blue-600 dark:text-blue-400">{{ number_format($totals['total'], 2) }}</dd></div>
                    </dl>

                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Margin</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $totals['margin'] < (float) config('noorhan.quotation.min_margin') ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' }}">{{ $totals['margin'] }}%</span>
                        </div>
                        @if ($totals['margin'] < (float) config('noorhan.quotation.min_margin'))
                            <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">⚠ Below minimum margin — management approval will be required.</p>
                        @endif
                    </div>
                </div>
            </x-card>

            <button wire:click="save" class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 active:scale-[.99] text-white text-sm font-semibold shadow-sm transition">
                {{ $quotationId ? 'Update Quotation' : 'Save Quotation' }}
            </button>
        </div>
    </div>
</div>
<div x-data="{ open: @entangle('open') }">
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50" @click="open = false"></div>

        <div x-show="open" x-transition class="relative mx-auto my-8 w-full max-w-3xl bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">New Sales Order</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer *</label>
                        <select wire:model="customer_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">— Select —</option>
                            @foreach ($customers as $c) <option value="{{ $c->id }}">{{ $c->displayName() }}</option> @endforeach
                        </select>
                        @error('customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division</label>
                        <select wire:model="division" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expected Delivery</label>
                        <input wire:model="expected_delivery_date" type="date" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Address</label>
                        <input wire:model="delivery_address" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Items *</label>
                        <button type="button" wire:click="addItemRow" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">+ Add</button>
                    </div>
                    <div class="mt-2 space-y-2">
                        @foreach ($items as $index => $item)
                            <div class="grid grid-cols-12 gap-2" wire:key="oi-{{ $index }}">
                                <select wire:model.live="items.{{ $index }}.product_id" class="col-span-3 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                                    <option value="">Custom…</option>
                                    @foreach ($products as $p) <option value="{{ $p->id }}">{{ $p->sku }}</option> @endforeach
                                </select>
                                <input wire:model="items.{{ $index }}.description" placeholder="Description *" class="col-span-3 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                                <input wire:model="items.{{ $index }}.quantity" type="number" min="1" class="col-span-1 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                                <input wire:model="items.{{ $index }}.unit_price" type="number" step="0.01" placeholder="Price" class="col-span-2 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                                <input wire:model="items.{{ $index }}.cost_price" type="number" step="0.01" placeholder="Cost" class="col-span-2 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                                <button type="button" wire:click="removeItemRow({{ $index }})" class="col-span-1 text-red-500">&times;</button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Create Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
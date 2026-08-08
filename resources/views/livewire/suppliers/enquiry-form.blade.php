<div x-data="{ open: @entangle('open') }">
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50" @click="open = false"></div>

        <div x-show="open" x-transition class="relative mx-auto my-8 w-full max-w-3xl bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">New Supplier Enquiry</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier *</label>
                        <select wire:model="supplier_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">— Select —</option>
                            @foreach ($suppliers as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Related Lead</label>
                        <select wire:model="lead_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">— None —</option>
                            @foreach ($leads as $l) <option value="{{ $l->id }}">#{{ $l->id }} {{ $l->name }}</option> @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Items *</label>
                        <button type="button" wire:click="addItemRow" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">+ Add Item</button>
                    </div>
                    <div class="mt-2 space-y-2">
                        @foreach ($items as $index => $item)
                            <div class="grid grid-cols-12 gap-2" wire:key="item-{{ $index }}">
                                <select wire:model="items.{{ $index }}.product_id" class="col-span-4 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                    <option value="">Custom item…</option>
                                    @foreach ($products as $p) <option value="{{ $p->id }}">{{ $p->sku }}</option> @endforeach
                                </select>
                                <input wire:model="items.{{ $index }}.description" placeholder="Description *" class="col-span-5 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <input wire:model="items.{{ $index }}.quantity" type="number" min="1" class="col-span-2 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <button type="button" wire:click="removeItemRow({{ $index }})" class="col-span-1 text-red-500 hover:text-red-700">&times;</button>
                                @error("items.{$index}.description") <p class="col-span-12 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes to supplier</label>
                    <textarea wire:model="notes" rows="2" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Create Enquiry</button>
                </div>
            </form>
        </div>
    </div>
</div>
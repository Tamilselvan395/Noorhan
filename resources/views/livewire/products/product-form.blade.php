<div x-data="{ open: @entangle('open') }">
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50" @click="open = false"></div>

        <div x-show="open" x-transition class="relative mx-auto my-8 w-full max-w-3xl bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $productId ? 'Edit Product' : 'New Product' }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
            </div>

            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU *</label>
                    <div class="mt-1 flex space-x-2">
                        <input wire:model="sku" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="button" wire:click="generateSku" class="shrink-0 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Auto</button>
                    </div>
                    @error('sku') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                    <input wire:model="name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division *</label>
                    <select wire:model.live="division" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <select wire:model="category_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        <option value="">—</option>
                        @foreach ($categories as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
                    <input wire:model="brand" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit *</label>
                    <select wire:model="unit" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        @foreach (\App\Enums\ProductUnit::cases() as $u) <option value="{{ $u->value }}">{{ $u->label() }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost Price *</label>
                    <input wire:model="cost_price" type="number" step="0.01" min="0" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('cost_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sale Price *</label>
                    <input wire:model="sale_price" type="number" step="0.01" min="0" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('sale_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tax Rate % *</label>
                    <input wire:model="tax_rate" type="number" step="0.01" min="0" max="100" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2">Active</span>
                    </label>
                </div>

                {{-- Extensible attributes --}}
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Attributes (viscosity, size, fitment…)</label>
                        <button type="button" wire:click="addAttributeRow" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">+ Add</button>
                    </div>
                    <div class="mt-2 space-y-2">
                        @foreach ($attributes as $index => $row)
                            <div class="flex space-x-2" wire:key="attr-{{ $index }}">
                                <input wire:model="attributes.{{ $index }}.key" placeholder="Key" class="w-1/3 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <input wire:model="attributes.{{ $index }}.value" placeholder="Value" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <button type="button" wire:click="removeAttributeRow({{ $index }})" class="shrink-0 text-red-500 hover:text-red-700">&times;</button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea wire:model="description" rows="2" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>

                <div class="md:col-span-2 flex justify-end space-x-3 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">{{ $productId ? 'Update' : 'Create' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
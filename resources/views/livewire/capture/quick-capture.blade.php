<div x-data="{ open: @entangle('open') }" @open-quick-capture.window="open = true">
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div x-show="open" x-transition class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Capture</h3>
            <p class="text-xs text-gray-400 mt-0.5">Walk-ins, exhibitions, networking & travel enquiries.</p>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                    <input wire:model="name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                    <input wire:model="phone" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Source</label>
                        <select wire:model="source" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            @foreach (['walk_in','exhibition','networking','international_travel'] as $s)
                                <option value="{{ $s }}">{{ \App\Enums\LeadSource::from($s)->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division</label>
                        <select wire:model="division" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle Brand</label>
                    <select wire:model="vehicle_brand_category" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        <option value="">— Unknown (Triage) —</option>
                        @foreach (\App\Enums\VehicleBrandCategory::cases() as $v) <option value="{{ $v->value }}">{{ $v->label() }}</option> @endforeach
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Capture</button>
                </div>
            </form>
        </div>
    </div>
</div>
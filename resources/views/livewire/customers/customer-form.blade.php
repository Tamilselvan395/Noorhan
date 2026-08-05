<div x-data="{ open: @entangle('open') }">
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50" @click="open = false"></div>

        <div x-show="open" x-transition class="relative mx-auto my-8 w-full max-w-3xl bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $customerId ? 'Edit Customer' : 'New Customer' }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
            </div>

            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                    <input wire:model="name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                    <select wire:model="company_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        <option value="">— Independent / none —</option>
                        @foreach ($companies as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input wire:model="email" type="email" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                    <input wire:model="phone" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp</label>
                    <input wire:model="whatsapp" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Type *</label>
                    <select wire:model="type" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        @foreach (\App\Enums\CustomerType::cases() as $t) <option value="{{ $t->value }}">{{ $t->label() }}</option> @endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                    <select wire:model="status" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        @foreach (\App\Enums\CustomerStatus::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division *</label>
                    <select wire:model="division" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle Brand</label>
                    <select wire:model="vehicle_brand_category" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        <option value="">—</option>
                        @foreach (\App\Enums\VehicleBrandCategory::cases() as $v) <option value="{{ $v->value }}">{{ $v->label() }}</option> @endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Owner</label>
                    <select wire:model="owner_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        <option value="">—</option>
                        @foreach ($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Credit Limit</label>
                    <input wire:model="credit_limit" type="number" step="0.01" min="0" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                    <input wire:model="address" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                    <input wire:model="city" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                    <input wire:model="country" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea wire:model="notes" rows="2" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea></div>

                <div class="md:col-span-2 flex justify-end space-x-3 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">{{ $customerId ? 'Update' : 'Create' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
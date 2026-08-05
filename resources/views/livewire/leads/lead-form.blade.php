<div x-data="{ open: @entangle('open') }">
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50" @click="open = false"></div>

        <div x-show="open" x-transition class="relative mx-auto my-8 w-full max-w-3xl bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $leadId ? 'Edit Lead' : 'New Lead' }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
            </div>

            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Name *</label>
                    <input wire:model="name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                    <input wire:model="company_name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input wire:model="email" type="email" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone / WhatsApp</label>
                    <input wire:model="phone" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division *</label>
                    <select wire:model.live="division" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Source *</label>
                    <select wire:model="source" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        @foreach (\App\Enums\LeadSource::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
                    </select>
                </div>
                @if ($division === \App\Enums\Division::Automotive->value)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle Brand Category</label>
                        <select wire:model="vehicle_brand_category" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">— Select —</option>
                            @foreach (\App\Enums\VehicleBrandCategory::cases() as $v) <option value="{{ $v->value }}">{{ $v->label() }}</option> @endforeach
                        </select>
                        <p class="mt-1 text-[11px] text-gray-400">Unknown/empty routes to Triage Queue.</p>
                    </select>
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Type</label>
                        <select wire:model="customer_type" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">— Select —</option>
                            @foreach (\App\Enums\CustomerType::cases() as $c) <option value="{{ $c->value }}">{{ $c->label() }}</option> @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority *</label>
                    <select wire:model="priority" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        @foreach (\App\Enums\LeadPriority::cases() as $p) <option value="{{ $p->value }}">{{ $p->label() }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Value</label>
                    <input wire:model="estimated_value" type="number" step="0.01" min="0" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Next Follow-up</label>
                    <input wire:model="next_follow_up_at" type="datetime-local" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assign To</label>
                    <select wire:model="assigned_to" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        <option value="">— Unassigned —</option>
                        @foreach ($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
                    <input wire:model="subject" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. Enquiry for Japanese brake pads">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Requirements / Notes</label>
                    <textarea wire:model="requirements" rows="3" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>

                <div class="md:col-span-2 flex justify-end space-x-3 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">{{ $leadId ? 'Update Lead' : 'Create Lead' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
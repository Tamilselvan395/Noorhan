<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Lead Routing Rules</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Vehicle-brand & customer-type assignment per division. Highest priority wins.</p>
        </div>
        <button wire:click="openForm()" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">+ New Rule</button>
    </div>

    <x-table>
        <x-slot:head>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Division</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Condition</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Assign To</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Priority</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Active</th>
            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
        </x-slot:head>
        <x-slot:body>
            @forelse ($rules as $rule)
                <tr wire:key="rule-{{ $rule->id }}">
                    <td class="px-6 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ \App\Enums\Division::from($rule->division)->label() }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $rule->describe() }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $rule->user?->name ?? '— (hold in Triage)' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $rule->priority }}</td>
                    <td class="px-6 py-3">
                        <button wire:click="toggle({{ $rule->id }})" class="relative inline-flex h-5 w-9 items-center rounded-full transition {{ $rule->is_active ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                            <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition {{ $rule->is_active ? 'translate-x-4' : 'translate-x-1' }}"></span>
                        </button>
                    </td>
                    <td class="px-6 py-3 text-right space-x-2">
                        <button wire:click="openForm({{ $rule->id }})" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Edit</button>
                        <button wire:click="delete({{ $rule->id }})" wire:confirm="Delete this rule?" class="text-xs font-medium text-red-600 dark:text-red-400 hover:underline">Delete</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No rules yet — unassigned leads will wait in Triage.</td></tr>
            @endforelse
        </x-slot:body>
    </x-table>

    {{-- Rule form modal --}}
    @if ($formOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('formOpen', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $editingId ? 'Edit Rule' : 'New Routing Rule' }}</h3>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division</label>
                        <select wire:model.live="division" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Condition</label>
                            <select wire:model.live="condition_type" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                <option value="vehicle_brand">Vehicle Brand</option>
                                <option value="customer_type">Customer Type</option>
                                <option value="default">Default (fallback)</option>
                            </select>
                        </div>
                        <div>
                            @if ($condition_type !== 'default')
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Value</label>
                                <select wire:model="condition_value" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                    <option value="">— Select —</option>
                                    @if ($condition_type === 'vehicle_brand')
                                        @foreach (\App\Enums\VehicleBrandCategory::cases() as $v) <option value="{{ $v->value }}">{{ $v->label() }}</option> @endforeach
                                    @else
                                        @foreach (\App\Enums\CustomerType::cases() as $c) <option value="{{ $c->value }}">{{ $c->label() }}</option> @endforeach
                                    @endif
                                </select>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assign To</label>
                        <select wire:model="user_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">— Hold in Triage —</option>
                            @foreach ($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                        </select>
                        <p class="mt-1 text-[11px] text-gray-400">Map e.g. Swiftec + Distributor → Distributor Manager.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                            <input wire:model="priority" type="number" min="1" max="999" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2">Active</span>
                            </label>
                        </div>
                    </div>
                    @error('condition_value') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="$set('formOpen', false)" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                        <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save Rule</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
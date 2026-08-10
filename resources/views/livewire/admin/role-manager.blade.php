<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Roles & Permissions</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Role definitions drive every policy in the CRM.</p>
        </div>
        <button wire:click="$dispatch('open-role-form')" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">+ New Role</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach ($roles as $role)
            <x-card wire:key="r-{{ $role->id }}">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $role->name }}</h3>
                    @if ($role->name !== 'Super Admin')
                        <div class="space-x-2">
                            <button wire:click="$dispatch('open-role-form', { roleId: {{ $role->id }} })" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Edit</button>
                            <button wire:click="delete({{ $role->id }})" wire:confirm="Delete role?" class="text-xs font-medium text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </div>
                    @endif
                </div>
                <p class="mt-2 text-xs text-gray-400">{{ $role->permissions_count }} permissions · {{ $role->users_count }} users</p>
            </x-card>
        @endforeach
    </div>

    @if ($formOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('formOpen', false)"></div>
            <div class="relative mx-auto my-8 bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-2xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editingId ? 'Edit Role' : 'New Role' }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Name *</label>
                        <input wire:model="name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Permissions</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-80 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            @foreach ($groupedPermissions as $module => $perms)
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">{{ $module }}</p>
                                    @foreach ($perms as $perm)
                                        <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                            <input type="checkbox" value="{{ $perm->name }}" wire:model="permissions" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2">{{ explode('.', $perm->name)[1] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="$set('formOpen', false)" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                        <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save Role</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
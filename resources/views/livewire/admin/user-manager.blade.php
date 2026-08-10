<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Accounts, role assignment & activation.</p>
        </div>
        <button wire:click="$dispatch('open-user-form')" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">+ New User</button>
    </div>

    <x-card>
        <x-table>
            <x-slot:head>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Roles</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Last Login</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($users as $user)
                    <tr wire:key="u-{{ $user->id }}">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->roles as $role)
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400">{{ $role->name }}</span>
                                @empty
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">No role (legacy access)</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <button wire:click="toggleActive({{ $user->id }})" class="relative inline-flex h-5 w-9 items-center rounded-full transition {{ $user->is_active ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition {{ $user->is_active ? 'translate-x-4' : 'translate-x-1' }}"></span>
                            </button>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <button wire:click="$dispatch('open-user-form', { userId: {{ $user->id }} })" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Edit</button>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </x-card>

    @if ($formOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('formOpen', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editingId ? 'Edit User' : 'New User' }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                        <input wire:model="name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email *</label>
                        <input wire:model="email" type="email" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password {{ $editingId ? '(leave blank to keep)' : '*' }}</label>
                        <input wire:model="password" type="password" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
                    {{-- Linked Customer --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Linked Customer (portal access)
                        </label>

                        <select
                            wire:model="customer_id"
                            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        >
                            <option value="">— None —</option>

                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->displayName() }}
                                </option>
                            @endforeach
                        </select>

                        @error('customer_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Roles --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Roles
                        </label>

                        <div class="max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg p-2 space-y-1">
                            @foreach ($allRoles as $role)
                                <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                    <input
                                        type="checkbox"
                                        value="{{ $role->name }}"
                                        wire:model="roles"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >

                                    <span class="ml-2">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-green-600 focus:ring-green-500"><span class="ml-2">Active</span>
                    </label>
                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="$set('formOpen', false)" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                        <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
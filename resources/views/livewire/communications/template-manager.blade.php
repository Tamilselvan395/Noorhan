<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Email Templates</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Variables: <code class="text-xs">{{'{{customer.name}}'}}</code> <code class="text-xs">{{'{{lead.name}}'}}</code> <code class="text-xs">{{'{{company.name}}'}}</code> <code class="text-xs">{{'{{user.name}}'}}</code> <code class="text-xs">{{'{{unsubscribe_url}}'}}</code> + any keys you pass in context.</p>
        </div>
        <button wire:click="$dispatch('open-template-form')" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">+ New Template</button>
    </div>

    <x-card>
        <x-table>
            <x-slot:head>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Template</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Key</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($templates as $template)
                    <tr wire:key="tpl-{{ $template->id }}">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $template->name }}</p>
                            <p class="text-xs text-gray-400">{{ $template->subject }}</p>
                        </td>
                        <td class="px-6 py-3 text-xs font-mono text-gray-500">{{ $template->key }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300 capitalize">{{ $template->category }}</td>
                        <td class="px-6 py-3">
                            <button wire:click="toggle({{ $template->id }})" class="relative inline-flex h-5 w-9 items-center rounded-full transition {{ $template->is_active ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition {{ $template->is_active ? 'translate-x-4' : 'translate-x-1' }}"></span>
                            </button>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <button wire:click="$dispatch('open-template-form', { templateId: {{ $template->id }} })" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Edit</button>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </x-card>

    @if ($formOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('formOpen', false)"></div>
            <div class="relative mx-auto my-8 bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-2xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editingId ? 'Edit Template' : 'New Template' }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                            <input wire:model="name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key *</label>
                            <input wire:model="key" placeholder="snake_case_key" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none" {{ $editingId ? 'disabled' : '' }}></div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <select wire:model="category" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                @foreach (['general','onboarding','sales','finance','marketing'] as $c) <option value="{{ $c }}">{{ ucfirst($c) }}</option> @endforeach
                            </select></div>
                        <label class="flex items-end pb-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span class="ml-2">Active</span>
                        </label>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject *</label>
                        <input wire:model="subject" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Body *</label>
                        <textarea wire:model="body" rows="8" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none"></textarea></div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="$set('formOpen', false)" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                        <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save Template</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
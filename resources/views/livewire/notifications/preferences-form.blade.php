<x-card>
    <x-slot:header><h3 class="font-semibold">Channels by Category</h3></x-slot:header>

    <form wire:submit="save">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">In-App</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($categories as $key => $label)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $label }}</td>
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model="prefs.{{ $key }}.database" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model="prefs.{{ $key }}.mail" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 flex justify-end">
            <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save Preferences</button>
        </div>
    </form>
</x-card>
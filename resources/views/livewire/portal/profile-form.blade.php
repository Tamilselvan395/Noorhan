<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
    <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
            <input wire:model="name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
            <input wire:model="phone" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp</label>
            <input wire:model="whatsapp" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
        <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
            <input wire:model="address" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
            <input wire:model="city" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
            <input wire:model="country" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email (managed by Noorhan)</label>
            <input value="{{ auth()->user()->email }}" disabled class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40 text-sm text-gray-400">
        </div>
        <div class="md:col-span-2 flex justify-end">
            <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save Profile</button>
        </div>
    </form>
</div>
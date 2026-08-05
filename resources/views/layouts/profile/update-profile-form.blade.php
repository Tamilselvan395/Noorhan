<x-card>
    <x-slot:header><h3 class="font-semibold">Change Password</h3></x-slot:header>

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current password</label>
            <input type="password" wire:model="current_password"
                   class="mt-1.5 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            @error('current_password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New password</label>
                <input type="password" wire:model="password"
                       class="mt-1.5 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                @error('password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm new password</label>
                <input type="password" wire:model="password_confirmation"
                       class="mt-1.5 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            </div>
        </div>
        <button class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">Update Password</button>
    </form>
</x-card>
<div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
    <form wire:submit="upload" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">File (max 10MB)</label>
            <input type="file" wire:model="file" class="mt-1 w-full text-sm">
            @error('file') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
            <select wire:model="category" class="mt-1 w-full px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                @foreach (\App\Enums\DocumentCategory::cases() as $c) <option value="{{ $c->value }}">{{ $c->label() }}</option> @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Expires (optional)</label>
            <input type="date" wire:model="expires_at" class="mt-1 w-full px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
        </div>
        <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Upload</button>
    </form>
    <div wire:loading wire:target="file" class="mt-2 text-xs text-blue-600 dark:text-blue-400">Uploading…</div>
</div>
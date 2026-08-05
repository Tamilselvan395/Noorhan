<div x-data="{ open: @entangle('open') }" @open-card-scan.window="open = true">
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div x-show="open" x-transition class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Business Card Scanner</h3>
            <p class="text-xs text-gray-400 mt-0.5">Upload a card photo — it lands in Triage for data completion (OCR arrives with the AI Engine).</p>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center">
                    @if ($file)
                        <img src="{{ $file->temporaryUrl() }}" class="mx-auto max-h-40 rounded-lg shadow">
                    @else
                        <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Photo of the business card</p>
                    @endif
                    <input type="file" wire:model="file" accept="image/*" class="mt-3 text-sm">
                    @error('file') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note (where you met, context…)</label>
                    <textarea wire:model="note" rows="2" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Upload & Capture</button>
                </div>
            </form>
        </div>
    </div>
</div>
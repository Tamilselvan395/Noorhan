<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Book a Service</h3>
    <form wire:submit="book" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service *</label>
            <select wire:model="product_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                <option value="">—</option>
                @foreach ($services as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
            </select>
            @error('product_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preferred Date & Time *</label>
            <input wire:model="scheduled_at" type="datetime-local" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            @error('scheduled_at') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle (Make Model Year · Plate)</label>
            <div class="grid grid-cols-2 gap-2 mt-1">
                <input wire:model="vehicle_make" placeholder="Make" class="px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                <input wire:model="vehicle_model" placeholder="Model" class="px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                <input wire:model="vehicle_year" placeholder="Year" class="px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                <input wire:model="plate" placeholder="Plate" class="px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            </div>
        </div>
        <div class="md:col-span-3 flex justify-end">
            <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Book Service</button>
        </div>
    </form>
</div>
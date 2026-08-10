<x-card>
    <x-slot:header><h3 class="font-semibold">Compose Email</h3></x-slot:header>
    <form wire:submit="send" class="p-4 space-y-3">
        <div class="grid grid-cols-2 gap-3">
            <select wire:model.live="entityType" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                <option value="customer">Customer</option>
                <option value="lead">Lead</option>
            </select>
            <select wire:model.live="entityId" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                <option value="">— Recipient —</option>
                @foreach (($entityType === 'customer' ? $customers : $leads) as $entity)
                    <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                @endforeach
            </select>
        </div>
        <input wire:model="to" type="email" placeholder="To *" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('to') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

        <select wire:model.live="templateKey" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="">— Custom (no template) —</option>
            @foreach ($templates as $template) <option value="{{ $template->key }}">{{ $template->name }}</option> @endforeach
        </select>

        <input wire:model="subject" placeholder="Subject *" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <textarea wire:model="body" rows="6" placeholder="Body *" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
        @error('body') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

        <button class="w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Send & Log</button>
        <p class="text-[11px] text-gray-400">Opted-out recipients are skipped automatically. Every send is logged to the timeline.</p>
    </form>
</x-card>
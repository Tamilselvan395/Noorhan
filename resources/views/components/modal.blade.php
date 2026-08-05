@props(['name'])

<div x-data="{ show: @entangle($attributes->wire('model')).defer }" 
     x-show="show" 
     class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
     style="display: none;">
     
    <!-- Backdrop -->
    <div x-show="show" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="show = false"></div>

    <!-- Modal Content -->
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6 z-10 relative">
         
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title ?? 'Modal Title' }}</h3>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
        </div>
        
        {{ $slot }}
    </div>
</div>
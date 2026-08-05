<div x-data="{ show: false, message: '', type: 'success' }" 
     @notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform translate-y-2"
     class="fixed bottom-4 right-4 z-50 min-w-[300px]">
     
    <div :class="type === 'success' ? 'bg-green-500' : 'bg-red-500'" class="p-4 rounded-lg shadow-lg text-white flex items-center justify-between">
        <span x-text="message"></span>
        <button @click="show = false" class="ml-4 text-white hover:text-gray-200">&times;</button>
    </div>
</div>
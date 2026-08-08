<div class="relative">
    <button wire:click="toggle" class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 relative focus:outline-none">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        @if ($this->unreadCount() > 0)
            <span class="absolute top-1 right-1 min-w-[16px] h-4 px-0.5 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $this->unreadCount() }}</span>
        @endif
    </button>

    @if ($open)
        <div class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden z-50">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</h3>
                <button wire:click="markAllRead" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Mark all read</button>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto">
                @forelse ($notifications as $notification)
                    <li class="{{ $notification->read_at ? '' : 'bg-blue-50/60 dark:bg-blue-900/20' }}">
                        <a href="{{ $notification->data['url'] ?? '#' }}" wire:click="markRead('{{ $notification->id }}')"
                           class="flex items-start space-x-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <span class="mt-1 w-2 h-2 rounded-full shrink-0 {{ $notification->read_at ? 'bg-gray-300 dark:bg-gray-600' : 'bg-blue-500' }}"></span>
                            <div>
                                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $notification->data['message'] ?? 'Notification' }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-400">You're all caught up. 🎉</li>
                @endforelse
            </ul>
            <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('settings.notifications') }}" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Notification preferences</a>
            </div>
        </div>
    @endif
</div>
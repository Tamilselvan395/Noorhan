@props(['label', 'value', 'delta' => null, 'hint' => null, 'icon' => 'chart', 'accent' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400'])

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $value }}</p>
        </div>
        <div class="w-10 h-10 rounded-lg {{ $accent }} flex items-center justify-center shrink-0">
            @switch($icon)
                @case('users')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    @break
                @case('login')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    @break
                @case('shield')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    @break
                @case('bolt')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    @break
                @default
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            @endswitch
        </div>
    </div>

    <div class="mt-3 flex items-center text-xs">
        @if ($delta !== null)
            <span class="inline-flex items-center font-semibold {{ $delta >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                <svg class="w-3.5 h-3.5 {{ $delta < 0 ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                {{ abs($delta) }}%
            </span>
            <span class="ml-1 text-gray-400 dark:text-gray-500">vs previous period</span>
        @endif
        @if ($hint)
            <span class="text-gray-400 dark:text-gray-500 {{ $delta !== null ? 'ml-2' : '' }}">{{ $hint }}</span>
        @endif
    </div>
</div>
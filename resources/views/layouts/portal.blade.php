<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('noorhan.name') }} Portal — @yield('title', 'Home')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold">N</div>
                <span class="font-bold text-gray-900 dark:text-white hidden sm:block">{{ config('noorhan.name') }} <span class="text-blue-600 dark:text-blue-400">Portal</span></span>
            </div>

            <nav class="flex items-center space-x-1 sm:space-x-2 text-sm">
                @foreach ([
                    'portal.dashboard' => ['Home', '/portal'],
                    'portal.quotations' => ['Quotations', '/portal/quotations'],
                    'portal.invoices' => ['Invoices', '/portal/invoices'],
                    'portal.orders' => ['Orders', '/portal/orders'],
                    'portal.payments' => ['Payments', '/portal/payments'],
                    'portal.profile' => ['Profile', '/portal/profile'],
                    'portal.appointments' => ['Appointments', '/portal/appointments'],
                ] as $route => [$label, $href])
                    <a href="{{ $href }}" class="px-2 sm:px-3 py-2 rounded-lg font-medium {{ request()->routeIs($route) ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ $label }}</a>
                @endforeach

                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="px-3 py-2 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">Sign out</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        @if (session('status'))
            <div class="mb-6 p-3 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-400">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>

    <x-toast />
    @livewireScripts
    @stack('scripts')
</body>
</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: true }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('noorhan.name') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Fonts & Tailwind CSS -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200">
    
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <x-header />

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900">
                @yield('content')
            </main>

            <!-- Footer -->
            <x-footer />
        </div>
    </div>

    <!-- Global Modals & Notifications -->
    <x-toast />
    
    @livewireScripts
    @stack('scripts')
</body>
</html>
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:inset-0">

    <div class="flex items-center h-16 px-6 border-b border-gray-200 dark:border-gray-700">
        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold">N</div>
        <span class="ml-3 text-lg font-bold text-gray-900 dark:text-white">Noorhan CRM</span>
    </div>

    <nav class="mt-6 px-4 space-y-1">
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            Dashboard
        </a>
        <a href="{{ route('settings.profile') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('settings.profile') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            Profile
        </a>
        <a href="{{ route('settings.security') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('settings.security') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            Security Center
        </a>
        <a href="{{ route('leads.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('leads.*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            Leads
        </a>
    </nav>
</aside>
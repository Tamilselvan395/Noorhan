<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings Center</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Organization profile, commercial defaults, appearance & maintenance.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Nav --}}
        <div class="space-y-4">
            <x-card>
                <ul class="p-2 space-y-1">
                    @foreach (['general' => 'General / Company', 'defaults' => 'Commercial Defaults', 'appearance' => 'Appearance', 'maintenance' => 'Maintenance'] as $key => $label)
                        <li>
                            <button wire:click="switchSection('{{ $key }}')"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition {{ $section === $key ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                {{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </x-card>

            <x-card>
                <x-slot:header><h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Quick Links</h3></x-slot:header>
                <ul class="p-2 space-y-1 text-sm">
                    <li><a href="{{ route('settings.profile') }}" class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">My Profile</a></li>
                    <li><a href="{{ route('settings.security') }}" class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Security & Sessions</a></li>
                    <li><a href="{{ route('settings.notifications') }}" class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Notification Preferences</a></li>
                    <li><a href="{{ route('settings.routing-rules') }}" class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Lead Routing Rules</a></li>
                    <li><a href="{{ route('settings.zoho') }}" class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Zoho Books</a></li>
                    <li><a href="{{ route('system.audit') }}" class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Audit Trail</a></li>
                </ul>
            </x-card>
        </div>

        {{-- Content --}}
        <div class="lg:col-span-3 space-y-6">
            @if ($section === 'general')
                <x-card>
                    <x-slot:header><h3 class="font-semibold">General / Company Profile</h3></x-slot:header>
                    <form wire:submit="saveGeneral" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Name *</label>
                            <input wire:model="general.company_name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            @error('general.company_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input wire:model="general.company_email" type="email" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                            <input wire:model="general.company_phone" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                            <input wire:model="general.country" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</label>
                            <select wire:model="general.timezone" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                @foreach (['Asia/Dubai','Asia/Riyadh','Asia/Kuwait','Europe/London','Europe/Paris','America/New_York','Asia/Karachi','Asia/Seoul','Asia/Tokyo'] as $tz)
                                    <option value="{{ $tz }}">{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                            <input wire:model="general.address" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Format</label>
                            <select wire:model="general.date_format" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                <option value="M d, Y">Mar 05, 2026</option>
                                <option value="d M Y">05 Mar 2026</option>
                                <option value="Y-m-d">2026-03-05</option>
                                <option value="d/m/Y">05/03/2026</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex justify-end">
                            <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save General</button>
                        </div>
                    </form>
                </x-card>
            @elseif ($section === 'defaults')
                <x-card>
                    <x-slot:header><h3 class="font-semibold">Commercial Defaults</h3></x-slot:header>
                    <form wire:submit="saveDefaults" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Currency</label>
                            <select wire:model="defaults.currency" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                @foreach (['USD','AED','SAR','EUR','GBP','KWD','QAR'] as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Tax Rate %</label>
                            <input wire:model="defaults.tax_rate" type="number" step="0.01" min="0" max="100" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quotation Validity (days)</label>
                            <input wire:model="defaults.quotation_valid_days" type="number" min="1" max="365" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Due (days)</label>
                            <input wire:model="defaults.invoice_due_days" type="number" min="1" max="365" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Standard Payment Terms</label>
                            <textarea wire:model="defaults.payment_terms" rows="2" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                        </div>
                        <div class="md:col-span-2 flex justify-end">
                            <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save Defaults</button>
                        </div>
                    </form>
                </x-card>
            @elseif ($section === 'appearance')
                <x-card>
                    <x-slot:header><h3 class="font-semibold">Appearance</h3></x-slot:header>
                    <form wire:submit="saveAppearance" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">My Theme</label>
                            <select wire:model="userTheme" class="mt-1 w-full max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Saved to your profile and becomes the organization default.</p>
                        </div>
                        <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save Appearance</button>
                    </form>
                </x-card>
            @else
                <x-card>
                    <x-slot:header><h3 class="font-semibold">Maintenance</h3></x-slot:header>
                    <div class="space-y-4 text-sm">
                        <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="font-medium text-gray-800 dark:text-gray-200">Prune System Logs</p>
                                <p class="text-xs text-gray-400 mt-0.5">Delete activity older than {{ config('noorhan.audit.activity_retention_days') }}d and audit older than {{ config('noorhan.audit.retention_days') }}d.</p>
                            </div>
                            <button wire:click="runPrune" wire:confirm="Run log pruning now?" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold">Run Now</button>
                        </div>
                        <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="font-medium text-gray-800 dark:text-gray-200">Clear Application Cache</p>
                                <p class="text-xs text-gray-400 mt-0.5">Flushes cached settings & application cache.</p>
                            </div>
                            <button wire:click="clearCache" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Clear</button>
                        </div>
                        @if ($maintenanceMessage)
                            <p class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700/40 text-xs text-gray-600 dark:text-gray-300 font-mono">{{ $maintenanceMessage }}</p>
                        @endif
                    </div>
                </x-card>
            @endif
        </div>
    </div>
</div>
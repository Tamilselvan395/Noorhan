<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Zoho Books Integration</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">External accounting ledger sync — contacts, estimates, sales orders, invoices & payments.</p>
    </div>

    {{-- Connection --}}
    <x-card>
        <x-slot:header><h3 class="font-semibold">Connection</h3></x-slot:header>
        @if ($connection)
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-gray-400">Organization</dt><dd class="font-medium text-gray-800 dark:text-gray-200">{{ $connection->organization_id }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Token Status</dt>
                    <dd class="{{ $connection->token_expires_at?->isFuture() ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $connection->token_expires_at?->isFuture() ? 'Valid (auto-refresh on)' : 'Expired — will refresh on next sync' }}
                    </dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Enabled</dt><dd class="text-gray-800 dark:text-gray-200">{{ config('zoho.enabled') ? 'Yes' : 'No (set ZOHO_ENABLED=true)' }}</dd></div>
            </dl>
            <div class="mt-4 flex items-center space-x-3">
                <button wire:click="testConnection" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Test Connection</button>
                @if ($testResult) <span class="text-sm {{ str_starts_with($testResult, '✓') ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $testResult }}</span> @endif
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Not connected. Configure <code>.env</code> (ZOHO_CLIENT_ID, ZOHO_CLIENT_SECRET, ZOHO_ORGANIZATION_ID) then authorize.</p>
            <a href="{{ route('zoho.connect') }}" class="inline-block px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Connect Zoho Books</a>
        @endif
    </x-card>

    {{-- Sync toggles --}}
    @if ($connection)
        <x-card>
            <x-slot:header><h3 class="font-semibold">Sync Preferences</h3></x-slot:header>
            <div class="space-y-3">
                @foreach (['sync_customers' => 'Customers → Contacts', 'sync_estimates' => 'Quotations → Estimates', 'sync_sales_orders' => 'Sales Orders → Sales Orders', 'sync_invoices' => 'Invoices → Invoices', 'sync_payments' => 'Payments → Customer Payments'] as $key => $label)
                    <label class="flex items-center justify-between text-sm text-gray-700 dark:text-gray-300">
                        <span>{{ $label }}</span>
                        <input type="checkbox" wire:model="{{ $key }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </label>
                @endforeach
                <button wire:click="saveSettings" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Save Preferences</button>
            </div>
        </x-card>
    @endif

    {{-- Sync logs --}}
    <x-card>
        <x-slot:header><h3 class="font-semibold">Sync Log (retry queue)</h3></x-slot:header>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($logs as $log)
                <li class="flex items-center justify-between p-4" wire:key="zl-{{ $log->id }}">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $log->entity_type }} #{{ $log->syncable_id }} <span class="text-xs text-gray-400">({{ $log->operation }})</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Attempts: {{ $log->attempts }} · {{ $log->updated_at->diffForHumans() }}
                            @if ($log->error) <span class="text-red-500">— {{ $log->error }}</span> @endif
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $log->status === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : ($log->status === 'failed' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400') }}">
                            {{ ucfirst($log->status) }}
                        </span>
                        @if ($log->status === 'failed')
                            <button wire:click="retry({{ $log->id }})" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Retry</button>
                        @endif
                    </div>
                </li>
            @empty
                <li class="p-8 text-center text-sm text-gray-400">No sync activity yet.</li>
            @endforelse
        </ul>
    </x-card>
</div>
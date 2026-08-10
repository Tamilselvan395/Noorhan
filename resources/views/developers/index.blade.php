@extends('layouts.app')
@section('title', 'Developers')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Developers / API</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Issue Sanctum tokens and explore the REST API.</p>
    </div>

    <livewire:developers.token-manager />

    <x-card>
        <x-slot:header><h3 class="font-semibold">API Reference (base: <code class="text-xs">{{ config('app.url') }}/api/v1</code>)</h3></x-slot:header>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead><tr>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Method</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Endpoint</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Ability</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Description</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @foreach ([
                        ['GET', '/stats', 'read', 'Executive KPI snapshot'],
                        ['GET', '/leads?status=&division=&search=&per_page=', 'read', 'List/filter leads (paginated)'],
                        ['GET', '/leads/{id}', 'read', 'Lead detail + recent activity'],
                        ['POST', '/leads', 'write', 'Create lead (same validation as CRM)'],
                        ['GET', '/customers', 'read', 'List/filter customers'],
                        ['POST', '/customers', 'write', 'Create customer'],
                        ['GET', '/products', 'read', 'Product catalog'],
                        ['GET', '/suppliers', 'read', 'Supplier directory with ratings'],
                        ['GET', '/quotations · /sales-orders · /invoices · /payments', 'read', 'Commercial documents & payments'],
                    ] as [$method, $endpoint, $ability, $desc])
                        <tr>
                            <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs font-bold {{ $method === 'GET' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' }}">{{ $method }}</span></td>
                            <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-200">{{ $endpoint }}</td>
                            <td class="px-4 py-2 text-xs text-gray-500">{{ $ability }}</td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $desc }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="p-4 text-xs text-gray-400">Authenticate with <code>Authorization: Bearer &lt;token&gt;</code>. Rate limit: 60 req/min. Errors always return JSON.</p>
    </x-card>
</div>
@endsection
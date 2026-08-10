@extends('layouts.portal')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold">Welcome, {{ $customer->name }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your account at a glance.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-400">Outstanding Balance</p>
            <p class="mt-1 text-2xl font-bold {{ $outstanding > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}">{{ \App\Helpers\CurrencyHelper::format($outstanding) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-400">Credit Balance</p>
            <p class="mt-1 text-2xl font-bold text-violet-600 dark:text-violet-400">{{ \App\Helpers\CurrencyHelper::format((float) $customer->credit_balance) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-400">Open Orders / Quotations</p>
            <p class="mt-1 text-2xl font-bold">{{ $openOrders }} / {{ $openQuotations }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="font-semibold">Recent Invoices</h3>
                <a href="{{ route('portal.invoices') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View all</a>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($recentInvoices as $invoice)
                    <li class="px-5 py-3 flex justify-between items-center">
                        <a href="{{ route('portal.invoices.show', $invoice) }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ $invoice->reference }}</a>
                        <span class="text-sm">{{ \App\Helpers\CurrencyHelper::format((float) $invoice->balance_due) }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $invoice->status()->badge() }}">{{ $invoice->status()->label() }}</span>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-sm text-gray-400">No invoices yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="font-semibold">Recent Orders</h3>
                <a href="{{ route('portal.orders') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View all</a>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($recentOrders as $order)
                    <li class="px-5 py-3 flex justify-between items-center">
                        <a href="{{ route('portal.orderShow', $order) }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ $order->reference }}</a>
                        <span class="text-sm">{{ \App\Helpers\CurrencyHelper::format((float) $order->total) }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status()->badge() }}">{{ $order->status()->label() }}</span>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-sm text-gray-400">No orders yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
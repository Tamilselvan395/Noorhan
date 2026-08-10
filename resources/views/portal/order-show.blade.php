@extends('layouts.portal')

@section('title', $order->reference)

@section('content')

<div class="space-y-6">

    {{-- Order Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                Order {{ $order->reference }}
            </h1>

            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Created {{ $order->created_at?->format('M d, Y') }}
            </p>
        </div>

        @php
            $status = $order->status()->value;
        @endphp

        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
            @if ($status === 'delivered')
                bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
            @elseif ($status === 'cancelled')
                bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
            @elseif ($status === 'pending')
                bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
            @else
                bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
            @endif
        ">
            {{ ucfirst($status) }}
        </span>
    </div>


    {{-- Order Details --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">

            <div>
                <p class="text-gray-400">Order Number</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ $order->reference }}
                </p>
            </div>

            <div>
                <p class="text-gray-400">Order Date</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ $order->created_at?->format('M d, Y') }}
                </p>
            </div>

            <div>
                <p class="text-gray-400">Expected Delivery</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ $order->expected_delivery_date?->format('M d, Y') ?? '—' }}
                </p>
            </div>

        </div>

    </div>


    {{-- Items --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">

            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        Description
                    </th>

                    <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        Qty
                    </th>

                    <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        Price
                    </th>

                    <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        Total
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

                @forelse ($order->items as $item)

                    <tr>
                        <td class="px-5 py-3 text-sm text-gray-900 dark:text-white">
                            {{ $item->description }}
                        </td>

                        <td class="px-5 py-3 text-sm text-right text-gray-600 dark:text-gray-300">
                            {{ $item->quantity }}
                        </td>

                        <td class="px-5 py-3 text-sm text-right text-gray-600 dark:text-gray-300">
                            {{ number_format((float) $item->unit_price, 2) }}
                        </td>

                        <td class="px-5 py-3 text-sm text-right font-medium text-gray-900 dark:text-white">
                            {{ number_format((float) $item->line_total, 2) }}
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-400">
                            No items found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>


        {{-- Totals --}}
        <div class="p-5 flex justify-end border-t border-gray-200 dark:border-gray-700">

            <dl class="w-56 text-sm space-y-1">

                <div class="flex justify-between">
                    <dt class="text-gray-400">Subtotal</dt>
                    <dd>
                        {{ \App\Helpers\CurrencyHelper::format((float) $order->subtotal) }}
                    </dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-gray-400">Discount</dt>
                    <dd>
                        -{{ \App\Helpers\CurrencyHelper::format((float) $order->discount_amount) }}
                    </dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-gray-400">Tax</dt>
                    <dd>
                        {{ \App\Helpers\CurrencyHelper::format((float) $order->tax_amount) }}
                    </dd>
                </div>

                <div class="flex justify-between font-bold text-base pt-2">
                    <dt>Total</dt>
                    <dd class="text-blue-600 dark:text-blue-400">
                        {{ \App\Helpers\CurrencyHelper::format((float) $order->total) }}
                    </dd>
                </div>

            </dl>

        </div>

    </div>


    {{-- Delivery Details --}}
    @if ($order->delivery_address || $order->delivery_notes)

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">

            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">
                Delivery Details
            </h3>

            <div class="space-y-4 text-sm">

                @if ($order->delivery_address)
                    <div>
                        <p class="text-gray-400 mb-1">Delivery Address</p>
                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-line">
                            {{ $order->delivery_address }}
                        </p>
                    </div>
                @endif

                @if ($order->delivery_notes)
                    <div>
                        <p class="text-gray-400 mb-1">Delivery Notes</p>
                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-line">
                            {{ $order->delivery_notes }}
                        </p>
                    </div>
                @endif

            </div>

        </div>

    @endif

</div>

@endsection
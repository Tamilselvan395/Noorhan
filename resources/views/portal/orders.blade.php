@extends('layouts.portal')

@section('title', 'Orders')

@section('content')

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">

        <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                    Order
                </th>

                <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                    Status
                </th>

                <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                    Delivery Date
                </th>

                <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                    Total
                </th>

                <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                    Action
                </th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

            @forelse ($orders as $order)

                <tr>

                    <td class="px-5 py-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $order->reference }}
                        </div>

                        <div class="text-xs text-gray-400 mt-1">
                            {{ $order->created_at?->format('M d, Y') }}
                        </div>
                    </td>

                    <td class="px-5 py-4">
                        @php
                            $status = $order->status()->value;
                        @endphp

                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
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
                    </td>

                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                        {{ $order->expected_delivery_date?->format('M d, Y') ?? '—' }}
                    </td>

                    <td class="px-5 py-4 text-sm text-right font-medium text-gray-900 dark:text-white">
                        {{ \App\Helpers\CurrencyHelper::format((float) $order->total) }}
                    </td>

                    <td class="px-5 py-4 text-right">
                        <a
                            href="{{ route('portal.orders.show', $order) }}"
                            class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline"
                        >
                            View
                        </a>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">
                        No orders found.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection
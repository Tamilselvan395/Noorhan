@extends('layouts.portal')

@section('title', 'Payments')

@section('content')

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            Payments
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            View your payment history and transaction details.
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">

            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        Reference
                    </th>

                    <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        Payment Date
                    </th>

                    <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        Method
                    </th>

                    <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        Amount
                    </th>

                    <th class="px-5 py-3 text-center text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        Status
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

                @forelse ($payments as $payment)

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">

                        <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $payment->reference }}
                        </td>

                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ optional($payment->payment_date)->format('d M Y') }}
                        </td>

                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300 capitalize">
                            {{ str_replace('_', ' ', $payment->method) }}
                        </td>

                        <td class="px-5 py-4 text-sm text-right font-semibold text-gray-900 dark:text-white">
                            {{ \App\Helpers\CurrencyHelper::format((float) $payment->amount) }}
                        </td>

                        <td class="px-5 py-4 text-center">

                            @php
                                $status = strtolower($payment->status);

                                $statusClasses = match ($status) {
                                    'completed', 'paid', 'success' =>
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',

                                    'pending' =>
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',

                                    'failed', 'cancelled', 'canceled' =>
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',

                                    default =>
                                        'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                };
                            @endphp

                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClasses }}">
                                {{ ucfirst($status) }}
                            </span>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            No payments found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>
    </div>

    @if (method_exists($payments, 'links'))
        <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $payments->links() }}
        </div>
    @endif

</div>

@endsection
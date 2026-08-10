@extends('layouts.portal')

@section('title', $invoice->reference)

@section('content')

<div class="flex items-center justify-between mb-4 print:hidden">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        Invoice {{ $invoice->reference }}
    </h2>

    <button
        type="button"
        onclick="window.print()"
        class="print:hidden px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium"
    >
        Print Invoice
    </button>
</div>

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
            @foreach ($invoice->items as $item)
                <tr>
                    <td class="px-5 py-3 text-sm">
                        {{ $item->description }}
                    </td>

                    <td class="px-5 py-3 text-sm text-right">
                        {{ $item->quantity }}
                    </td>

                    <td class="px-5 py-3 text-sm text-right">
                        {{ number_format((float) $item->unit_price, 2) }}
                    </td>

                    <td class="px-5 py-3 text-sm text-right font-medium">
                        {{ number_format((float) $item->line_total, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="p-5 flex justify-end">
        <dl class="w-56 text-sm space-y-1">

            <div class="flex justify-between">
                <dt class="text-gray-400">Subtotal</dt>
                <dd>
                    {{ number_format((float) $invoice->subtotal, 2) }}
                </dd>
            </div>

            <div class="flex justify-between">
                <dt class="text-gray-400">Discount</dt>
                <dd>
                    -{{ number_format((float) $invoice->discount_amount, 2) }}
                </dd>
            </div>

            <div class="flex justify-between">
                <dt class="text-gray-400">Tax</dt>
                <dd>
                    {{ number_format((float) $invoice->tax_amount, 2) }}
                </dd>
            </div>

            <div class="flex justify-between font-bold text-base">
                <dt>Total</dt>
                <dd class="text-blue-600 dark:text-blue-400">
                    {{ \App\Helpers\CurrencyHelper::format((float) $invoice->total) }}
                </dd>
            </div>

            {{-- Balance Due --}}
            <div class="flex justify-between font-bold text-base text-amber-600 dark:text-amber-400 pt-2">
                <dt>Balance Due</dt>
                <dd>
                    {{ \App\Helpers\CurrencyHelper::format((float) $invoice->balance_due) }}
                </dd>
            </div>

        </dl>
    </div>
</div>

@if ($invoice->terms)
    <p class="mt-4 text-xs text-gray-400 whitespace-pre-line">
        {{ $invoice->terms }}
    </p>
@endif

@endsection
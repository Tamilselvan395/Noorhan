@extends('layouts.portal')
@section('title', $quotation->reference)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Quotation {{ $quotation->reference }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Valid until {{ $quotation->valid_until?->format('M d, Y') ?? '—' }}</p>
        </div>
        @if ($quotation->status === 'sent')
            <div class="flex space-x-2">
                <form method="POST" action="{{ route('portal.quotations.accept', $quotation) }}">
                    @csrf
                    <button class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">Accept</button>
                </form>
                <form method="POST" action="{{ route('portal.quotations.decline', $quotation) }}">
                    @csrf
                    <button class="px-4 py-2 rounded-lg border border-red-300 dark:border-red-800 text-red-600 dark:text-red-400 text-sm font-semibold hover:bg-red-50 dark:hover:bg-red-900/30">Decline</button>
                </form>
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Description</th>
                <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Qty</th>
                <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Price</th>
                <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($quotation->items as $item)
                    <tr>
                        <td class="px-5 py-3 text-sm">{{ $item->description }}</td>
                        <td class="px-5 py-3 text-sm text-right">{{ $item->quantity }}</td>
                        <td class="px-5 py-3 text-sm text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="px-5 py-3 text-sm text-right font-medium">{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-5 flex justify-end">
            <dl class="w-56 text-sm space-y-1">
                <div class="flex justify-between"><dt class="text-gray-400">Subtotal</dt><dd>{{ number_format((float) $quotation->subtotal, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Discount</dt><dd>-{{ number_format((float) $quotation->discount_amount, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Tax</dt><dd>{{ number_format((float) $quotation->tax_amount, 2) }}</dd></div>
                <div class="flex justify-between font-bold text-base"><dt>Total</dt><dd class="text-blue-600 dark:text-blue-400">{{ \App\Helpers\CurrencyHelper::format((float) $quotation->total) }}</dd></div>
            </dl>
        </div>
    </div>

    @if ($quotation->terms) <p class="text-xs text-gray-400 whitespace-pre-line">{{ $quotation->terms }}</p> @endif
</div>
@endsection
@extends('layouts.portal')
@section('title', 'Quotations')

@section('content')
<h1 class="text-2xl font-bold mb-6">Quotations</h1>
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Reference</th>
            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Date</th>
            <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total</th>
            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($quotations as $quotation)
                <tr>
                    <td class="px-5 py-3 text-sm font-medium">{{ $quotation->reference }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $quotation->created_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3 text-sm text-right font-medium">{{ \App\Helpers\CurrencyHelper::format((float) $quotation->total) }}</td>
                    <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $quotation->status()->badge() }}">{{ $quotation->status()->label() }}</span></td>
                    <td class="px-5 py-3 text-right"><a href="{{ route('portal.quotations.show', $quotation) }}" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">No quotations yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
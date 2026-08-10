@extends('layouts.guest')
@section('title', 'Unsubscribed')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">You're unsubscribed</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ $customer->email }} will no longer receive marketing emails from {{ config('noorhan.name') }}.
            Transactional emails (quotations, invoices) will still be delivered.
        </p>
    </div>
</div>
@endsection
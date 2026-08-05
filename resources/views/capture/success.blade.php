@extends('layouts.guest')
@section('title', 'Thank You')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
        <div class="w-14 h-14 rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 flex items-center justify-center mx-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">Enquiry received</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Thank you for contacting Noorhan Group. A specialist will reach out within one business day.</p>
    </div>
</div>
@endsection
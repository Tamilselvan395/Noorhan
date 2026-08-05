@extends('layouts.guest')
@section('title', 'Verify Email')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <h2 class="mt-4 text-xl font-bold">Verify your email</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">A verification link has been sent to your email address. Please click the link to activate your account.</p>

        @if (session('status') === 'verification-link-sent')
            <div class="mt-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-sm text-green-600 dark:text-green-400">A new verification link has been sent.</div>
        @endif

        <div class="mt-6 flex items-center justify-center space-x-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">Resend Email</button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Sign out</button>
            </form>
        </div>
    </div>
</div>
@endsection
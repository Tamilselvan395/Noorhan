@extends('layouts.guest')
@section('title', 'Forgot Password')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
        </div>
        <h2 class="mt-4 text-xl font-bold text-center">Reset your password</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">Enter your email and we'll send you a reset link.</p>

        @if (session('status'))
            <div class="mt-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-sm text-green-600 dark:text-green-400">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1.5 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <button class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">Email Reset Link</button>
        </form>
        <p class="mt-4 text-center text-sm"><a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Back to sign in</a></p>
    </div>
</div>
@endsection
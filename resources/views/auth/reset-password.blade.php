@extends('layouts.guest')
@section('title', 'Reset Password')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <h2 class="text-xl font-bold">Choose a new password</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Must be 8+ chars with upper, lower, number & symbol.</p>

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required
                       class="mt-1.5 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New password</label>
                <input type="password" name="password" required
                       class="mt-1.5 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                @error('password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm password</label>
                <input type="password" name="password_confirmation" required
                       class="mt-1.5 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            </div>
            <button class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">Reset Password</button>
        </form>
    </div>
</div>
@endsection
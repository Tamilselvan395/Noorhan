@extends('layouts.guest')
@section('title', 'Two-Factor Verification')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6" x-data="{ useRecovery: false }">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <h1 class="mt-4 text-xl font-bold text-center text-gray-900 dark:text-white">Two-Factor Verification</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 text-center">Enter the 6-digit code from your authenticator app.</p>

        <form method="POST" action="{{ route('two-factor.verify') }}" class="mt-6 space-y-4">
            @csrf
            <div x-show="!useRecovery">
                <input name="code" inputmode="numeric" autocomplete="one-time-code" autofocus placeholder="123456"
                       class="w-full px-3 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-center text-lg tracking-widest focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div x-show="useRecovery">
                <input name="recovery" placeholder="RECOVERY-CODE" class="w-full px-3 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-center font-mono focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            @error('code') <p class="text-sm text-red-500 text-center">{{ $message }}</p> @enderror

            <label class="flex items-center justify-center text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2">Trust this device for {{ (int) config('noorhan.auth.two_factor.remember_device_days', 7) }} days</span>
            </label>

            <button class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Verify & Continue</button>

            <button type="button" @click="useRecovery = !useRecovery" class="w-full text-center text-sm text-blue-600 dark:text-blue-400 hover:underline" x-text="useRecovery ? 'Use authenticator code' : 'Use a recovery code'"></button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
            @csrf
            <button class="text-xs text-gray-400 hover:underline">Cancel & sign out</button>
        </form>
    </div>
</div>
@endsection
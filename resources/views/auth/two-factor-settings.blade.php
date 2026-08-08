@extends('layouts.app')
@section('title', 'Two-Factor Authentication')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Two-Factor Authentication</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add an authenticator-app layer to your account.</p>
    </div>

    @if (session('status'))
        <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-sm text-green-600 dark:text-green-400">{{ session('status') }}</div>
    @endif

    @if ($enabled)
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold">Status</h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">ACTIVE</span>
                </div>
            </x-slot:header>
            <p class="text-sm text-gray-600 dark:text-gray-300">Two-factor authentication is protecting your account since {{ auth()->user()->two_factor_confirmed_at?->format('M d, Y') }}.</p>
            <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-4" onsubmit="return confirm('Disable two-factor authentication?')">
                @csrf
                <button class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">Disable 2FA</button>
            </form>
        </x-card>
    @elseif ($pendingSecret)
        <x-card>
            <x-slot:header><h3 class="font-semibold">Step 2 — Scan & Confirm</h3></x-slot:header>
            <div class="flex flex-col md:flex-row gap-6 items-start">
                <img src="{{ app(\App\Services\Auth\TotpService::class)->qrUrl($pendingSecret, auth()->user()->email) }}" alt="QR" class="w-52 h-52 rounded-lg border border-gray-200 dark:border-gray-700 bg-white p-2">
                <div class="flex-1 space-y-3">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Scan with Google Authenticator / 1Password, or enter this secret manually:</p>
                    <code class="block text-xs bg-gray-100 dark:bg-gray-700 rounded-lg p-2 break-all">{{ $pendingSecret }}</code>
                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-3">
                        @csrf
                        <input name="code" inputmode="numeric" autocomplete="one-time-code" placeholder="123456" maxlength="6"
                               class="w-40 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('code') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        <div><button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Confirm & Activate</button></div>
                    </form>
                </div>
            </div>
        </x-card>
    @else
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold">Status</h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">OFF</span>
                </div>
            </x-slot:header>
            <p class="text-sm text-gray-600 dark:text-gray-300">Protect your account with time-based one-time passwords.</p>
            <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-4">
                @csrf
                <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Enable 2FA</button>
            </form>
        </x-card>
    @endif

    @if ($recoveryCodes)
        <x-card>
            <x-slot:header><h3 class="font-semibold"> Save Your Recovery Codes</h3></x-slot:header>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Each code works once if you lose your authenticator. Shown only now.</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($recoveryCodes as $code)
                    <code class="text-sm bg-gray-100 dark:bg-gray-700 rounded-lg p-2 text-center font-mono">{{ $code }}</code>
                @endforeach
            </div>
        </x-card>
    @endif
</div>
@endsection
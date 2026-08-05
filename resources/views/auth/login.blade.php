@extends('layouts.guest')
@section('title', 'Sign In')

@section('content')
<div class="min-h-screen flex">
    {{-- Brand Panel --}}
    <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-900 text-white">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur flex items-center justify-center text-xl font-bold">N</div>
            <span class="text-xl font-semibold">{{ config('noorhan.name') }}</span>
        </div>
        <div>
            <h1 class="text-4xl font-bold leading-tight">Your Business<br>Operating System.</h1>
            <p class="mt-4 text-blue-100 max-w-md">Leads, quotations, suppliers, WhatsApp automation and AI insights — unified across every Noorhan Group division.</p>
        </div>
        <p class="text-sm text-blue-200">© {{ date('Y') }} Noorhan Group. All rights reserved.</p>
    </div>

    {{-- Form Panel --}}
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12" x-data="{ show: false }">
        <div class="w-full max-w-md">
            <div class="lg:hidden flex items-center space-x-2 mb-8">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">N</div>
                <span class="text-lg font-semibold">{{ config('noorhan.name') }}</span>
            </div>

            <h2 class="text-2xl font-bold">Welcome back</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sign in to your workspace to continue.</p>

            @if ($errors->any())
                <div class="mt-6 p-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-sm text-red-600 dark:text-red-400">
                    {{ $errors->first('email') }}
                </div>
            @endif

            @if (session('status'))
                <div class="mt-6 p-3 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-sm text-green-600 dark:text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email address</label>
                    <div class="relative mt-1.5">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                               class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
                               placeholder="you@noorhan.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <div class="relative mt-1.5">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                               class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
                               placeholder="••••••••">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <input type="checkbox" name="remember" value="1"
                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-800">
                        <span class="ml-2">Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Forgot password?</a>
                </div>

                <button type="submit"
                        class="w-full py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 active:scale-[.98] text-white text-sm font-semibold shadow-sm transition">
                    Sign In
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
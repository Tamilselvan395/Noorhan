@extends('layouts.app')
@section('title', 'Profile Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Profile Settings</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage your personal information and password.</p>
    </div>

    <livewire:profile.update-profile-form />
    <livewire:profile.update-password-form />
</div>
@endsection
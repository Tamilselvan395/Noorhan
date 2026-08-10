@extends('layouts.portal')
@section('title', 'Profile')

@section('content')
<div class="max-w-xl">
    <h1 class="text-2xl font-bold mb-6">My Profile</h1>
    <livewire:portal.profile-form />
</div>
@endsection
@extends('layouts.app')
@section('title', 'Companies')

@section('content')
<livewire:companies.company-index />
<livewire:companies.company-form />
@endsection
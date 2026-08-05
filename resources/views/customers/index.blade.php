@extends('layouts.app')
@section('title', 'Customers')

@section('content')
<livewire:customers.customer-index />
<livewire:customers.customer-form />
@endsection
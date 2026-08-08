@extends('layouts.app')
@section('title', 'Sales Orders')

@section('content')
<livewire:sales-orders.order-index />
<livewire:sales-orders.order-form />
@endsection
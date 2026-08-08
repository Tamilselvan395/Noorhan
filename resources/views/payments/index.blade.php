@extends('layouts.app')
@section('title', 'Payments')

@section('content')
<livewire:payments.payment-index />
<livewire:payments.payment-form />
@endsection
@extends('layouts.app')
@section('title', 'Invoices')

@section('content')
<livewire:invoices.invoice-index />
<livewire:payments.payment-form />
@endsection
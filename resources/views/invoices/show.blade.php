@extends('layouts.app')
@section('title', 'Invoice · '.$invoice->reference)

@section('content')
<livewire:invoices.invoice-show :invoice="$invoice" />
<livewire:payments.payment-form />
@endsection
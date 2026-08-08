@extends('layouts.app')
@section('title', 'Order · '.$order->reference)

@section('content')
<livewire:sales-orders.order-show :order="$order" />
@endsection
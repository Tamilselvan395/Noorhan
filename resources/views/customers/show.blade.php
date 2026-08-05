@extends('layouts.app')
@section('title', 'Customer · '.$customer->name)

@section('content')
<livewire:customers.customer-show :customer="$customer" />
<livewire:customers.customer-form />
@endsection
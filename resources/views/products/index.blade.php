@extends('layouts.app')
@section('title', 'Products')

@section('content')
<livewire:products.product-index />
<livewire:products.product-form />
@endsection
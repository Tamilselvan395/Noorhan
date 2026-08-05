@extends('layouts.app')
@section('title', 'Product · '.$product->name)

@section('content')
<livewire:products.product-show :product="$product" />
<livewire:products.product-form />
@endsection
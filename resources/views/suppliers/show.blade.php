@extends('layouts.app')
@section('title', 'Supplier · '.$supplier->name)

@section('content')
<livewire:suppliers.supplier-show :supplier="$supplier" />
<livewire:suppliers.supplier-form />
@endsection
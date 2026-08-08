@extends('layouts.app')
@section('title', 'Quotation Builder')

@section('content')
<livewire:quotations.quotation-builder :quotation="$quotation ?? null" />
@endsection
@extends('layouts.app')
@section('title', 'Quotation · '.$quotation->reference)

@section('content')
<livewire:quotations.quotation-show :quotation="$quotation" />
@endsection
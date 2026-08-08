@extends('layouts.app')
@section('title', 'Enquiry · '.$enquiry->reference)

@section('content')
<livewire:suppliers.enquiry-show :enquiry="$enquiry" />
@endsection
@extends('layouts.app')
@section('title', 'Company · '.$company->name)

@section('content')
<livewire:companies.company-show :company="$company" />
<livewire:companies.company-form />
<livewire:customers.customer-form />
@endsection
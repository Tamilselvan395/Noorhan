@extends('layouts.app')
@section('title', 'Lead · '.$lead->name)

@section('content')
<livewire:leads.lead-show :lead="$lead" />
<livewire:leads.lead-form />
@endsection
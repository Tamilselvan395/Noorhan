@extends('layouts.app')
@section('title', 'Campaign · '.$campaign->name)

@section('content')
<livewire:marketing.campaign-show :campaign="$campaign" />
@endsection
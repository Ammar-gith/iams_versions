@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Profile --}}
    <div class="row">
        <h1>Profile</h1>
    </div>
@endpush

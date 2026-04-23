@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <form method="POST" action="{{ route('password.change.request') }}">
        @csrf
        <button type="submit" class="btn btn-warning">Request Password Change</button>
    </form>
@endpush

@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Table --}}
    <div class="row custom-paddings">
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                    <h5 class="card-header text-primary">{{ $user->name }}</h5>
                    <a href="{{ url()->previous() }}" class="custom-primary-button">← Back</a>
                </div>
                <div class="menu-divider mb-4"></div>
                <div class="table-responsive text-nowrap card-body">

                </div>
            </div>
        </div>
    </div>
    {{-- ! / End Table --}}
@endpush

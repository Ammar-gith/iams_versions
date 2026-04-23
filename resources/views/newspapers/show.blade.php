@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Details --}}
    <div class="row">
        <div class="col-xxl">
            <div class="card mb-4" style="padding: 0;">
                {{-- Title (Header) --}}
                <div class="card-header-table">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                        <h5 class="h5-reset-margin h5-padding">{{ $newspaper->title }} Details</h5>
                    </div>
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                </div>

                {{-- Body --}}

            </div>
        </div>
    </div>
    {{--! / End Details --}}
@endpush

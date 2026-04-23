@extends('layouts.masterVertical')

{{-- Custom CSS --}}
@push('style')
    <style>
        .form-header {
            justify-content: space-between;
            align-items: center;
            background-color: #397F67;
            padding: 1rem 2rem;
            border-radius: 18px 18px 0 0;
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                {{-- Title (Header) --}}
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="h5-reset-margin">{{ $file_image->collection_name }}</h5>
                    <a href="{{ $previousUrl }}"
                        class="text-decoration-none">
                        <i class='bx bx-x bx-modal-icons'></i>
                    </a>
                </div>
                {{-- Body --}}
                <div class="card-body">
                    {{--  View file  --}}
                    <div class="row mb-3  ">
                        <div class="col-sm-6">
                            <img src="{{ $file_image->getUrl() }}" alt="file-image" class="img-fluid">
                        </div>
                        <div class="col-sm-6">
                            <label style="font-weight: 700;" class="form-label m-3 text-danger" for="">File Details</label>
                            <ol>
                                <li>Collection Name: {{ $file_image->collection_name }}</li>
                                <li>Name: {{ $file_image->name }}</li>
                                <li>Mime Type: {{ $file_image->mime_type }}</li>
                                <li>Size: {{ $file_image->human_readable_size }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush

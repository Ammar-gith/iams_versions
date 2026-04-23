@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('adWorthParameter.store') }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">New Ad Worth Parameter</h5>
                </div>

                <div class="form-padding">
                    {{-- Ad Worth Parameter Range --}}
                    <div class="row mb-3 g-3">
                        <div class="col-sm-4">
                            <label class="form-label-x col-form-label" for="range">Ad Worth Range</label>
                            <input type="text" name="range" id="alignment-range" class="form-control"
                                placeholder="Enter ad worth range..." />
                        </div>
                        @error('range')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- {{ Ad Worth Parameter Formula }} --}}
                    <div class="row mb-3 g-3">
                        <div class="col-sm-4">
                            <label class="form-label-x col-form-label" for="formula">Distribution Formula</label>
                            <input type="text" name="formula" id="alignment-formula" class="form-control"
                                placeholder="Enter ad worth distribution formula..." />
                        </div>
                        @error('formula')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Save</button>

                    {{-- Cancel --}}
                    <a href="{{ route('master.adWorthParameter.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

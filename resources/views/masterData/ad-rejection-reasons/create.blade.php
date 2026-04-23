@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('adRejectionReason.store') }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">New Ad Rejection Reason</h5>
                </div>

                <div class="form-padding">
                    {{-- Reason Description --}}
                    <div class="row mb-3 g-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <label class="form-label-x col-form-label" for="description">Ad Reason Description</label>
                            <textarea type="text" name="description" id="alignment-description" rows="4" class="form-control"
                                placeholder="Enter ad rejection description..." ></textarea>
                        </div>
                        @error('description')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Save</button>

                    {{-- Cancel --}}
                    <a href="{{ route('master.adRejectionReason.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

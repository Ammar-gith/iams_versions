@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('adRejectionReason.update', $ad_rejection_reason->id) }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Update Ad Rejection Reason Category</h5>
                </div>

                <div class="form-padding">
                    {{-- Permission Name --}}
                    <div class="row mb-3 g-3">
                        <div class="col-sm-12">
                            <label class="form-label-x" for="description">Reason Description</label>
                            <textarea name="description" class="form-control" id="collapsible-address" rows="4" required>{{ $ad_rejection_reason->description }}</textarea>
                        </div>
                        @error('description')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Update</button>

                    {{-- Cancel --}}
                    <a href="{{ route('master.adRejectionReason.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('adWorthParameter.update', $ad_worth_parameter->id) }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Update Ad Worth Parameter</h5>
                </div>

                <div class="form-padding">
                    {{-- Ad Worth Parameter range --}}
                    <div class="row mb-3 g-3">
                        <div class="col-sm-4">
                            <label class="form-label-x" for="range">Ad Worth Range</label>
                            <input type="text" name="range" id="alignment-range" class="form-control" value="{{ $ad_worth_parameter->range }}" />
                        </div>
                    </div>
                    @error('range')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                    {{-- Ad Worth Parameter Formula --}}
                    <div class="row mb-3 g-3">
                        <div class="col-sm-4">
                            <label class="form-label-x" for="formula">Distribution Formula</label>
                            <input type="text" name="formula" id="alignment-formula" class="form-control" value="{{ $ad_worth_parameter->formula }}" />
                        </div>
                        @error('formula')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Update</button>

                    {{-- Cancel --}}
                    <a href="{{ route('master.adWorthParameter.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

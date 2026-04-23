@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('publisherType.update', $publisherType->id) }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Update Publisher Type</h5>
                </div>

                <div class="form-padding">
                    {{-- Publisher Type Code --}}
                    <div class="row mb-3 mx-5 ">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="code">Code</label>
                            <input type="text" name="code" id="alignment-username" class="form-control"
                                value="{{ $publisherType->code }}" />
                        </div>
                        @error('code')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Publisher Type Description --}}
                    <div class="row mb-3 mx-5  ">
                        <div class="col-sm-6">
                            <label class=" col-form-label text-sm-end" for="description">Description</label>
                            <input type="text" name="description" id="alignment-username" class="form-control"
                                value="{{ $publisherType->description }}" />
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
                    <a href="{{ route('master.publisherType.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

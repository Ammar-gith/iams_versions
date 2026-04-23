@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('district.store') }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">New District</h5>
                </div>

                <div class="form-padding">
                    {{-- District Name --}}
                    <div class="row mb-3 mx-5  ">
                        <div class="col-sm-6">
                            <label class=" col-form-label text-sm-end" for="name">Name</label>
                            <input type="text" name="name" id="alignment-name" class="form-control" required
                                placeholder="Enter district name..." />
                        </div>
                        @error('name')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Province --}}
                    <div class="row mb-3 mx-5  ">
                        <label class=" col-form-label text-sm-start" for="name">Provinces</label>
                        <div class="col-sm-12">
                            @foreach ($provinces as $province)
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="province_{{ $province->id }}" name="province_id"
                                        value="{{ $province->id }}" class="form-check-input ">
                                    <label class="form-check-label " for="province_{{ $province->id }}">
                                        {{ $province->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('province_id')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
 
                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Save</button>

                    {{-- Cancel --}}
                    <a href="{{ route('master.district.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

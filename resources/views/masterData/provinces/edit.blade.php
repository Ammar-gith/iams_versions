@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('province.update', $province->id) }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Update Province</h5>
                </div>

                <div class="form-padding">
                    <div class="row mb-3 mx-5 ">
                        {{-- Province Code --}}
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="code">Code</label>
                            <input type="text" name="code" id="alignment-code" class="form-control" value="{{ $province->code }}" />
                        </div>

                        {{-- Province name --}}
                        <div class="col-sm-6">
                            <label class=" col-form-label text-sm-end" for="name">Name</label>
                            <input type="text" name="name" id="alignment-name" class="form-control" value="{{ $province->name }}" />
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Update</button>

                    {{-- Cancel --}}
                    <a href="{{ route('master.province.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

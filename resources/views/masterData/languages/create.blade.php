@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('language.store') }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">New Language</h5>
                </div>

                <div class="form-padding">
                    {{-- Language --}}
                    <div class="row mb-3 mx-5  ">
                        <div class="col-sm-6">
                            <label class=" col-form-label text-sm-end" for="name">Language Name</label>
                            <input type="text" name="name" id="alignment-name" class="form-control" required
                                placeholder="Enter language name..." />
                            {{-- <label class=" col-form-label text-sm-end" for="code">Language Code</label>
                            <input type="text" name="code" id="alignment-code" class="form-control" required
                                placeholder="Enter language code..." /> --}}
                        </div>
                        @error('code')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Save</button>

                    {{-- Cancel --}}
                    <a href="{{ route('master.language.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

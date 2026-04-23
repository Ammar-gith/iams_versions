@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('newspaperCategory.store') }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Newspaper Category Form</h5>
                </div>

                <div class="form-padding">
                    {{-- Permission Name --}}
                    <div class="row mb-3 g-3">
                        <div class="col-sm-4">
                            <label class="form-label-x col-form-label" for="title">Newspaper Category Name</label>
                            <input type="text" name="title" id="alignment-title" class="form-control"
                                placeholder="Enter category name..." />
                        </div>
                        @error('title')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Save</button>

                    {{-- Cancel --}}
                    <a href="{{ route('newspaper.newspaperCategory.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('userManagement.role.update', $role->id) }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Update Role</h5>
                </div>

                {{-- Role Name --}}
                <div class="row mb-3 mx-5  ">
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="name">Name</label>
                        <input type="text" name="name" id="alignment-name" class="form-control" required
                            value="{{ $role->name }}" />
                    </div>
                    @error('name')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Update</button>

                    {{-- Cancel --}}
                    <a href="{{ route('userManagement.role.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

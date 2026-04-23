@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('userManagement.role.assignPermission', $role->id) }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Assign Permissions to Role: {{ $role->name }}</h5>
                </div>

                {{-- Permission Name --}}
                <div class="row mb-3 mx-5  ">
                    <label class="col-form-label text-sm-start fw-bold fs-5 mb-3" for="permission">Permissions</label>
                    @foreach ($permissions as $permission)
                        <div class="col-sm-4 mb-3">
                            <input class="form-check-input " type="checkbox" name="permissions[]"
                                id="alignment-permission" value="{{ $permission->name }}"
                                {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }} />
                            {{ $permission->name }}
                        </div>
                    @endforeach

                    @error('"permission')
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


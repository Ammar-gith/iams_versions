@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row custom-paddings">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('userManagement.user.update', $user->id) }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Update User</h5>
                </div>

                <div class="form-padding">
                    {{-- Name --}}
                    <div class="row mb-3 mx-5  ">
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="name">Name</label>
                            <input type="text" name="name" id="alignment-name" class="form-control"
                                value="{{ $user->name }}" />
                        </div>
                        @error('name')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- username --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="username">Username</label>
                            <input type="text" name="username" id="alignment-username" class="form-control"
                                value="{{ $user->username }}" />
                        </div>
                        @error('username')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User email --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="email">Email</label>
                            <input type="text" name="email" id="alignment-email" class="form-control"
                                value="{{ $user->email }}" />
                        </div>
                        @error('email')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- User designation --}}
                    <div class="row mb-3 mx-5  ">
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="designation">Designation</label>
                            <input type="text" name="designation" id="alignment-designation" class="form-control"
                                value="{{ $user->designation }}" />
                        </div>
                        @error('designation')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User password --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="password">Password</label>
                            <input type="password" name="password" id="alignment-password" class="form-control"
                                value="{{ $user->password }}" />
                        </div>
                        @error('password')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User image --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="image">Upload Signature</label>
                            <input type="file" name="image" id="alignment-image" class="form-control"
                                value="{{ $user->image }}" accept="image/*" />
                        </div>

                        @error('image')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- User department --}}
                    <div class="row mb-3 mx-5  ">
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="department_id">Department</label>
                            <select id="department_id" name="department_id" class="select2 form-select"
                                data-allow-clear="true">
                                <option value="">Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ $selected_department == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('department_id')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User office --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="office_id">Office</label>
                            <select name="office_id" id="office_id" class="select2 form-select" data-allow-clear="true">
                                <option value="">Select office</option>
                                @foreach ($offices as $office)
                                    <option value="{{ $office->id }}"
                                        {{ $selected_office == $office->id ? 'selected' : '' }}>
                                        {{ $office->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('office_id')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Newspaper --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="newspaper_id">Newspaper</label>
                            <select name="newspaper" id="alignment-newspaper" class="select2 form-select">
                                <option value="">Select newspaper</option>
                                @foreach ($newspapers as $newspaper)
                                    <option value="{{ $newspaper->id }}"
                                        {{ $selected_newspaper == $newspaper->id ? 'selected' : '' }}>
                                        {{ $newspaper->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('newspaper')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row mb-3 mx-5  ">
                        {{-- Adv agency --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="newspaper_id">Adv. Agency</label>
                            <select name="newspaper" id="alignment-newspaper" class="select2 form-select">
                                <option value="">Select adv. agency</option>
                                @foreach ($adv_agencies as $adv_agency)
                                    <option value="{{ $adv_agency->id }}"
                                        {{ $selected_adv_agency == $adv_agency->id ? 'selected' : '' }}>
                                        {{ $adv_agency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('adv_agency')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User activation date --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="activation_date">Activation Date</label>
                            <input type="date" name="activation_date" id="alignment-activation_date"
                                class="form-control" />
                        </div>
                        @error('activation_date')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User deactivation_date --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="deactivation_date">Deactivation Date</label>
                            <input type="date" name="deactivation_date" id="alignment-deactivation_date"
                                class="form-control" />
                        </div>
                        @error('deactivation_date')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row mb-3 mx-5  ">
                        {{-- Status --}}
                        <label class="col-form-label text-sm-start" for="name">Status</label>
                        <div class="col-sm-6">
                            @foreach ($statuses as $status)
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="status_{{ $status->id }}" name="status_id"
                                        value="{{ $status->id }}" class="form-check-input"
                                        {{ $selected_status == $status->id ? 'checked' : '' }}>
                                    <label class="form-check-label " for="status_{{ $status->id }}">
                                        {{ $status->title }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('status_id')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User Role --}}
                        <div class="col-sm-6">
                            <label class=" col-form-label text-sm-end" for="role">Role</label>
                            <select name="role[]" id="alignment-role" class="form-control" multiple>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}"
                                        {{ in_array($role, $userRole) ? 'selected' : '' }}>{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('password')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Update</button>

                    {{-- Cancel --}}
                    <a href="{{ route('userManagement.user.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

@push('scripts')
@endpush

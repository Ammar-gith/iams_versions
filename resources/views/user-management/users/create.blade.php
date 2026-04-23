@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row custom-paddings">
        <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('userManagement.user.store') }}" method="POST" class="card-body"
                enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">New User</h5>
                </div>

                <div class="form-padding">
                    {{-- Name --}}
                    <div class="row mb-3 g-3">
                        <div class="col-sm-4">
                            <label class="form-label-x" for="name">Name</label>
                            <input type="text" name="name" id="alignment-name" class="form-control"
                                placeholder="Enter name..." />
                        </div>
                        @error('name')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- username --}}
                        <div class="col-sm-4">
                            <label class="form-label-x" for="username">Username</label>
                            <input type="text" name="username" id="alignment-username" class="form-control"
                                placeholder="Enter username..." />
                        </div>
                        @error('username')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User email --}}
                        <div class="col-sm-4">
                            <label class="form-label-x" for="email">Email</label>
                            <input type="text" name="email" id="alignment-email" class="form-control"
                                placeholder="Enter email..." />
                        </div>
                        @error('email')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- User designation --}}
                    <div class="row mb-3 g-3">
                        <div class="col-sm-4">
                            <label class="form-label-x" for="designation">Designation</label>
                            <input type="text" name="designation" id="alignment-designation" class="form-control"
                                placeholder="Enter designation..." />
                        </div>
                        @error('designation')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User password --}}
                        <div class="col-sm-4">
                            <label class="form-label-x" for="password">Password</label>
                            <input type="password" name="password" id="alignment-password" class="form-control"
                                placeholder="Enter password..." />
                        </div>
                        @error('password')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User image --}}
                        <div class="col-sm-4">
                            <label class="form-label-x" for="image">Upload Signature</label>
                            <input type="file" name="image" id="alignment-image" class="form-control"
                                placeholder="upload signature" accept="image/*" />
                        </div>
                        @error('image')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- User Department & Office --}}
                    <div class="row mb-3 g-3">
                        {{-- Department --}}
                        <div class="col-md-4">
                            <label class="form-label-x col-form-label" for="department">Department</label>
                            <select id="department" name="department_id" class="select2 form-select"
                                data-allow-clear="true">
                                <option value="">Select Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger small mt-1" id="department_error"></div>
                        </div>
                        @error('department_id')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- ffice --}}
                        <div class="col-md-4">
                            <label class="form-label-x col-form-label" for="office">Office</label>
                            <select id="office" name="office_id" class="select2 form-select" data-allow-clear="true" >
                                <option value="">Select Office</option>
                            </select>
                            <div class="text-danger small mt-1" id="office_error"></div>
                        </div>
                        @error('office_id')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Newspaper --}}
                        <div class="col-sm-4">
                            <label class="form-label-x" for="newspaper_id">Newspaper</label>
                            <select name="newspaper_id" id="alignment-newspaper" class="select2 form-select"
                                data-allow-clear="true">
                                <option value="">Select newspaper</option>
                                @foreach ($newspapers as $newspaper)
                                    <option value="{{ $newspaper->id }}">{{ $newspaper->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('newspaper')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row mb-3 g-3">
                        {{-- Adv agency --}}
                        <div class="col-sm-4">
                            <label class="form-label-x" for="advAgency_id">Adv Agency</label>
                            <select name="adv_agency_id" id="alignment-adv_agency" class="select2 form-select"
                                data-allow-clear="true">
                                <option value="" >Select adv agency</option>
                                @foreach ($advAgencies as $advAgency)
                                    <option value="{{ $advAgency->id }}">{{ $advAgency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('adv_agency')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User activation date --}}
                        <div class="col-sm-4">
                            <label class="form-label-x" for="activation_date">Activation Date</label>
                            <input type="date" name="activation_date" id="alignment-activation_date"
                                class="form-control" />
                        </div>
                        @error('activation_date')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User deactivation_date --}}
                        <div class="col-sm-4">
                            <label class="form-label-x" for="deactivation_date">Deactivation Date</label>
                            <input type="date" name="deactivation_date" id="alignment-deactivation_date"
                                class="form-control" />
                        </div>
                        @error('deactivation_date')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- Status --}}
                        <div class="col-sm-6 d-flex flex-column" style="margin-top: 0;">
                            <label class="form-label-x" for="name">Status</label>
                            @foreach ($user_statuses as $key => $value)
                                <div class="form-check form-check-inline ms-2">
                                    <input type="radio" id="user_status_{{ $key }}" name="status_id"
                                        value="{{ $key }}" class="form-check-input ">
                                    <label class="form-check-label " for="user_status_{{ $key }}">
                                        {{ $value }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('status_id')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- User Role --}}
                        <div class="col-sm-6" style="margin-top: 0;">
                            <label class="form-label-x" for="role">Role</label>
                            <select name="role[]" id="alignment-role" class="select2 form-select"
                                data-allow-clear="true" multiple>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
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
                    <button type="submit" class="custom-primary-button">Save User</button>

                    {{-- Cancel --}}
                    <a href="{{ route('userManagement.user.index') }}" type="button"
                        class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

@push('scripts')
    {{-- Office Selection based on Selected Department --}}
    <script>
        const userOfficeId = @json(auth()->user()->office_id);
        $(document).ready(function() {
            $('#department').change(function() {
                var departmentId = $(this).val();
                // console.log('The department ID is :', departmentId);
                if (departmentId) {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('advertisements.getOffices') }}",
                        data: {
                            department_id: departmentId
                        },
                        success: function(response) {
                            console.log('The response is :', response);
                            $('#office').empty();
                            $('#office').append('<option></option>');
                            $.each(response, function(key, value) {
                                var isSelected = (value.id == userOfficeId) ?
                                    'selected' : '';
                                $('#office').append('<option value="' + value.id +
                                    '" ' + isSelected + '>' + value.ddo_name +
                                    '</option>');

                                // $('#office').append('<option value="' + value.id +
                                //     '">' + value.name + '</option>');
                            });
                        }
                    });
                }
            });
            // If department is already selected (like from logged-in user), trigger change to load offices
            var selectedDepartment = $('#department').val();
            if (selectedDepartment) {
                $('#department').trigger('change');
            }
        });
    </script>
@endpush

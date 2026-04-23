@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row mb-4">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">

            {{-- Office Form --}}
            <form id="officeForm" action="{{ route('office.store') }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">New Office</h5>
                </div>

                <div class="card-padding mt-3 mb-3">
                    <div class="row mb-3 mx-5  ">
                        {{-- Office Name --}}
                        <div class="col-sm-6 mb-3">
                            <label for="ddo_name" class="form-label col-form-label">Office Name</label>
                            <input type="text" name="ddo_name" id="ddo_name" class="form-control" placeholder="Enter office name..." />
                            <div class="text-danger small mt-1" id="ddo_name_error"></div>
                        </div>

                        {{-- DDO Code --}}
                        <div class="col-sm-6 mb-3">
                            <label for="ddo_code" class="form-label col-form-label">DDO Code</label>
                            <input type="text" name="ddo_code" id="ddo_code" class="form-control" placeholder="Enter office DDO code..." />
                            <div class="text-danger small mt-1" id="ddo_code_error"></div>
                        </div>
                    </div>

                    {{-- Department, District & Office Category --}}
                    <div class="row mb-3 mx-5  ">

                        {{-- Department --}}
                        <div class="col-sm-6">
                            <label for="department_id" class="form-label col-form-label">Department</label>
                            <select id="department_id" name="department_id" class="form-select">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger small mt-1" id="department_error"></div>
                        </div>

                        {{-- District --}}
                        <div class="col-sm-3">
                            <label for="district_id" class="form-label col-form-label">District</label>
                            <select id="district_id" name="district_id" class="form-select">
                                <option value="">Select District</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger small mt-1" id="district_error"></div>
                        </div>

                        {{-- Office Category --}}
                        <div class="col-sm-3">
                            <label for="office_category_id" class="form-label col-form-label">Office Category</label>
                            <select id="office_category_id" name="office_category_id" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($officeCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger small mt-1" id="office_category_error"></div>
                        </div>

                    </div>

                    {{-- Opening Dues and Status --}}
                    <div class="row mb-3 mx-5  ">

                        {{-- Opening Dues (only numbers >= 0) --}}
                        <div class="col-sm-6 mb-3">
                            <label class="col-form-label text-sm-end" for="opening_dues">Opening Dues</label>
                            <input type="number" name="opening_dues" id="opening_dues" class="form-control" placeholder="Enter opening dues..." />
                            <div class="text-danger small mt-1" id="opening_dues_error"></div>
                        </div>

                        {{-- Office Status --}}
                        <div class="col-sm-6">
                            <label class="form-label col-form-label">Status</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                        type="radio" name="status" id="status_active" value="1"
                                        {{ old('status', 1) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_active">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                        type="radio" name="status" id="status_inactive" value="0"
                                        {{ old('status', 1) == 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_inactive">Inactive</label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Save</button>

                    {{-- Cancel --}}
                    <a href="{{ route('master.office.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

@push('scripts')
    <script>
        document.getElementById("officeForm").addEventListener("submit", function(event) {
            let valid = true;
            let firstInvalidField = null;

            // Clear previous errors
            document.querySelectorAll(".text-danger").forEach(el => el.textContent = "");

            // Office Name: letters only
            const ddoName = document.getElementById("ddo_name");
            if (!/^[A-Za-z\s]+$/.test(ddoName.value.trim())) {
                document.getElementById("ddo_name_error").textContent =
                    "Office Name must be letters only (e.g., ABC Department)";
                valid = false;
                if (!firstInvalidField) firstInvalidField = ddoName;
            }

            // DDO Code: uppercase letters + digits
            const ddoCode = document.getElementById("ddo_code");
            if (!/^[A-Z]+\d+$/.test(ddoCode.value.trim())) {
                document.getElementById("ddo_code_error").textContent =
                    "DDO Code must be uppercase letters followed by digits (e.g., ABC123)";
                valid = false;
                if (!firstInvalidField) firstInvalidField = ddoCode;
            }

            // Department required
            const department = document.getElementById("department_id");
            if (department.value === "") {
                document.getElementById("department_error").textContent = "Please select Department";
                valid = false;
                if (!firstInvalidField) firstInvalidField = department;
            }

            // District required
            const district = document.getElementById("district_id");
            if (district.value === "") {
                document.getElementById("district_error").textContent = "Please select District";
                valid = false;
                if (!firstInvalidField) firstInvalidField = district;
            }

            // Office Category required
            const category = document.getElementById("office_category_id");
            if (category.value === "") {
                document.getElementById("office_category_error").textContent = "Please select Office Category";
                valid = false;
                if (!firstInvalidField) firstInvalidField = category;
            }

            // Opening Dues: must be >= 0
            const dues = document.getElementById("opening_dues");
            if (dues.value.trim() === "" || isNaN(dues.value) || Number(dues.value) < 0) {
                document.getElementById("opening_dues_error").textContent =
                    "Opening dues must be 0 or greater (only numbers >= 0)";
                valid = false;
                if (!firstInvalidField) firstInvalidField = dues;
            }

            // Stop submission if invalid
            if (!valid) {
                event.preventDefault();
                firstInvalidField.focus();
            }
        });
    </script>
@endpush

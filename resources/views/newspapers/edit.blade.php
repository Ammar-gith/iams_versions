@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('newspaper.update', $newspaper->id) }}" method="POST" class="card-body"
                enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin h5-padding">Update {{ $newspaper->title }} Newspaper</h5>
                </div>

                {{-- Newspaper title --}}
                <div class="row mb-3 mx-5">
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="title">Title</label>
                        <input type="text" name="title" id="alignment-title" class="form-control" required
                            placeholder="Enter Ad category title..." />
                    </div>
                    @error('title')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    {{-- Newspaper Language --}}
                    <div class="col-md-6">
                        <label class=" col-form-label text-sm-end" for="language">Languages</label>
                        <select id="language" name="language_id" class="select2 form-select" required
                            data-allow-clear="true">
                            <option value="">Select Language</option>
                            @foreach ($languages as $language)
                                <option value="{{ $language->id }}">{{ $language->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('language_id')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row mb-3 mx-5  ">
                    {{-- Newspaper Periodicity --}}
                    <div class="col-md-6">
                        <label class=" col-form-label text-sm-end" for="periodicity">Periodicity</label>
                        <select id="periodicity" name="periodicity_id" class="select2 form-select" required
                            data-allow-clear="true">
                            <option value="">Select Periodicity</option>
                            @foreach ($newspaper_periodicities as $newspaper_periodicity)
                                <option value="{{ $newspaper_periodicity->id }}">{{ $newspaper_periodicity->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('periodicity_id')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    {{-- Newspaper Category --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="category">Category</label>
                        <select id="category" name="category_id" class="select2 form-select" required
                            data-allow-clear="true">
                            <option value="">Select Category</option>
                            @foreach ($newspaper_categories as $newspaper_category)
                                <option value="{{ $newspaper_category->id }}">{{ $newspaper_category->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('category_id')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row mb-3 mx-5  ">
                    {{-- Newspaper Province --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="province">Province</label>
                        <select id="province" name="province_id" class="select2 form-select" required
                            data-allow-clear="true">
                            <option value="">Select province</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('province_id')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    {{-- Newspaper district --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="district">District</label>
                        <select id="district" name="district_id" class="select2 form-select" data-allow-clear="true">
                            <option value="">Select district</option>
                        </select>
                    </div>
                    @error('district_id')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row mb-3 mx-5  ">
                    {{-- Is Combined --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="is-combined">Is Combined</label>
                        <select id="is_combined" name="is_combined" class="select2 form-select" required
                            data-allow-clear="true">
                            <option value="">Select Value</option>
                            @foreach ($is_combined as $key => $value)
                                <option value="{{ $key }}">{{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('is_combined')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    {{-- Newspaper Circulation --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="circulation">Circulation</label>
                        <input type="text" name="circulation" id="alignment-circulation" class="form-control"
                            requiredplaceholder="Enter Ad category title..." />
                    </div>
                    @error('circulation')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row mb-3 mx-5  ">
                    {{-- Registration Date --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="registration_date">Registration Date</label>
                        <input type="date" name="registration_date" id="alignment-registration_date" class="form-control"
                            requiredplaceholder="Enter Ad category title..." />
                    </div>
                    @error('registration_date')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    {{-- Rate Efc. Date --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="rate_eff_date">Rate Efc. Date</label>
                        <input type="date" name="rate_eff_date" id="alignment-rate_eff_date" class="form-control"
                            required placeholder="Enter Ad category title..." />
                    </div>
                    @error('rate_eff_date')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row mb-3 mx-5  ">
                    {{-- KAPRA Tax --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="register_kapra">KAPRA Tax</label>
                        <select id="register_kapra" name="register_with_kapra" class="select2 form-select" required
                            data-allow-clear="true">
                            <option value="">Select Value</option>
                            @foreach ($kpraReg as $key => $value)
                                <option value="{{ $key }}">{{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('register_kapra')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    {{-- Status --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="status">Status</label>
                        <select id="status" name="status" class="select2 form-select" required
                            data-allow-clear="true">
                            <option value="">Select Value</option>
                            @foreach ($statuses as $key => $value)
                                <option value="{{ $key }}">{{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row mb-3 mx-5  ">
                    {{-- Newspaper Rate --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="NP_rate">Rate</label>
                        <input type="text" name="NP_rate" id="alignment-NP_rate" class="form-control" required
                            placeholder="Enter Ad category title..." />
                    </div>
                    @error('NP_rate')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                    {{-- Newspaper Opening balance --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="opening_balance">Opening Balance</label>
                        <input type="text" name="opening_balance" id="alignment-opening_balance" class="form-control"
                            required placeholder="Enter Ad category title..." />
                    </div>
                    @error('balance')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- =========================== Contact Details =========================== --}}

                <div class="row mt-5 mx-5">
                    <h3>Contact Details</h3>
                </div>
                <div class="row mb-3 mx-5  ">
                    {{-- Phone number --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="phone_no">Phone Number</label>
                        <input type="text" name="phone_no" id="alignment-phone_no" class="form-control" required
                            placeholder="Enter phone number..." />
                    </div>
                    @error('phone_no')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    {{-- Email --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="email">Email</label>
                        <input type="email" name="email" id="alignment-email" class="form-control" required
                            placeholder="Enter email address" />
                    </div>
                    @error('email')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row mb-3 mx-5  ">
                    {{-- Fax --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="fax">Fax</label>
                        <input type="text" name="fax" id="alignment-fax" class="form-control" required
                            placeholder="Enter fax number..." />
                    </div>
                    @error('fax')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    {{-- Website --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="website">Website</label>
                        <input type="text" name="website" id="alignment-website" class="form-control" required
                            placeholder="Enter website URL..." />
                    </div>
                    @error('website')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row mb-3 mx-5  ">
                    {{-- Contact Person Name --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="fp_name">Contact Person Name</label>
                        <input type="text" name="fp_name" id="alignment-fp_name" class="form-control" required
                            placeholder="Enter name..." />
                    </div>
                    @error('fp_name')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    {{-- Contact Person Cell --}}
                    <div class="col-sm-6">
                        <label class=" col-form-label text-sm-end" for="cell">Contact Person Cell</label>
                        <input type="text" name="cell_no" id="alignment-cell" class="form-control" required
                            placeholder="Enter cell number..." />
                    </div>
                    @error('cell_no')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Update</button>

                    {{-- Cancel --}}
                    <a href="{{ route('newspaper.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    </div>
    {{-- ! / End Form --}}
@endpush

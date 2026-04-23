@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <!--Form -->
    <div class="row">
        <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('advAgency.store') }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">New Advertisement Agency</h5>
                </div>

                <div class="form-padding">
                    {{-- Name --}}
                    <div class="row mb-3 mx-2  ">
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="name">Name</label>
                            <input type="text" name="name" id="alignment-name" class="form-control" required
                                placeholder="Enter agency name..." />
                        </div>
                        @error('name')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Registration date --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="registration_date">Registration Date</label>
                            <input type="date" name="registration_date" id="alignment-registration_date"
                                class="form-control" required placeholder="Enter registration date..." />
                        </div>
                        @error('registration_date')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Registered with KPRA --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="registered_with_kpra">Registered with
                                KPRA?</label>
                            <select name="registered_with_kpra" id="registered_with_kpra" class="form-select">
                                <option value="">Select option</option>
                                <option value="1">Registered</option>
                                <option value="0">Not Registered</option>
                            </select>
                        </div>
                        @error('registered_with_kpra')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- website --}}
                    <div class="row mb-3 mx-2  ">
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="website">Website</label>
                            <input type="text" name="website" id="alignment-website" class="form-control" required
                                placeholder="Enter agency website..." />
                        </div>
                        @error('website')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- profile_pba --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="profile_pba">Profile (PBA)</label>
                            <input type="text" name="profile_pba" id="alignment-profile_pba" class="form-control"
                                required placeholder="Enter profile_pba..." />
                        </div>
                        @error('profile_pba')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Status --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="status_id"> Status
                            </label>
                            <select name="status_id" id="status_id" class="form-select">
                                <option value="">Select status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('status_id')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <hr>
                    <h5 class="fw-normal mx-2">2. Local Office</h5>
                    {{-- Phone local --}}
                    <div class="row mb-3 mx-2  ">
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="phone">Phone</label>
                            <input type="text" name="phone_local" id="alignment-phone" class="form-control" required
                                placeholder="Enter agency phone..." />
                        </div>
                        @error('phone_local')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Email local --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="email">Email</label>
                            <input type="email" name="email_local" id="alignment-email" class="form-control" required
                                placeholder="Enter registration date..." />
                        </div>
                        @error('email_local')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Fax local --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="fax">Fax</label>
                            <input type="text" name="fax_local" id="alignment-fax" class="form-control" required
                                placeholder="Enter fax..." />
                        </div>
                        @error('fax_local')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row mb-3 mx-2  ">
                        {{-- Mailing address local --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="mailing_address_local">Mailing Address</label>
                            <input type="text" name="mailing_address_local" id="alignment-mailing_address_local"
                                class="form-control" required placeholder="Enter agency mailing address..." />
                        </div>
                        @error('mailing_address_local')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Contact person name local --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="person_name_local">Contact person name</label>
                            <input type="text" name="person_name_local" id="alignment-person_name_local"
                                class="form-control" required placeholder="Enter contact person name..." />
                        </div>
                        @error('person_name_local')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Contact person cell --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="person_cell_local">Contact person cell</label>
                            <input type="text" name="person_cell_local" id="alignment-person_cell_local"
                                class="form-control" required placeholder="Enter person cell..." />
                        </div>
                        @error('person_cell_local')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <hr>
                    <h5 class="fw-normal mx-2">3. HQ Office</h5>
                    {{-- Phone HQ --}}
                    <div class="row mb-3 mx-2  ">
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="phone_hq">Phone</label>
                            <input type="text" name="phone_hq" id="alignment-phone_hq" class="form-control" required
                                placeholder="Enter agency phone..." />
                        </div>
                        @error('phone_hq')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Email HQ --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="email_hq">Email</label>
                            <input type="email" name="email_hq" id="alignment-email_hq" class="form-control" required
                                placeholder="Enter registration date..." />
                        </div>
                        @error('email_hq')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Fax hq --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="fax_hq">Fax</label>
                            <input type="text" name="fax_hq" id="alignment-fax_hq" class="form-control" required
                                placeholder="Enter fax..." />
                        </div>
                        @error('fax_hq')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row mb-3 mx-2  ">
                        {{-- Mailing address hq --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="mailing_address_hq">Mailing Address</label>
                            <input type="text" name="mailing_address_hq" id="alignment-mailing_address_hq"
                                class="form-control" required placeholder="Enter agency mailing address..." />
                        </div>
                        @error('mailing_address_hq')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Contact person name HQ --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="person_name_hq">Contact person name</label>
                            <input type="text" name="person_name_hq" id="alignment-person_name_hq"
                                class="form-control" required placeholder="Enter contact person name..." />
                        </div>
                        @error('person_name_hq')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror

                        {{-- Contact person cell HQ --}}
                        <div class="col-sm-4">
                            <label class=" col-form-label text-sm-end" for="person_cell_hq">Contact person cell</label>
                            <input type="text" name="person_cell_hq" id="alignment-person_cell_hq"
                                class="form-control" required placeholder="Enter person_cell..." />
                        </div>
                        @error('person_cell_hq')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Save</button>

                    {{-- Cancel --}}
                    <a href="{{ route('advAgency.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

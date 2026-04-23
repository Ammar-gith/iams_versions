@extends('layouts.masterVertical')
@push('style')
    <style>
        .upload-box {
            border: 1px solid #28a745;
            border-radius: 5px;
            padding: 35px;
            text-align: center;
            cursor: pointer;
            background-color: #f8fff9;
            transition: all 0.3s ease;
            position: relative;
        }

        /* Hover & Dragover */
        .upload-box:hover {
            background-color: #ecfff1;
            border-color: #1e7e34;
        }

        .upload-box.dragover {
            background-color: #d4edda;
            border-color: #155724;
            transform: scale(1.02);
        }

        /* Placeholder */
        .upload-placeholder {
            transition: 0.3s;
        }

        .upload-box.has-image .upload-placeholder {
            display: none;
        }

        /* SVG Dropzone Icon */
        .dz-icon {
            display: block;
            margin-bottom: 12px;
        }

        .dz-icon svg {
            width: 38px;
            height: 38px;
        }

        /* Placeholder Text */
        .upload-placeholder p {
            color: #666666;
            font-weight: 400;
            margin: 0;
        }

        /* Preview Image */
        .preview-area img {
            max-width: 150px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }

        /* Remove Button */
        .remove-image {
            display: display-block;
            padding: 4px 4px;

            /* background-color: #dc3545; */
            color: #dc3545;
            text-decoration: underline;
            border-radius: 4px;
            font-size: 0.875rem;
            cursor: pointer;
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-item-center mb-1">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class=" mt-2">Treasury Challans</h5>
                </div>
                {{-- @if ($treasuryChallans->isEmpty())
                    <span class="text-muted">No Bills to show</span>
                @endif --}}
                <div class="d-flex gap-2">
                    {{-- Global Search Form --}}
                    <div class="input-group position-relative ">
                        <i class='bx bx-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted'
                            style="z-index: 5; pointer-events: none;"></i>
                        <input type="text" name="search"
                            class="form-control rounded-pill form-control-sm ps-4 js-local-search-input"
                            placeholder="Search..." value="{{ request('search') }}">

                    </div>
                    {{-- Advanced Filter Button --}}
                    <button style=" background: linear-gradient(135deg, #AAD9C9, #5DB698); border-style: none; color:black;"
                        type="button" class="btn btn-sm rounded-pill btn-primary" data-bs-toggle="modal"
                        data-bs-target="#advancedFilterModal">
                        <i class='bx bx-search'></i> Advanced
                    </button>
                </div>

                {{-- Export Buttons --}}
                <div class="d-flex justify-content-end mb-1">
                    <a href="{{ route('treasury.export.excel') }}" class="custom-excel-button me-2">Export
                        Excel</a>
                    <a href="{{ route('treasury.export.pdf') }}" class="custom-pdf-button">Export PDF</a>
                </div>
                @can('Create treasury challan button')
                    <div class="d-flex justify-content-end mb-1">
                        <a href="{{ route('billings.treasury-challans.create') }}" class="btn custom-primary-button"
                            type="submit">Create</a>
                    </div>
                @endcan

            </div>
            {{-- Advanced Filter Modal --}}
            <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-labelledby="advancedFilterModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET" action="{{ route('billings.treasury-challans.index') }}">
                            <div class="modal-header">
                                <h5 class="modal-title" id="advancedFilterModalLabel">Advanced Filters</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    {{-- inf Number --}}
                                    <div class="col-md-6">
                                        <label for="inf-number" class="form-label">Inf Number</label>
                                        <input type="text" name="inf_number" id="inf_number" class="form-control"
                                            placeholder="inf number" value="{{ request('inf_number') }}">
                                    </div>
                                    {{-- Memo Number --}}
                                    <div class="col-md-6">
                                        <label for="inf-number" class="form-label">Memo Number</label>
                                        <input type="text" name="memo_number" id="memo_number" class="form-control"
                                            placeholder="memo number" value="{{ request('memo_number') }}">
                                    </div>
                                </div>
                                {{-- Department Dropdown --}}
                                <div class="mb-3">
                                    <label for="department_id" class="form-label">Department</label>
                                    <select name="department_id" id="department_id" class="form-select select2">
                                        <option value="">All Departments</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Office Dropdown --}}
                                <div class="mb-3">
                                    <label for="office_id" class="form-label">Office</label>
                                    <select name="office_id" id="office_id" class="form-select select2">
                                        <option value="">All Offices</option>
                                        @foreach ($offices as $office)
                                            <option value="{{ $office->id }}"
                                                {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                                {{ $office->ddo_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Cheque Number --}}
                                <div class="row mb-3">
                                    <div class="col-md-6 ">
                                        <label for="cheque_number" class="form-label">Cheque Number</label>
                                        <input type="text" name="cheque_number" id="cheque_number" class="form-control"
                                            placeholder="cheque number" value="{{ request('cheque_number') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cheque Date</label>
                                        <input type="text" name="cheque_date" id="cheque_date" class="form-control"
                                            value="{{ request('cheque_date') }}" placeholder="Select Date Range">
                                    </div>
                                </div>
                                {{-- Status Dropdown --}}


                                {{-- Date Range --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="status_id" class="form-label">Status</label>
                                        <select name="status_id" id="status_id" class="form-select select2">
                                            <option value="">All Statuses</option>
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status->id }}"
                                                    {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                                    {{ $status->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 ">
                                        <label class="form-label">Bank Verify Date</label>
                                        <input type="text" name="sbp_verification_date" id="sbp_verification_date"
                                            class="form-control" value="{{ request('sbp_verification_date') }}"
                                            placeholder="Select Date Range">
                                    </div>

                                </div>

                                {{-- Hidden field to preserve global search if needed --}}
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            </div>
                            <div class="modal-footer">
                                <a href="{{ route('billings.treasury-challans.index') }}"
                                    class="btn btn-secondary">Reset</a>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {{-- Get the authenticated logged in user  --}}
            @php
                $user = Auth::User();
            @endphp

            {{-- Show ads if any --}}
            @if ($treasuryChallans->isNotEmpty())
                <div class="table-responsive">
                    <table class="table w-100 js-local-filter-table">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Memo No.</th>
                                <th>INF No.</th>
                                <th>Office</th>
                                <th>Cheque Number</th>
                                <th>Cheque Date</th>
                                {{-- <th>Newspapers Amount</th> --}}
                                <th>Cheque Amount</th>
                                <th>Bank Verify Date</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0 js-local-filter-rows">
                            @foreach ($treasuryChallans as $key => $treasuryChallan)
                                <tr>
                                    <td>{{ $treasuryChallans->firstItem() + $key }}</td> <!-- Serial Number -->
                                    <td>{{ $treasuryChallan->memo_number }}</td>
                                    {{-- <td>{{ implode(', ', $treasuryChallan->inf_number ?? '') }}</td> --}}
                                    <td>
                                        @foreach ($treasuryChallan->inf_number ?? [] as $inf)
                                            {{ $inf }} <br>
                                        @endforeach
                                    </td>
                                    <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                        {{ \Illuminate\Support\Str::words($treasuryChallan->office->ddo_name ?? '', 5, '...') }}
                                    </td>
                                    <td>{{ $treasuryChallan->cheque_number ?? '' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($treasuryChallan->cheque_date)->toFormattedDateString() }}
                                        {{-- <td>{{ $treasuryChallan->newspapers_amount ?? '-' }}</td> --}}
                                    <td>{{ number_format(round($treasuryChallan->total_amount)) ?? '-' }}</td>
                                    <td>{{ $treasuryChallan->sbp_verification_date?->toFormattedDateString() ?? '-' }}</td>
                                    </td>
                                    {{-- <td>{{ $treasuryChallan->status->title }}</td> --}}
                                    <td>
                                        @if ($treasuryChallan->status_id == 19)
                                            <span class="badge rounded-pill bg-label-warning">Pending Verification</span>
                                        @elseif ($treasuryChallan->status_id == 17)
                                            <span class="badge rounded-pill bg-label-warning">Pending Verification</span>
                                        @elseif($treasuryChallan->status_id == 18)
                                            <span class="badge rounded-pill bg-label-info">Pending/DG Approval</span>
                                        @elseif($treasuryChallan->status_id == 10)
                                            <span class="badge rounded-pill bg-label-success">Approved</span>
                                        @elseif($treasuryChallan->status_id == 'rejected')
                                            <span class="badge rounded-pill bg-label-danger">Rejected</span>
                                            @if ($treasuryChallan->rejection_reason)
                                                <small class="text-muted d-block"
                                                    title="{{ $treasuryChallan->rejection_reason }}">Reason
                                                    provided</small>
                                            @endif
                                            {{-- @else
                                        <span class="badge bg-warning">Pending Verificaition</span> --}}
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- View --}}
                                            {{-- <div class="action-item custom-tooltip">
                                            <a href="">
                                                <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                            </a>
                                            <span class="tooltip-text">View Ad</span>
                                        </div> --}}


                                            {{-- <div class="action-item custom-tooltip">
                                            <a href="{{ route('billings.treasury-challans.edit', $treasuryChallan->id) }}">
                                                <i class='bx bx-edit fs-4 bx-icon'></i>
                                                {{-- <span class="bx-icon test-sm">Newspaper Bills</span> --}
                                            </a>
                                            <span class="tooltip-text">Edit</span>
                                        </div> --}}


                                            {{-- Edit (media form) --}}
                                            <div class="action-item custom-tooltip">
                                                <a class="btn btn-success rounded-pill text-lg btn-xs"
                                                    href="{{ route('billings.treasury-challans.viewChallan', $treasuryChallan->id) }}">CHALLAN

                                                    <span class="tooltip-text">Challan</span>
                                                </a>
                                            </div>
                                            <div class="action-item custom-tooltip">
                                                <a class="btn btn-warning rounded-pill text-sm btn-xs"
                                                    href="{{ route('billings.treasury-challans.viewDepositSlip', $treasuryChallan->id) }}">DEPOSIT

                                                    <span class="tooltip-text">Deposit Slip</span>
                                                </a>
                                            </div>

                                            <div class="action-item custom-tooltip">
                                                @if ($user->hasRole('Superintendent'))
                                                    @if (empty($treasuryChallan->sbp_verification_date))
                                                        {{-- Show the check button when the SBP verification date is EMPTY (Pending) --}}
                                                        <a class="btn btn-danger rounded-circle btn-xs text-white"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal{{ $treasuryChallan->id }}">
                                                            <i class='bx bx-check text-white fs-4 bx-icon'></i>

                                                        </a>
                                                        <span class="tooltip-text">Verify Challan</span>
                                                    @else
                                                        <a class="btn btn-primary rounded-circle btn-xs disabled">
                                                            <i class='bx bx-check text-white fs-4 bx-icon'></i>
                                                        </a>
                                                        <span class="tooltip-text">Verified</span>
                                                    @endif
                                                @endif

                                                @if ($user->hasRole('Director General'))
                                                    @if (empty($treasuryChallan->approved_by))
                                                        <a class="btn btn-danger rounded-circle btn-xs text-white"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editDgModal{{ $treasuryChallan->id }}">
                                                            PLA
                                                        </a>
                                                        <span class="tooltip-text">Credit to PLA</span>
                                                    @else
                                                        {{-- Show the Credited badge when the SBP verification date is NOT EMPTY (Complete) --}}
                                                        <a class="btn btn-primary rounded-circle btn-xs disabled">
                                                            <i class='bx bx-check text-white fs-4 bx-icon'></i>
                                                        </a>
                                                        <span class="tooltip-text">Credited</span>
                                                    @endif
                                                @endif

                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $treasuryChallans->links() }}
                    </div>
                </div>
            @else
                <div class="text-center text-muted py-4">No Challan to show</div>
            @endif
        </div>
    </div>
    {{-- modal popup for superintendent to verify challan start here --}}
    @foreach ($treasuryChallans as $treasuryChallan)
        @if (empty($treasuryChallan->sbp_verification_date))
            <div class="modal fade" id="editModal{{ $treasuryChallan->id }}" tabindex="-1"
                aria-labelledby="editModalLabel{{ $treasuryChallan->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="editModalLabel{{ $treasuryChallan->id }}">Treasury Challan
                                #{{ $treasuryChallan->memo_number }}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Body --}}
                            <form method="POST"
                                action="{{ route('billings.treasury-challans.modalUpdate', $treasuryChallan->id) }}"
                                class="card-body" style="padding: 0;" id="upload-form">
                                @csrf
                                {{-- Page Content --}}
                                <div class="row custom-paddings">
                                    {{-- Newspaper Billing --}}
                                    {{-- @if ($newspapers->isNotEmpty()) --}}
                                    <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">

                                        {{-- Title (Header) --}}
                                        <div class="card-header-table">
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="{{ url()->previous() }}" class="back-button"><i
                                                        class='bx bx-arrow-back'></i></a>
                                                <h5 class="h5-reset-margin">Treasury Challan Verification</h5>
                                            </div>
                                        </div>



                                        {{-- Data --}}
                                        <div class="form-padding">
                                            <div class="row g-4">
                                                <div class="col-md-12">
                                                    <div class="row g-1">

                                                        {{-- Treasury Verify Date --}}
                                                        <div class="col-md-12">
                                                            <label class="form-label-x col-form-label"
                                                                for="tr-challan-verification-date-{{ $treasuryChallan->id }}">Treasury
                                                                Verify Date
                                                            </label>
                                                            <input type="text"
                                                                id="tr-challan-verification-date-{{ $treasuryChallan->id }}"
                                                                name="tr_challan_verification_date" class="form-control"
                                                                value="{{ $treasuryChallan->tr_challan_verification_date }}"
                                                                placeholder="DD-MM-YYYY" />
                                                        </div>

                                                        {{-- SBP Verify Date --}}
                                                        <div class="col-md-12">
                                                            <label class="form-label-x col-form-label"
                                                                for="sbp-verification-date-{{ $treasuryChallan->id }}">Bank
                                                                Verify
                                                                Date
                                                            </label>
                                                            <input type="text"
                                                                id="sbp-verification-date-{{ $treasuryChallan->id }}"
                                                                name="sbp_verification_date" class="form-control"
                                                                value="{{ $treasuryChallan->sbp_verification_date }}"
                                                                placeholder="DD-MM-YYYY" />
                                                        </div>

                                                        {{-- Cheque Number --}}
                                                        <div class="col-md-12">
                                                            <label class="form-label-x col-form-label"
                                                                for="challan-number-{{ $treasuryChallan->id }}">Challan #
                                                            </label>
                                                            <input type="text"
                                                                id="challan-number-{{ $treasuryChallan->id }}"
                                                                name="challan_number" class="form-control"
                                                                value="{{ $treasuryChallan->challan_number }}"
                                                                placeholder="Enter challan number" />
                                                        </div>

                                                        {{-- challan image --}}
                                                        {{-- <div class="col-md-12">
                                                            <label class="form-label-x col-form-label">
                                                                Upload Challan File
                                                            </label>
                                                            <input type="file" name="tr_challan_image"
                                                                class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf"
                                                                id="tr_challan_image">

                                                            @error('tr_challan_image')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror

                                                            @if ($treasuryChallan->tr_challan_image)
                                                                <small class="text-muted mt-2 d-block">
                                                                    Current file:
                                                                    <a href="{{ Storage::url($treasuryChallan->tr_challan_image) }}"
                                                                        target="_blank" class="ms-2">
                                                                        <i class='bx bx-show'></i> View
                                                                    </a>
                                                                </small>
                                                            @endif
                                                        </div> --}}

                                                        <!-- Basic  -->
                                                        {{-- Challan Image Upload --}}
                                                        {{-- <div class="col-md-12">
                                                            <label class="form-label-x col-form-label">
                                                                Upload Challan File
                                                            </label>
                                                            <input type="file" name="image">
                                                            <div id="my-dropzone-{{ $treasuryChallan->id }}"
                                                                class="dropzone-container text-center border border-success rounded p-3">
                                                                <div class="dz-message">
                                                                    <i
                                                                        class="fas fa-cloud-upload-alt fa-2x text-success"></i><br>
                                                                    <span>Drag & drop or click to upload image</span>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="tr_challan_image"
                                                                id="uploaded-tr-challan-image-{{ $treasuryChallan->id }}"
                                                                value="{{ $treasuryChallan->tr_challan_image }}">
                                                        </div> --}}

                                                        <div class="card mb-4">
                                                            <label class="form-label-x col-form-label"
                                                                for="uload image">Upload Challan File
                                                            </label>
                                                            <div class="upload-box"
                                                                id="upload-box-{{ $treasuryChallan->id }}"
                                                                data-id="{{ $treasuryChallan->id }}">
                                                                <input type="file"
                                                                    id="file-input-{{ $treasuryChallan->id }}"
                                                                    class="file-input d-none" accept="image/*">

                                                                <!-- PLACEHOLDER -->

                                                                <div class="upload-placeholder">
                                                                    <!-- SVG Dropzone Icon -->
                                                                    <span class="dz-icon">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            width="48" height="48"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="#28a745" stroke-width="2"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path
                                                                                d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                                            <polyline points="7 10 12 15 17 10" />
                                                                            <line x1="12" y1="15"
                                                                                x2="12" y2="3" />
                                                                        </svg>
                                                                    </span>
                                                                    <p>
                                                                        Drag & Drop Challan Image Here <br>
                                                                        <small>or click to upload</small>
                                                                    </p>
                                                                </div>

                                                                <!-- IMAGE PREVIEW -->
                                                                <div class="preview-area"></div>
                                                            </div>

                                                            <input type="hidden"
                                                                id="uploaded-tr-challan-image-{{ $treasuryChallan->id }}"
                                                                name="tr_challan_image">
                                                        </div>

                                                        {{-- <div class="col-12">
                                                            <div class="card mb-4">
                                                                <h5 class="card-header"> Upload Challan File</h5>
                                                                <div class="card-body">
                                                                    <div class="dropzone-container border border-success rounded p-3"
                                                                        id="my-dropzone-{{ $treasuryChallan->id }}">
                                                                        <div class="dz-message needsclick">
                                                                            <i
                                                                                class="fas fa-cloud-upload-alt fa-2x text-success"></i><br>
                                                                            <span>Drag & drop or click to upload
                                                                                image</span>
                                                                        </div>
                                                                        <div class="fallback">
                                                                            <input type="hidden" name="tr_challan_image"
                                                                                id="uploaded-tr-challan-image-{{ $treasuryChallan->id }}"
                                                                                value="{{ $treasuryChallan->tr_challan_image }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div> --}}

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- ! / Page Content --}}
                                <div class="modal-footer">
                                    <button type="button" class="custom-secondary-button"
                                        data-bs-dismiss="modal">Close</button>
                                    {{-- Save challans --}}
                                    <button type="submit" class="custom-primary-button">Verify & Forward to
                                        DG</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- modal popup ends here --}}
    {{-- modal popup for dg approval to verify challan start here --}}
    @foreach ($treasuryChallans as $treasuryChallan)
        @if (empty($treasuryChallan->approved_by))
            <div class="modal fade" id="editDgModal{{ $treasuryChallan->id }}" tabindex="-1"
                aria-labelledby="editModalLabel{{ $treasuryChallan->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="editModalLabel{{ $treasuryChallan->id }}">Treasury Challan
                                #{{ $treasuryChallan->memo_number }}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Body --}}
                            <form method="POST"
                                action="{{ route('treasury-challans.dg-approve', $treasuryChallan->id) }}"
                                enctype="multipart/form-data" class="card-body" style="padding: 0;">
                                @csrf
                                {{-- Page Content --}}
                                <div class="row custom-paddings">
                                    {{-- Newspaper Billing --}}
                                    {{-- @if ($newspapers->isNotEmpty()) --}}
                                    <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">

                                        {{-- Title (Header) --}}
                                        <div class="card-header-table">
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="{{ url()->previous() }}" class="back-button"><i
                                                        class='bx bx-arrow-back'></i></a>
                                                <h5 class="h5-reset-margin">Treasury Challan Verification</h5>
                                            </div>
                                        </div>



                                        {{-- Data --}}
                                        <div class="form-padding">
                                            <div class="row g-4">
                                                <div class="col-md-12">
                                                    <div class="row g-1">

                                                        {{-- Treasury Verify Date --}}
                                                        <div class="col-md-12">
                                                            <label class="form-label-x col-form-label"
                                                                for="tr-challan-verification-date-{{ $treasuryChallan->id }}">Treasury
                                                                Verify Date
                                                            </label>
                                                            <input type="date"
                                                                id="tr-challan-verification-date-{{ $treasuryChallan->id }}"
                                                                name="tr_challan_verification_date" class="form-control"
                                                                value="{{ $treasuryChallan->tr_challan_verification_date ? $treasuryChallan->tr_challan_verification_date->format('Y-m-d') : '' }}"
                                                                placeholder="DD-MM-YYYY" />
                                                        </div>

                                                        {{-- SBP Verify Date --}}
                                                        <div class="col-md-12">
                                                            <label class="form-label-x col-form-label"
                                                                for="sbp-verification-date-{{ $treasuryChallan->id }}">Bank
                                                                Verify
                                                                Date
                                                            </label>


                                                            <input type="date"
                                                                id="sbp-verification-date-{{ $treasuryChallan->id }}"
                                                                name="sbp_verification_date" class="form-control"
                                                                value="{{ $treasuryChallan->sbp_verification_date ? $treasuryChallan->sbp_verification_date->format('Y-m-d') : '' }}"
                                                                placeholder="DD-MM-YYYY" />
                                                        </div>

                                                        {{-- Cheque Number --}}
                                                        <div class="col-md-12">
                                                            <label class="form-label-x col-form-label"
                                                                for="challan-number-{{ $treasuryChallan->id }}">Challan #
                                                            </label>
                                                            <input type="text"
                                                                id="challan-number-{{ $treasuryChallan->id }}"
                                                                name="challan_number" class="form-control"
                                                                value="{{ $treasuryChallan->challan_number }}"
                                                                placeholder="Enter challan number" />
                                                        </div>

                                                        {{-- challan image --}}

                                                        <!-- Basic  -->
                                                        {{-- Challan Image Upload --}}
                                                        {{-- <div class="col-md-12">
                                                            <label class="form-label-x col-form-label">
                                                                Upload Challan File
                                                            </label>
                                                            <div id="my-dropzone-{{ $treasuryChallan->id }}"
                                                                class="dropzone-container text-center border border-success rounded p-3">
                                                                <div class="dz-message">
                                                                    <i
                                                                        class="fas fa-cloud-upload-alt fa-2x text-success"></i><br>
                                                                    <span>Drag & drop or click to upload image</span>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="tr_challan_image"
                                                                id="uploaded-tr-challan-image-{{ $treasuryChallan->id }}"
                                                                value="{{ $treasuryChallan->tr_challan_image }}">
                                                        </div> --}}

                                                        <!-- With this simple file input: -->
                                                        {{-- <div class="col-md-12">
                                                            <label class="form-label-x col-form-label">
                                                                View uploaded challan file
                                                            </label>
                                                            <div class="upload-box">
                                                                @if ($treasuryChallan->tr_challan_image)
                                                                    <div class="preview-area text-center">

                                                                        <small class="text-muted mt-2 d-block">

                                                                            <a href="{{ Storage::url($treasuryChallan->tr_challan_image) }}"
                                                                                target="_blank"
                                                                                class="ms-2 text-decoration-none">
                                                                                <img src="{{ Storage::url($treasuryChallan->tr_challan_image) }}"
                                                                                    alt="Treasury Challan"
                                                                                    class="img-thumbnail rounded text-center"
                                                                                    style="width: 100px; height: 100px; object-fit: cover;">
                                                                            </a>

                                                                            <a href="{{ Storage::url($treasuryChallan->tr_challan_image) }}"
                                                                                download="treasury-challan-image-{{ $treasuryChallan->id }}.jpg"
                                                                                class="ms-2 text-decoration-none">
                                                                                <i class='bx bx-download'></i> Download
                                                                            </a>
                                                                        </small>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div> --}}
                                                        <div class="card mb-4">
                                                            <label class="form-label-x col-form-label">
                                                                View uploaded challan file
                                                            </label>
                                                            <div class="upload-box">
                                                                <!-- IMAGE PREVIEW -->
                                                                <div class="preview-area">
                                                                    @if ($treasuryChallan->tr_challan_image)
                                                                        <div class="text-center">

                                                                            <a href="{{ Storage::url($treasuryChallan->tr_challan_image) }}"
                                                                                target="_blank"
                                                                                class="ms-2 text-decoration-none">
                                                                                <img src="{{ asset('storage/' . $treasuryChallan->tr_challan_image) }}"
                                                                                    class="img-fluid mb-2"
                                                                                    style="max-width:180px;">
                                                                                <br>
                                                                                <a href="{{ asset('storage/' . $treasuryChallan->tr_challan_image) }}"
                                                                                    download
                                                                                    class="ms-2 text-decoration-none">
                                                                                    <i class='bx bx-download'></i>
                                                                                    Download
                                                                                </a>
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                            </div>
                                                        </div>

                                                        {{-- <div class="col-12">
                                                            <div class="card mb-4">
                                                                <h5 class="card-header"> Upload Challan File</h5>
                                                                <div class="card-body">
                                                                    <div class="dropzone-container border border-success rounded p-3"
                                                                        id="my-dropzone-{{ $treasuryChallan->id }}">
                                                                        <div class="dz-message needsclick">
                                                                            <i
                                                                                class="fas fa-cloud-upload-alt fa-2x text-success"></i><br>
                                                                            <span>Drag & drop or click to upload
                                                                                image</span>
                                                                        </div>
                                                                        <div class="fallback">
                                                                            <input type="hidden" name="tr_challan_image"
                                                                                id="uploaded-tr-challan-image-{{ $treasuryChallan->id }}"
                                                                                value="{{ $treasuryChallan->tr_challan_image }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div> --}}
                                                        <div class="col-md-12">
                                                            <label class="form-label-x col-form-label">
                                                                Confirmation
                                                            </label>
                                                            {{-- Approval Confirmation --}}
                                                            <div class="alert alert-warning">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="confirmVerification"
                                                                        name="confirm_verification" required>
                                                                    <label class="form-check-label fw-bold"
                                                                        for="confirmVerification">
                                                                        <i class='bx bx-check-shield'></i> I hereby certify
                                                                        that the cheque has been verified and credited to
                                                                        the PLA.

                                                                    </label>
                                                                    <div class="form-text text-danger">
                                                                        <i class='bx bx-info-circle'></i> By checking this
                                                                        box, you confirm that you have verified all details
                                                                        and authorize the creation of PLA account entry.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- ! / Page Content --}}
                                <div class="modal-footer">
                                    <button type="button" class="custom-secondary-button"
                                        data-bs-dismiss="modal">Close</button>
                                    {{-- Save challans --}}
                                    <button type="submit" class="custom-primary-button">Approve & Credit to PLA</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- modal popup ends here --}}
    {{-- ! / Page Content --}}
@endpush
@push('scripts')
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            Dropzone.autoDiscover = false;

            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
                const modalId = button.getAttribute('data-bs-target');
                // Only handle superintendent modals (where dropzone exists)
                if (!modalId.startsWith('#editModal')) return;

                const modalElement = document.querySelector(modalId);
                if (!modalElement) return;

                modalElement.addEventListener('shown.bs.modal', function() {
                    // Extract numeric ID from modalId (e.g., #editModal123 -> 123)
                    const challanId = modalId.match(/\d+$/)[0];

                    // Initialize flatpickr for date fields
                    const trDate = document.getElementById(
                        `tr-challan-verification-date-${challanId}`);
                    if (trDate && !trDate._flatpickr) {
                        flatpickr(trDate, {
                            altInput: true,
                            altFormat: "d-m-Y",
                            dateFormat: "Y-m-d"
                        });
                    }
                    const sbpDate = document.getElementById(`sbp-verification-date-${challanId}`);
                    if (sbpDate && !sbpDate._flatpickr) {
                        flatpickr(sbpDate, {
                            altInput: true,
                            altFormat: "d-m-Y",
                            dateFormat: "Y-m-d"
                        });
                    }

                    // Initialize Dropzone
                    const dropzoneEl = document.getElementById(`my-dropzone-${challanId}`);
                    if (!dropzoneEl) return;

                    // Destroy any existing Dropzone instance to avoid conflicts
                    if (dropzoneEl.dropzone) {
                        dropzoneEl.dropzone.destroy();
                        dropzoneEl.dropzone = null;
                    }

                    // Capture challanId for use inside callbacks
                    const currentChallanId = challanId;

                    const myDropzone = new Dropzone(dropzoneEl, {
                        url: "{{ route('tr_challan_image.upload') }}",
                        paramName: "file",
                        maxFiles: 1,
                        acceptedFiles: "image/*",
                        maxFilesize: 2, // MB, adjust as needed
                        addRemoveLinks: true,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        addedfile: function(file) {
                            console.log('File added:', file.name, 'status:', file
                                .status);
                            console.log('autoProcessQueue:', this.options
                                .autoProcessQueue);
                            console.log('Current queue length:', this.files.length);
                            this.processQueue(); // Force upload to start
                        },
                        sending: function(file, xhr, formData) {
                            console.log('Sending upload for', file.name);
                        },
                        success: function(file, response) {
                            console.log('Upload success', response);
                            if (response && response.file_path) {
                                document.getElementById(
                                        `uploaded-tr-challan-image-${ChallanId}`)
                                    .value = response.file_path;
                                console.log("Image path set:", response.file_path);
                            }
                        },
                        error: function(file, errorMessage) {
                            console.error('Error on file', file.name, ':',
                                errorMessage);
                        },
                        removedfile: function(file) {
                            console.log('File removed:', file.name);
                            document.getElementById(
                                    `uploaded-tr-challan-image-${ChallanId}`)
                                .value = '';
                            if (file.previewElement && file.previewElement.parentNode) {
                                file.previewElement.remove();
                            }
                        },
                        queuecomplete: function() {
                            console.log('Queue complete, remaining files:', this.files
                                .length);
                        }
                    });

                    dropzoneEl.dropzone = myDropzone;
                });
            });
        });
    </script> --}}

    {{-- <script>
        Dropzone.autoDiscover = false;
        document.addEventListener('DOMContentLoaded', function() {
            // Disable auto-discover of Dropzone

            // Use a selector to find ALL PLA buttons on the page
            const plaButtons = document.querySelectorAll('[data-bs-toggle="modal"]');

            plaButtons.forEach(button => {
                const modalId = button.getAttribute('data-bs-target');
                const modalElement = document.querySelector(modalId);

                if (modalElement) {
                    // Add event listener to each unique modal element
                    modalElement.addEventListener('shown.bs.modal', function() {

                        // Extract the unique ID from the modal's ID (e.g., #editModal123 -> 123)
                        const challanId = modalId.replace('#editModal', '');

                        // Construct the unique IDs for the date inputs
                        const trDateSelector = `#tr-challan-verification-date-${challanId}`;
                        const sbpDateSelector = `#sbp-verification-date-${challanId}`;

                        // Helper function to initialize Flatpickr safely (prevents re-initialization)
                        const initFlatpickr = (selector) => {
                            const element = document.querySelector(selector);
                            if (element && !element._flatpickr) {
                                flatpickr(element, {
                                    altInput: true,
                                    altFormat: "d-m-Y",
                                    dateFormat: "Y-m-d",
                                    // Remove onReady hook as it is not needed when initialized on shown.bs.modal
                                });
                            }
                        };

                        // 1. Initialize Treasury Verify Date Flatpickr
                        initFlatpickr(trDateSelector);

                        // 2. Initialize SBP Verify Date Flatpickr
                        initFlatpickr(sbpDateSelector);


                        function initDropzone(challanId) {

                            const dropzoneEl =
                                document.querySelector(`#my-dropzone-${challanId}`);

                            if (!dropzoneEl) return;

                            if (dropzoneEl.dropzone) {
                                dropzoneEl.dropzone.destroy();
                            }

                            new Dropzone(dropzoneEl, {
                                url: "{{ route('tr_challan_image.upload') }}",
                                paramName: "file",
                                maxFiles: 1,
                                acceptedFiles: "image/*",
                                addRemoveLinks: true,

                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },

                                success: function(file, response) {

                                    if (response?.file_path) {

                                        document.getElementById(
                                            `uploaded-tr-challan-image-${challanId}`
                                        ).value = response.file_path;
                                    }
                                },

                                removedfile: function(file) {

                                    document.getElementById(
                                        `uploaded-tr-challan-image-${challanId}`
                                    ).value = '';

                                    file.previewElement.remove();
                                }
                            });
                        }
                        // Initialize Dropzone
                        $(`div#my-dropzone-${challanId}`).dropzone({
                            url: "/file/post"
                        });
                        const dropzoneEl = this.querySelector(`#my-dropzone-${challanId}`);
                        //                         setTimeout(() => {
                        //     initDropzone(challanId);
                        // }, 200);

                        // if (dropzoneEl && !dropzoneEl.dropzone) {
                        //     const myDropzone = new Dropzone(dropzoneEl, {
                        //         url: "{{ route('tr_challan_image.upload') }}",
                        //         paramName: "file",
                        //         maxFiles: 1,
                        //         acceptedFiles: "image/*",
                        //         addRemoveLinks: true,
                        //         headers: {
                        //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        //         },
                        //         success: function(file, response) {
                        //             // console.log("Response from upload:", response);

                        //             // Check what's in the response
                        //             if (response && response.file_path) {
                        //                 const hiddenInput = document.getElementById(
                        //                     `uploaded-tr-challan-image-${challanId}`

                        //                 );
                        //                 if (hiddenInput) {
                        //                     hiddenInput.value = response.file_path;
                        //                     console.log("Set hidden input to:", response
                        //                         .file_path);
                        //                 }
                        //             }
                        //         },
                        //         error: function(file, errorMessage) {
                        //             console.error("Upload failed:", errorMessage);
                        //         },
                        //         removedfile: function(file) {
                        //             const hiddenInput = document.getElementById(
                        //                 `uploaded-tr-challan-image-${challanId}`);
                        //             if (hiddenInput) {
                        //                 hiddenInput.value = '';
                        //             }
                        //         }
                        //     });

                        //     dropzoneEl.dropzone = myDropzone;
                        // }
                    });
                }
            });
        });
    </script> --}}
    {{-- <script>
        Dropzone.autoDiscover = false;
        // When the modal is shown, initialize Dropzone on the element inside that modal
        modalElement.addEventListener('shown.bs.modal', function() {
            // ... existing Flatpickr initialization ...

            // Now initialize Dropzone
            const dropzoneEl = this.querySelector('#my-dropzone-{{ $treasuryChallan->id }}');
            if (dropzoneEl && !dropzoneEl.dropzone) {
                const myDropzone = new Dropzone(dropzoneEl, {
                    url: "{{ route('tr_challan_image.upload') }}",
                    paramName: "file",
                    maxFiles: 1,
                    acceptedFiles: "image/*",
                    addRemoveLinks: true,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(file, response) {
                        // Set the value of the corresponding hidden input
                        document.getElementById('uploaded_tr_challan_image-{{ $treasuryChallan->id }}')
                            .value = response.file_path;
                    },
                    removedfile: function(file) {
                        document.getElementById('uploaded_tr_challan_image-{{ $treasuryChallan->id }}')
                            .value = '';
                        file.previewElement.remove();
                    }
                });
                // Store the Dropzone instance on the element to avoid re-initialization
                dropzoneEl.dropzone = myDropzone;
            }
        });
    </script> --}}
    <script>
        $(document).ready(function() {

            // Click to open file selector
            $(document).on('click', '.upload-box', function(e) {
                e.stopPropagation();
                let challanId = $(this).data('id');
                $('#file-input-' + challanId)[0].click();
            });

            // File selected
            $(document).on('change', 'input[type="file"]', function() {
                let input = $(this);
                let challanId = this.id.replace('file-input-', '');
                let file = this.files[0];

                if (!file) return;

                let formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');

                let box = $('#upload-box-' + challanId);
                let preview = box.find('.preview-area');

                console.log("Uploading file:", file.name);

                $.ajax({
                    url: "{{ route('tr_challan_image.upload') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(response) {
                        console.log("Upload success:", response);

                        if (response.file_path) {
                            // Set hidden input
                            $('#uploaded-tr-challan-image-' + challanId).val(response
                                .file_path);

                            // Show preview image
                            preview.html(`
                    <img src="/storage/${response.file_path}" alt="Challan Image">
                    <div class="remove-image" data-id="${challanId}">Remove</div>
                `);

                            // Hide placeholder
                            box.addClass('has-image');
                        }
                    },
                    error: function(xhr) {
                        console.error("Upload error:", xhr.responseText);
                        alert("Upload failed");
                    }
                });
            });

            // Remove image
            $(document).on('click', '.remove-image', function(e) {
                e.stopPropagation();
                let challanId = $(this).data('id');
                let box = $('#upload-box-' + challanId);

                // Clear hidden input & preview
                $('#uploaded-tr-challan-image-' + challanId).val('');
                box.find('.preview-area').html('');
                box.removeClass('has-image');

                // Clear file input
                $('#file-input-' + challanId).val('');
            });

            // Drag & Drop
            $(document).on('dragover', '.upload-box', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });

            $(document).on('dragleave', '.upload-box', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });

            $(document).on('drop', '.upload-box', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');

                let challanId = $(this).data('id');
                let files = e.originalEvent.dataTransfer.files;

                if (files.length > 0) {
                    $('#file-input-' + challanId)[0].files = files;
                    $('#file-input-' + challanId).trigger('change');
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('[id^="tr-challan-verification-date-"]').forEach(input => {
                flatpickr(input, {
                    altInput: true,
                    altFormat: "d-m-Y",
                    dateFormat: "Y-m-d", // backend storage format
                    allowInput: true
                });
            });

            document.querySelectorAll('[id^="sbp-verification-date-"]').forEach(input => {
                flatpickr(input, {
                    altInput: true,
                    altFormat: "d-m-Y",
                    dateFormat: "Y-m-d", // backend storage format
                    allowInput: true
                });
            });

        });
    </script>
    <script>
        flatpickr("#cheque_date", {
            mode: "range",
            dateFormat: "d-m-Y",
            allowInput: true
        });
        flatpickr("#sbp_verification_date", {
            mode: "range",
            dateFormat: "d-m-Y",
            allowInput: true
        });
    </script>
    <script>
        (function() {
            function normalize(s) {
                return (s || '').toString().toLowerCase();
            }

            function applyLocalFilter(q) {
                const query = normalize(q).trim();
                const tbody = document.querySelector('tbody.js-local-filter-rows');
                if (!tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.forEach((tr) => {
                    const hay = normalize(tr.innerText || tr.textContent || '');
                    tr.style.display = !query || hay.includes(query) ? '' : 'none';
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const input = document.querySelector('.js-local-search-input');
                if (!input) return;

                input.addEventListener('input', function() {
                    applyLocalFilter(input.value);
                });

                if (input.value) applyLocalFilter(input.value);
            });
        })();
    </script>
    {{-- <script>
        flatpickr("#submission_date", {
            mode: "range",
            dateFormat: "d-m-Y",
            allowInput: true
        });
        flatpickr("#publication_date", {
            mode: "range",
            dateFormat: "d-m-Y",
            allowInput: true
        });
    </script> --}}
@endpush

@extends('layouts.masterVertical')



@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="{{ url()->previous() }}" class="back-button me-2"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="mb-0">Agencies Bills Request</h5>
                </div>
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
                    <a href="{{ route('reports.billing.export.excel', array_merge(request()->query(), ['billing_type' => 'agency'])) }}"
                        class="custom-excel-button me-2">
                        Export Excel</a>
                    <a href="{{ route('reports.billing.export.pdf', array_merge(request()->query(), ['billing_type' => 'agency'])) }}"
                        class="custom-pdf-button">Export PDF</a>
                </div>
            </div>

            {{-- Advanced Filter Modal --}}
            <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-labelledby="advancedFilterModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET" action="{{ route('billings.agencies.index') }}">
                            <div class="modal-header">
                                <h5 class="modal-title" id="advancedFilterModalLabel">Advanced Filters</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                {{-- inf Number --}}
                                <div class="mb-3">
                                    <label for="inf-number" class="form-label">Inf Number</label>
                                    <input type="text" name="inf_number" id="inf_number" class="form-control"
                                        placeholder="inf number" value="{{ request('inf_number') }}">
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

                                {{-- Status Dropdown --}}
                                {{-- <div class="mb-3">
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
                                </div> --}}

                                {{-- Date Range --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Publication Date</label>
                                        <input type="text" name="publication_date" id="publication_date"
                                            class="form-control" value="{{ request('publication_date') }}"
                                            placeholder="Select Date Range">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Bill/Invoice Date</label>
                                        <input type="text" name="submission_date" id="submission_date"
                                            class="form-control" value="{{ request('submission_date') }}"
                                            placeholder="Select Date Range">
                                    </div>
                                </div>
                                {{-- Hidden field to preserve global search if needed --}}
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            </div>
                            <div class="modal-footer">
                                <a href="{{ route('billings.agencies.index') }}" class="btn btn-secondary">Reset</a>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {{-- Get the authenticated logged in user --}}
            @php
                $user = Auth::User();
            @endphp

            {{-- Show ads if any --}}
            @if ($billClassifiedAds->isNotEmpty())
                <div class="table-responsive">
                    <table class="table w-100 js-local-filter-table">
                        <thead>
                            <tr>
                                <th style="padding-right: 0 !important;">S. No.</th>
                                {{-- <th>INF No.</th> --}}
                                <th>Bill Date</th>
                                <th>Office/Department</th>
                                <th>Agency Name</th>
                                <th>Insertions</th>
                                <th>Total Dues</th>
                                <th>Kpra Tax</th>
                                <th>Net Dues</th>
                                <th>Publication Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0 js-local-filter-rows">
                            @foreach ($billClassifiedAds as $key => $billClassifiedAd)
                                <td>{{ ++$key }}</td> <!-- Serial Number -->
                                <td>{{ \Carbon\Carbon::parse($billClassifiedAd->invoice_date)->toFormattedDateString() }}
                                </td>
                                <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                    {{ \Illuminate\Support\Str::words($billClassifiedAd->advertisement->office->ddo_name ?? 'N/A', 5, '...') }}
                                </td>
                                <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                    {{ \Illuminate\Support\Str::words($billClassifiedAd->user->agency->name ?? 'N/A', 500, '...') }}
                                </td>
                                <td class="text-center">{{ $billClassifiedAd->printed_no_of_insertion }}</td>
                                <td>{{ $billClassifiedAd->printed_bill_cost }}</td>
                                <td>{{ $billClassifiedAd->kpra_tax }}</td>
                                <td>{{ $billClassifiedAd->printed_total_bill }}</td>
                                <td>{{ \Carbon\Carbon::parse($billClassifiedAd->publication_date)->toFormattedDateString() }}
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center align-items-center">

                                        {{-- View --}}
                                        {{-- <div class="action-item custom-tooltip">
                                                <a href="{{ route('advertisements.show', $advertisement->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View Ad</span>
                                            </div> --}}


                                        <div class="action-item custom-tooltip">
                                            <a href="{{ route('billings.agencies.bill.detail', $billClassifiedAd->id) }}">

                                                <i class='bx bx-file fs-4 bx-icon'></i>

                                            </a>
                                            <span class="tooltip-text">Agency Bills</span>
                                        </div>


                                        {{-- Edit (media form) --}}
                                        <div class="action-item custom-tooltip">
                                            <a href="{{ route('billings.agencies.print', $billClassifiedAd->id) }}"
                                                target="_blank">
                                                <i class='bx bx-printer fs-4 bx-icon'></i>
                                            </a>
                                            <span class="tooltip-text">Print bill</span>
                                        </div>

                                        {{-- Forward bill to Client Office --}}
                                        <div class="action-item custom-tooltip">
                                            @if ($user->hasRole('Superintendent') && $billClassifiedAd->advertisement->bill_submitted_to_role_id == null)
                                                <form
                                                    action="{{ route('billings.newspapers.agencyBillSubmission', $billClassifiedAd->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn p-0 border-0 bg-transparent">
                                                        <i class='bx bx-right-top-arrow-circle bx-icon'></i>
                                                    </button>
                                                    <span class="tooltip-text">Submit bill</span>
                                                </form>
                                            @elseif (!$user->hasRole(['Client Office', 'Super Admin', 'Deputy Director', 'Director General']))
                                                <i class='bx bx-right-top-arrow-circle fs-4 bx-icon text-muted'></i>
                                                <span class="tooltip-text">Bill Submitted</span>
                                            @endif
                                        </div>
                                        {{-- Track --}}
                                        {{-- @can('Track Ad Btn')
                                                <div class="action-item custom-tooltip">
                                                    <a type="button" data-bs-toggle="modal"
                                                        data-bs-target="#trackAdModal{{ $advertisement->id }}">
                                                        <i class="bx bx-trip fs-4 bx-icon"></i>
                                                    </a>
                                                    <span class="tooltip-text">Track Ad</span>
                                                </div>
                                            @endcan --}}

                                    </div>
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- <div class="custom-pagination">
                        {{ $advertisements->links() }}
                    </div> --}}
                </div>
            @else
                <div class="text-center text-muted py-4">No ads to show</div>
            @endif
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush

@push('scripts')
    <script>
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
@endpush

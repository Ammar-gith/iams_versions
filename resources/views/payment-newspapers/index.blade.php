@extends('layouts.masterVertical')



@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5">Payment Legderization</h5>
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
                    <a href="{{ route('payment.newspapers.export.excel', request()->query()) }}"
                        class="custom-excel-button me-2">Export Excel</a>
                    <a href="{{ route('payment.newspapers.export.pdf', request()->query()) }}" class="custom-pdf-button">
                        Export PDF</a>
                </div>
            </div>

            {{-- Advanced Filter Modal --}}
            <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-labelledby="advancedFilterModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET" action="{{ route('payment.newspapers.index') }}">
                            <div class="modal-header">
                                <h5 class="modal-title" id="advancedFilterModalLabel">Advanced Filters</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                {{-- inf Number --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="inf-number" class="form-label">Inf Number</label>
                                        <input type="text" name="inf_number" id="inf_number" class="form-control"
                                            placeholder="inf number" value="{{ request('inf_number') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cheque-number" class="form-label">Cheque Number</label>
                                        <input type="text" name="cheque_number" id="cheque_number" class="form-control"
                                            placeholder="cheque number" value="{{ request('cheque_number') }}">
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

                                {{-- Status Dropdown --}}
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
                                    <div class="col-md-6">
                                        <label for="challan-number" class="form-label">Challan Number</label>
                                        <input type="text" name="challan_number" id="challan_number" class="form-control"
                                            placeholder="cheque number" value="{{ request('challan_number') }}">
                                    </div>
                                </div>



                                {{-- Date Range --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tr_challan_verification_date" class="form-label">Treasury Verify
                                            Date</label>
                                        <input type="date" name="tr_challan_verification_date"
                                            id="tr_challan_verification_date" class="form-control date"
                                            placeholder="DD-MM-YYYY"
                                            value="{{ request('tr_challan_verification_date') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="sbp_verification_date" class="form-label">Bank Verify Date</label>
                                        <input type="date" name="sbp_verification_date" id="sbp_verification_date"
                                            class="form-control date" placeholder="DD-MM-YYYY"
                                            value="{{ request('sbp_verification_date') }}">
                                    </div>
                                </div>

                                {{-- Hidden field to preserve global search if needed --}}
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            </div>
                            <div class="modal-footer">
                                <a href="{{ route('payment.newspapers.index') }}" class="btn btn-secondary">Reset</a>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {{-- Get the authenticated logged in user --}}
            {{-- @php
                $user = Auth::User();
            @endphp --}}

            {{-- Show ads if any --}}
            @if ($paymentNewspapers->isNotEmpty())
                <div class="table-responsive">
                    <table class="table w-100 js-local-filter-table">
                        <thead>
                            <tr>
                                {{-- <th>ID</th> --}}
                                <th>INF No.</th>
                                <th>Office</th>
                                <th>Cheque Number</th>
                                {{-- <th>Cheque Date</th> --}}
                                <th>Cheque Amount</th>
                                <th>Treasury Verify Date</th>
                                <th>Challan No.</th>
                                <th>Bank Verify Date</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0 js-local-filter-rows">
                            @foreach ($paymentNewspapers as $key => $paymentNewspaper)
                                {{-- {{ dd($paymentNewspaper) }} --}}
                                <tr>
                                    {{-- <td>{{ $paymentNewspaper->id }}</td> <!-- Serial Number --> --}}
                                    {{-- <td>{{ implode(', ', $paymentNewspaper->inf_number ?? '') }}</td> --}}
                                    <td>
                                        @foreach ($paymentNewspaper->inf_number ?? [] as $inf)
                                            {{ $inf }} <br>
                                        @endforeach
                                    </td>
                                    <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                        {{ \Illuminate\Support\Str::words($paymentNewspaper->office->ddo_name ?? '', 5, '...') }}
                                    </td>
                                    <td>{{ $paymentNewspaper->cheque_number ?? '' }}</td>
                                    {{-- <td>{{$paymentNewspaper->cheque_number_date?->toFormattedDateString() ?? '-' }}</td> --}}
                                    </td>
                                    {{-- <td>{{ $paymentNewspaper->newspapers_amount ?? '-' }}</td> --}}
                                    <td>{{ number_format(round($paymentNewspaper->total_amount)) ?? '-' }}</td>
                                    <td>{{ $paymentNewspaper->tr_challan_verification_date?->toFormattedDateString() ?? '-' }}
                                    </td>
                                    <td>{{ $paymentNewspaper->challan_number }}</td>
                                    <td>{{ $paymentNewspaper->sbp_verification_date?->toFormattedDateString() ?? '-' }}
                                    </td>
                                    <td>
                                        @php
                                            $payment = $paymentNewspaper->payments->first(); // get first payment
                                            if ($payment) {
                                                $status = 'PARKED';
                                            } else {
                                                $status = 'UNPARKED';
                                            }

                                            $badgeClass =
                                                [
                                                    'PARKED' => 'bg-label-success',
                                                    'UNPARKED' => 'bg-label-danger',
                                                ][$status] ?? 'bg-secondary';

                                            $statusText =
                                                [
                                                    'PARKED' => 'Parked',
                                                    'UNPARKED' => 'Unparked',
                                                ][$status] ?? ucfirst(strtolower($status));
                                        @endphp

                                        <span class="badge {{ $badgeClass }} rounded-pill">{{ $statusText }}</span>
                                    </td>
                                    <td>
                                        <div class="action-item custom-tooltip">
                                            <a href="{{ route('payment.newspapers.receipt', $paymentNewspaper->id) }}">
                                                <i class='bx  bx-receipt fs-4 bx-icon'></i>
                                            </a>
                                            <span class="tooltip-text">Payments</span>
                                        </div>
                                    </td>
                                </tr>

                                {{-- modal popup start here --}}

                                {{-- modal popup ends here --}}
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-center p-2">
                        {{ $paymentNewspapers->links() }}
                    </div>
                </div>
            @else
                <div class="text-center text-muted py-4">No Legder to show</div>
            @endif
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush
@push('scripts')
    <script>
        flatpickr("#tr_challan_verification_date", {
            mode: "range",
            dateFormat: "d-m-Y",
            allowInput: true
        });

        flatpickr("#sbp_verification_date", {
            mode: "range",
            dateFormat: "d-m-Y",
            allowInput: true
        });

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

                // apply on load if there is prefilled search value
                if (input.value) applyLocalFilter(input.value);
            });
        })();
    </script>
@endpush

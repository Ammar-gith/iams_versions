@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-item-cneter">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5 mt-2">PLA Account</h5>
                    {{-- @if ($plaAcounts->isEmpty())
                        <span class="text-muted">No Bills to show</span>
                    @endif --}}
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
                    <a href="{{ route('pla-accounts.export.excel', request()->query()) }}" class="custom-excel-button me-2">
                        Export Excel</a>
                    <a href="{{ route('pla-accounts.export.pdf', request()->query()) }}" class="custom-pdf-button">Export
                        PDF</a>
                </div>

                <div class="inf-badge">
                    <span class="icon"><i class='bx bxs-purchase-tag'></i></span>
                    <span class="label">Total:</span>
                    <span class="number">Rs. {{ number_format(round($totalAmountPla)) }} /-</span>
                </div>
            </div>
            {{-- Advanced Filter Modal --}}
            <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-labelledby="advancedFilterModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET" action="{{ route('billings.treasury-challans.plaIndex') }}">
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
                                    {{-- challan number --}}
                                    <div class="col-md-6">
                                        <label for="challan_number" class="form-label">Challan Number</label>
                                        <input type="text" name="challan_number" id="challan_number" class="form-control"
                                            value="{{ request('challan_number') }}" placeholder="Enter challan number">
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
                                    {{-- challan number --}}
                                    <div class="col-md-6">
                                        <label for="cheque_number" class="form-label">Cheque Number</label>
                                        <input type="text" name="cheque_number" id="cheque_number" class="form-control"
                                            value="{{ request('cheque_number') }}" placeholder="Enter cheque number">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cheque Date</label>
                                        <input type="text" name="cheque_date" id="cheque_date" class="form-control"
                                            value="{{ request('cheque_date') }}" placeholder="Select Date Range">
                                    </div>
                                </div>

                                {{-- Hidden field to preserve global search if needed --}}
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            </div>
                            <div class="modal-footer">
                                <a href="{{ route('billings.treasury-challans.plaIndex') }}"
                                    class="btn btn-secondary">Reset</a>
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
            @if ($plaAcounts->isNotEmpty())
                <div class="table-responsive text-nowrap">
                    <table class="table w-100 js-local-filter-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>INF No.</th>
                                <th>Cheque Number</th>
                                <th>Cheque Date</th>
                                <th>Office</th>
                                {{-- <th>Newspapers Amount</th> --}}
                                <th>Challan NUmber</th>
                                <th>Amount Received</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0 js-local-filter-rows">
                            @foreach ($plaAcounts as $key => $plaAcount)
                                <tr>
                                    <td> {{ $plaAcounts->firstItem() + $key }}</td> <!-- Serial Number -->
                                    {{-- <td>{{ implode(', ', $plaAcount->inf_number ?? '') }}</td> --}}
                                    <td>
                                        {{-- @foreach ($plaAcount->plaAccountItems->inf_number ?? [] as $inf)
                                            {{ $inf }} <br>
                                        @endforeach --}}
                                        {{ $plaAcount->plaAccountItems->pluck('inf_number')->unique()->implode(',  ') }}.
                                    </td>
                                    <td>{{ $plaAcount->cheque_no ?? '' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($plaAcount->cheque_date)->toFormattedDateString() }}
                                    <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                        {{ \Illuminate\Support\Str::words($plaAcount->office->ddo_name ?? '', 500, '...') }}
                                    </td>
                                    <td>{{ $plaAcount->challan_no ?? '' }}</td>
                                    {{-- <td>{{ $plaAcount->newspapers_amount ?? '-' }}</td> --}}
                                    <td>{{ number_format(round($plaAcount->total_cheque_amount)) ?? '-' }}</td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('pla-accounts.plaView', $plaAcount->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View Ad</span>
                                            </div>


                                            {{-- <div class="action-item custom-tooltip">
                                            <a href="">
                                                <i class='bx bx-edit fs-4 bx-icon'></i>
                                                {{-- <span class="bx-icon test-sm">Newspaper Bills</span> --}
                                            </a>
                                            <span class="tooltip-text">Edit</span>
                                        </div> --}}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="p-3">
                        {{ $plaAcounts->links() }}
                    </div>
                </div>
            @else
                <div class="text-center text-muted py-4">No PLA Account to show</div>
            @endif
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush
@push('scripts')
    <script>
        flatpickr("#cheque_date", {
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

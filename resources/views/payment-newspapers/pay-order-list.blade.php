@extends('layouts.masterVertical')


@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="card mb-4">
            <div class="card-header col-md-12 d-flex justify-content-between gap-2 align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5-reset-margin">Pay Order List</h5>
                    @if (empty($bankWiseData))
                        <span class="text-muted">No Pay Order List to show</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    {{-- Global Search Form --}}
                    <label for="search" class="form-label mt-2">Search:</label>
                    <div class="input-group position-relative ">
                        <i class='bx bx-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted'
                            style="z-index: 5; pointer-events: none;"></i>
                        <input type="text" name="search"
                            class="form-control rounded-pill form-control-sm ps-4 js-local-search-input"
                            placeholder="Search..." value="{{ request('search') }}">

                    </div>
                    {{-- <button style="background: linear-gradient(135deg, #AAD9C9, #5DB698); border-style: none; color:black;"
                        type="button" class="btn btn-sm rounded-pill btn-primary" data-bs-toggle="modal"
                        data-bs-target="#advancedFilterModal"><i class='bx bx-search'></i> Advanced</button> --}}
                </div>
                <div class="d-flex justify-content-end">
                    <a href="{{ route('payment.newspapers.pay-order-list.export.excel', request()->query()) }}"
                        class="custom-excel-button me-2">Export Excel</a>
                    <a href="{{ route('payment.newspapers.pay-order-list.export.pdf', request()->query()) }}"
                        class="custom-pdf-button">Export PDF</a>
                </div>
            </div>

            <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET" action="{{ url()->current() }}" class="js-local-advanced-form">
                            <div class="modal-header">
                                <h5 class="modal-title">Advanced Filters</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3"><label class="form-label">INF Number</label><input type="text"
                                        name="inf_number" class="form-control" value="{{ request('inf_number') }}"></div>
                                <div class="mb-3"><label class="form-label">Batch No</label><input type="text"
                                        name="batch_no" class="form-control" value="{{ request('batch_no') }}"></div>
                                <div class="mb-3"><label class="form-label">Newspaper</label><select name="newspaper_id"
                                        class="form-select">
                                        <option value="">All Newspapers</option>
                                        @foreach ($newspapers ?? [] as $np)
                                            <option value="{{ $np->id }}"
                                                {{ request('newspaper_id') == $np->id ? 'selected' : '' }}>
                                                {{ $np->title }}</option>
                                        @endforeach
                                    </select></div>
                                <div class="mb-3"><label class="form-label">Submission Date</label><input type="text"
                                        name="submission_date" id="submission_date" class="form-control"
                                        value="{{ request('submission_date') }}" placeholder="dd-mm-yyyy to dd-mm-yyyy">
                                </div>
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            </div>
                            <div class="modal-footer">
                                <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>



            <div class="table-responsive">
                <table class="table table-bordered-stripped  mb-3 js-local-filter-table">
                    <thead>
                        <tr>
                            <th>Sr.No</th>


                            <th>Payee Name</th>
                            {{-- <th class="text-end">KPRA Tax (INF)</th>
                            <th class="text-end">KPRA Tax (Dept)</th>
                            <th class="text-end">IT Tax (INF)</th>
                            <th class="text-end">IT Tax (Dept)</th> --}}
                            <th class="text-end">Payable Amount</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $srNo = 1; @endphp

                        @foreach ($bankWiseData as $bankData)
                            @php $newspapers = $bankData['newspapers'] ?? []; @endphp

                            {{-- @foreach ($newspapers as $newspaper)
                                <tr>
                                    <td>{{ $srNo++ }}</td>
                                  <td class="fw-bold">
                                        {{ $newspaper['newspaper']->title ?? 'Unknown Newspaper' }}
                                    </td>
                                    <td>{{ $newspaper['bank_detail']->account_number ?? 'N/A' }}</td>
                                    <td class="">{{ $bankData['bank_name'] }}</td>
                                  <td class="text-end">{{ number_format($newspaper['totals']['kpra_inf']) }}</td>
                                    <td class="text-end">{{ number_format($newspaper['totals']['kpra_dept']) }}</td>
                                    <td class="text-end">{{ number_format($newspaper['totals']['it_inf']) }}</td>
                                    <td class="text-end">{{ number_format($newspaper['totals']['it_dept']) }}</td>
                                    <td class="text-end">{{ number_format($newspaper['totals']['payable']) }}/— Rs</td>
                                </tr>
                            @endforeach --}}

                            {{-- Bank Total Row --}}
                            <tr>
                                <td>{{ $srNo++ }}</td>

                                <td class="fw-bold">
                                    Manager {{ $bankData['bank_name'] }}
                                </td>
                                {{-- <td class="text-end">{{ number_format($bankData['totals']['kpra_inf']) }}</td>
                                <td class="text-end">{{ number_format($bankData['totals']['kpra_dept']) }}</td>
                                <td class="text-end">{{ number_format($bankData['totals']['it_inf']) }}</td>
                                <td class="text-end">{{ number_format($bankData['totals']['it_dept']) }}</td> --}}
                                <td class="fw-bold text-end text-primary">
                                    {{ number_format($bankData['totals']['payable']) }}
                                </td>
                                <td class="text-end">

                                    {{-- <div class="action-item custom-tooltip"> --}}
                                    <a class="btn btn-warning rounded-pill text-sm btn-xs"
                                        href="{{ route('payment.newspapers.viewGovCheque', ['bank_ids' => implode(',', $bankData['bank_ids'])]) }}">CHEQUE

                                        <span class="tooltip-text">Gov Cheques</span>
                                    </a>
                                    {{-- </div> --}}
                                </td>
                            </tr>

                            {{-- Agency amount (included in payable) --}}
                            {{-- @if (!empty($bankData['totals']['agency_payable']))
                                <tr class="table-light">
                                    <td></td>
                                    <td class="text-muted">Agency Total ({{ $bankData['bank_name'] }})</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($bankData['totals']['agency_payable']) }}
                                    </td>
                                    <td></td>
                                </tr>
                            @endif --}}

                            {{-- Empty row for separation between banks --}}
                            {{-- @if (!$loop->last)
                                <tr style="height: 15px; background-color: transparent;">
                                    <td colspan="3"></td>
                                </tr>
                            @endif --}}
                        @endforeach
                    </tbody>

                    {{-- Grand Total Footer --}}
                    <tfoot class="table-warning fw-bold">
                        @if (!empty($kpraTotal) && $kpraTotal > 0)
                            <tr class="table-light">
                                <td>{{ $srNo++ }}</td>

                                <td class="fw-bold">
                                    {{ $kpraPayee->description ?? 'KPRA' }}
                                    <div class="small text-muted">
                                        {{ $kpraPayee->bank_name ?? '' }}
                                        @if (!empty($kpraPayee?->account_number))
                                            — {{ $kpraPayee->account_number }}
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end text-primary">{{ number_format($kpraTotal) }}</td>
                                <td class="text-end">
                                    <a class="btn btn-warning rounded-pill text-sm btn-xs"
                                        href="{{ route('payment.newspapers.viewGovCheque', ['tax_type' => 'kpra']) }}">
                                        CHEQUE
                                    </a>
                                </td>
                            </tr>
                        @endif

                        @if (!empty($fbrTotal) && $fbrTotal > 0)
                            <tr class="table-light">
                                <td>{{ $srNo++ }}</td>

                                <td class="fw-bold">
                                    {{ $fbrPayee->description ?? 'FBR' }}
                                    <div class="small text-muted">
                                        {{ $fbrPayee->bank_name ?? '' }}
                                        @if (!empty($fbrPayee?->account_number))
                                            — {{ $fbrPayee->account_number }}
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end text-primary">{{ number_format($fbrTotal) }}</td>
                                <td class="text-end">
                                    <a class="btn btn-warning rounded-pill text-sm btn-xs"
                                        href="{{ route('payment.newspapers.viewGovCheque', ['tax_type' => 'fbr']) }}">
                                        CHEQUE
                                    </a>
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <td colspan="2" class="text-end">GRAND TOTAL (All Banks):</td>
                            {{-- <td class="text-end">{{ number_format($overallTotals['kpra_inf']) }}</td>
                            <td class="text-end">{{ number_format($overallTotals['kpra_dept']) }}</td>
                            <td class="text-end">{{ number_format($overallTotals['it_inf']) }}</td>
                            <td class="text-end">{{ number_format($overallTotals['it_dept']) }}</td> --}}
                            <td class="text-end text-primary">
                                {{ number_format($grandTotal ?? $overallTotals['payable']) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endpush

@push('styles')
    <style>
        .table-warning {
            background-color: #fff3cd !important;
        }

        tr[style*="height: 15px"] td {
            padding: 0;
            border: none;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function() {
            function getRowText(tr) {
                return (tr.innerText || tr.textContent || '').toLowerCase();
            }

            function applyLocalFilter(tableEl, filters) {
                const tbody = tableEl ? tableEl.querySelector('tbody') : null;
                if (!tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                const global = (filters.search || '').trim().toLowerCase();
                const inf = (filters.inf_number || '').trim().toLowerCase();
                const batch = (filters.batch_no || '').trim().toLowerCase();
                const newspaperText = (filters.newspaper_text || '').trim().toLowerCase();
                const dateText = (filters.submission_date || '').trim().toLowerCase();

                const isTotalRow = (tr) => tr.closest('tfoot') || tr.classList.contains('table-warning') || tr.classList
                    .contains('table-light');

                rows.forEach((tr) => {
                    if (isTotalRow(tr)) {
                        tr.style.display = '';
                        return;
                    }

                    const hay = getRowText(tr);
                    let ok = true;

                    if (global) ok = ok && hay.includes(global);
                    if (inf) ok = ok && hay.includes(inf);
                    if (batch) ok = ok && hay.includes(batch);
                    if (newspaperText) ok = ok && hay.includes(newspaperText);
                    if (dateText) ok = ok && hay.includes(dateText);

                    tr.style.display = ok ? '' : 'none';
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const tableEl = document.querySelector('.js-local-filter-table');
                const searchForm = document.querySelector('.js-local-search-form');
                const searchInput = document.querySelector('.js-local-search-input');
                const advForm = document.querySelector('.js-local-advanced-form');

                if (!tableEl || !searchInput) return;

                if (searchForm) {
                    searchForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        applyLocalFilter(tableEl, {
                            search: searchInput.value
                        });
                    });
                }

                searchInput.addEventListener('input', function() {
                    applyLocalFilter(tableEl, {
                        search: searchInput.value
                    });
                });

                if (advForm) {
                    advForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const inf = advForm.querySelector('[name="inf_number"]')?.value || '';
                        const batch = advForm.querySelector('[name="batch_no"]')?.value || '';
                        const submission = advForm.querySelector('[name="submission_date"]')?.value ||
                            '';
                        const npSelect = advForm.querySelector('[name="newspaper_id"]');
                        const npText = npSelect && npSelect.selectedOptions && npSelect.selectedOptions
                            .length ?
                            (npSelect.selectedOptions[0].textContent || '') :
                            '';
                        const global = advForm.querySelector('[name="search"]')?.value || searchInput
                            .value || '';

                        applyLocalFilter(tableEl, {
                            search: global,
                            inf_number: inf,
                            batch_no: batch,
                            newspaper_text: npText && npSelect && npSelect.value ? npText : '',
                            submission_date: submission
                        });

                        searchInput.value = global;

                        const modalEl = document.getElementById('advancedFilterModal');
                        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                            const instance = window.bootstrap.Modal.getInstance(modalEl) || new window
                                .bootstrap.Modal(modalEl);
                            instance.hide();
                        }
                    });
                }
            });
        })();
    </script>
@endpush

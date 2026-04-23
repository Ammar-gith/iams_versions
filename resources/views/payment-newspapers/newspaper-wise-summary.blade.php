{{-- old code --}}
@extends('layouts.masterVertical')
@push('style')
    <style>
        .rounded-left {
            border-top-left-radius: 50rem !important;
            border-bottom-left-radius: 50rem !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .rounded-right {
            border-top-right-radius: 50rem !important;
            border-bottom-right-radius: 50rem !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="card mb-4">
            <div class="card-header col-md-12 d-flex justify-content-between gap-2 align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5-reset-margin">Newspaper Wise Total Amount</h5>
                </div>

                {{-- Search & Advanced Filter --}}
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
                        data-bs-target="#advancedFilterModal">
                        <i class='bx bx-search'></i> Advanced
                    </button> --}}
                </div>
                <div class="d-flex justify-content-end">
                    <a href="{{ route('payment.newspapers.summary.export.excel', request()->query()) }}"
                        class="custom-excel-button me-2">Export Excel</a>
                    <a href="{{ route('payment.newspapers.summary.export.pdf', request()->query()) }}"
                        class="custom-pdf-button">Export PDF</a>
                </div>

            </div>
            {{-- Advanced Filter Modal --}}
            <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-labelledby="advancedFilterModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET" action="{{ url()->current() }}" class="js-local-advanced-form">
                            <div class="modal-header">
                                <h5 class="modal-title">Advanced Filters</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">INF Number</label>
                                    <input type="text" name="inf_number" class="form-control"
                                        value="{{ request('inf_number') }}" placeholder="e.g., 01/26">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Batch No</label>
                                    <input type="text" name="batch_no" class="form-control"
                                        value="{{ request('batch_no') }}" placeholder="e.g., Apr-2026-1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Newspaper</label>
                                    <select name="newspaper_id" class="form-select select2">
                                        <option value="">All Newspapers</option>
                                        @foreach ($newspapers ?? [] as $np)
                                            <option value="{{ $np->id }}"
                                                {{ request('newspaper_id') == $np->id ? 'selected' : '' }}>
                                                {{ $np->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Submission Date (Range)</label>
                                    <input type="text" name="submission_date" id="submission_date" class="form-control"
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
            @if (!$payments->isEmpty())
                <div class="table-responsive">
                    <table class="table table-bordered-stripped w-100 mb-3 js-local-filter-table">

                        {{-- HEADER ONLY ONCE --}}
                        <thead>
                            <tr>
                                <th>Sr.No</th>
                                <th>Media Name</th>
                                <th>Account No.</th>
                                <th>Bank Name</th>
                                <th class="text-end">KPRA Tax (INF)</th>
                                <th class="text-end">KPRA Tax (Dept)</th>
                                <th class="text-end">IT Tax (INF)</th>
                                <th class="text-end">IT Tax (Dept)</th>
                                <th class="text-end">Payable Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php $sr = 1; @endphp

                            @foreach ($payments as $newspaperId => $rows)
                                @php
                                    $totalPayable = 0;
                                    $totalKpraInf = 0;
                                    $totalKpraDept = 0;
                                    $totalItInf = 0;
                                    $totalItDept = 0;

                                    foreach ($rows as $row) {
                                        $totalPayable += $row->net_dues;
                                        $totalKpraInf += $row->kpra_inf;
                                        $totalKpraDept += $row->kpra_dept;
                                        $totalItInf += $row->it_inf;
                                        $totalItDept += $row->it_dept;
                                    }
                                @endphp

                                {{-- ONE ROW PER NEWSPAPER --}}
                                <tr>
                                    <td>{{ $sr++ }}</td>
                                    <td class="fw-bold">
                                        {{ $rows->first()->newspaper->title }}
                                    </td>
                                    <td>{{ $rows->first()->mediaBankDetail->account_number ?? '' }}</td>
                                    <td>{{ $rows->first()->mediaBankDetail->bank_name ?? '' }}</td>
                                    <td class="text-end">{{ number_format($totalKpraInf) }}</td>
                                    <td class="text-end">{{ number_format($totalKpraDept) }}</td>
                                    <td class="text-end">{{ number_format($totalItInf) }}</td>
                                    <td class="text-end">{{ number_format($totalItDept) }}</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($totalPayable) }}
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Agencies (show after newspapers) --}}
                            @if (isset($agencies) && !$agencies->isEmpty())
                                <tr class="table-light">
                                    <td colspan="9" class="fw-bold">Agency Summary</td>
                                </tr>

                                @foreach ($agencies as $agencyId => $rows)
                                    @php
                                        $totalPayable = 0;
                                        $totalKpraInf = 0;
                                        $totalKpraDept = 0;
                                        $totalItInf = 0;
                                        $totalItDept = 0;

                                        foreach ($rows as $row) {
                                            $totalPayable += $row->net_dues;
                                            $totalKpraInf += $row->kpra_inf;
                                            $totalKpraDept += $row->kpra_department;
                                            $totalItInf += $row->it_inf;
                                            $totalItDept += $row->it_department;
                                        }
                                    @endphp

                                    <tr>
                                        <td>{{ $sr++ }}</td>
                                        <td class="fw-bold">
                                            {{ $rows->first()->agency->name ?? 'Unknown Agency' }}
                                        </td>
                                        <td>{{ $rows->first()->mediaBankDetail->account_number ?? '' }}</td>
                                        <td>{{ $rows->first()->mediaBankDetail->bank_name ?? '' }}</td>
                                        <td class="text-end">{{ number_format($totalKpraInf) }}</td>
                                        <td class="text-end">{{ number_format($totalKpraDept) }}</td>
                                        <td class="text-end">{{ number_format($totalItInf) }}</td>
                                        <td class="text-end">{{ number_format($totalItDept) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($totalPayable) }}</td>
                                    </tr>
                                @endforeach
                            @endif

                            {{-- KPRA & FBR Totals (last rows) --}}
                            @php
                                $kpraInf = (float) ($kpraTotalInf ?? 0);
                                $kpraDept = (float) ($kpraTotalDept ?? 0);
                                $fbrInf = (float) ($fbrTotalInf ?? 0);
                                $fbrDept = (float) ($fbrTotalDept ?? 0);
                            @endphp

                            @if ($kpraInf + $kpraDept > 0)
                                <tr class="table-light">
                                    <td colspan="9" class="fw-bold">kpra and income tax summary</td>
                                </tr>
                                <tr class="table-warning fw-bold">
                                    <td>{{ $sr++ }}</td>
                                    <td>{{ $kpraPayee->description ?? 'KPRA' }}</td>
                                    <td>{{ $kpraPayee->account_number ?? '' }}</td>
                                    <td>{{ $kpraPayee->bank_name ?? '' }}</td>
                                    <td class="text-end">{{ number_format($kpraInf) }}</td>
                                    <td class="text-end">{{ number_format($kpraDept) }}</td>
                                    <td class="text-end">0</td>
                                    <td class="text-end">0</td>
                                    <td class="text-end">{{ number_format($kpraInf + $kpraDept) }}</td>
                                </tr>
                            @endif

                            @if ($fbrInf + $fbrDept > 0)
                                <tr class="table-warning fw-bold">
                                    <td>{{ $sr++ }}</td>
                                    <td>{{ $fbrPayee->description ?? 'FBR' }}</td>
                                    <td>{{ $fbrPayee->account_number ?? '' }}</td>
                                    <td>{{ $fbrPayee->bank_name ?? '' }}</td>
                                    <td class="text-end">0</td>
                                    <td class="text-end">0</td>
                                    <td class="text-end">{{ number_format($fbrInf) }}</td>
                                    <td class="text-end">{{ number_format($fbrDept) }}</td>
                                    <td class="text-end">{{ number_format($fbrInf + $fbrDept) }}</td>
                                </tr>
                            @endif
                        </tbody>

                    </table>
                </div>
            @else
                <div class="text-center text-muted py-4">No Summary to show</div>
            @endif
        </div>
    </div>
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

                rows.forEach((tr) => {
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

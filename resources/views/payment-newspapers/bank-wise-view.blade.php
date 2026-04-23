@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="card mb-4">
            <div class="card-header col-md-12 d-flex justify-content-between gap-2 align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5-reset-margin">Bank Wise Payment Summary</h5>
                    @if (empty($bankWiseData))
                        <span class="text-muted">No Bank Schedule to show</span>
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
                        data-bs-target="#advancedFilterModal">
                        <i class='bx bx-search'></i> Advanced
                    </button> --}}
                </div>
                <div class="d-flex justify-content-end">
                    <a href="{{ route('payment.newspapers.bank-name-wise.export.excel', request()->query()) }}"
                        class="custom-excel-button me-2">Export Excel</a>
                    <a href="{{ route('payment.newspapers.bank-name-wise.export.pdf', request()->query()) }}"
                        class="custom-pdf-button">Export PDF</a>
                </div>
            </div>

            {{-- Advanced Filter Modal --}}
            <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET" action="{{ url()->current() }}" class="js-local-advanced-form">
                            <div class="modal-header">
                                <h5 class="modal-title">Advanced Filters</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">INF Number</label>
                                    <input type="text" name="inf_number" class="form-control"
                                        value="{{ request('inf_number') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Batch No</label>
                                    <input type="text" name="batch_no" class="form-control"
                                        value="{{ request('batch_no') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Newspaper</label>
                                    <select name="newspaper_id" class="form-select">
                                        <option value="">All Newspapers</option>
                                        @foreach ($newspapers ?? [] as $np)
                                            <option value="{{ $np->id }}"
                                                {{ request('newspaper_id') == $np->id ? 'selected' : '' }}>
                                                {{ $np->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Submission Date</label>
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



            <div class="table-responsive">
                <table class="table table-bordered-stripped w-100 mb-3 js-local-filter-table">
                    <thead>
                        <tr>
                            <th>Sr.No</th>
                            <th>Media Name</th>
                            <th>Partner</th>
                            <th>Share %</th>
                            <th>Account No.</th>
                            <th>Account Title</th>
                            <th>Bank Name</th>
                            {{-- <th class="text-end">KPRA Tax (INF)</th>
                            <th class="text-end">KPRA Tax (Dept)</th>
                            <th class="text-end">IT Tax (INF)</th>
                            <th class="text-end">IT Tax (Dept)</th> --}}
                            <th class="text-end">Payable Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $srNo = 1; @endphp

                        @foreach ($bankWiseData as $bankData)
                            @php $newspapers = $bankData['newspapers'] ?? []; @endphp
                            @php $agencies = $bankData['agencies'] ?? []; @endphp

                            @foreach ($newspapers as $newspaper)
                                <tr>
                                    <td>{{ $srNo++ }}</td>
                                    <td class="fw-bold">
                                        {{ $newspaper['newspaper']->title ?? ($newspaper['bank_detail']->media_name ?? 'Unknown Newspaper') }}
                                    </td>
                                    <td>
                                        {{ $newspaper['partner_name'] ?? '—' }}
                                    </td>
                                    <td>
                                        @if (!empty($newspaper['share_percentage']))
                                            {{ rtrim(rtrim(number_format((float) $newspaper['share_percentage'], 2, '.', ''), '0'), '.') }}%
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $newspaper['bank_detail']->account_number ?? 'N/A' }}</td>
                                    <td>{{ $newspaper['bank_detail']->account_title ?? 'N/A' }}</td>
                                    <td class="">{{ $bankData['bank_name'] }}</td>
                                    {{-- <td class="text-end">{{ number_format($newspaper['totals']['kpra_inf']) }}</td>
                                    <td class="text-end">{{ number_format($newspaper['totals']['kpra_dept']) }}</td>
                                    <td class="text-end">{{ number_format($newspaper['totals']['it_inf']) }}</td>
                                    <td class="text-end">{{ number_format($newspaper['totals']['it_dept']) }}</td> --}}
                                    <td class="text-end">{{ number_format($newspaper['totals']['payable']) }}</td>
                                </tr>
                            @endforeach

                            {{-- Agencies (show after newspapers for same bank) --}}
                            @if (!empty($agencies))
                                <tr class="table-light">
                                    <td colspan="8" class="fw-bold">Agencies - {{ $bankData['bank_name'] }}</td>
                                </tr>
                                @foreach ($agencies as $agency)
                                    <tr>
                                        <td>{{ $srNo++ }}</td>
                                        <td class="fw-bold">
                                            {{ $agency['agency']->name ?? 'Unknown Agency' }}
                                        </td>
                                        <td>—</td>
                                        <td>—</td>
                                        <td>{{ $agency['bank_detail']->account_number ?? 'N/A' }}</td>
                                        <td>{{ $agency['bank_detail']->account_title ?? 'N/A' }}</td>
                                        <td class="">{{ $bankData['bank_name'] }}</td>
                                        <td class="text-end">{{ number_format($agency['totals']['payable']) }}</td>
                                    </tr>
                                @endforeach
                            @endif

                            {{-- Bank Total Row --}}
                            <tr class="table-warning fw-bold">
                                <td colspan="7" class="text-end">
                                    <i class='bx bx-calculator'></i>
                                    Total for {{ $bankData['bank_name'] }}:
                                </td>
                                {{-- <td class="text-end">{{ number_format($bankData['totals']['kpra_inf']) }}</td>
                                <td class="text-end">{{ number_format($bankData['totals']['kpra_dept']) }}</td>
                                <td class="text-end">{{ number_format($bankData['totals']['it_inf']) }}</td>
                                <td class="text-end">{{ number_format($bankData['totals']['it_dept']) }}</td> --}}
                                <td class="text-end text-primary">
                                    {{ number_format($bankData['totals']['payable']) }}
                                </td>
                            </tr>

                            {{-- Empty row for separation between banks --}}
                            {{-- @if (!$loop->last)
                                <tr style="height: 15px; background-color: transparent;">
                                    <td colspan="9"></td>
                                </tr>
                            @endif --}}
                        @endforeach
                    </tbody>

                    {{-- Grand Total Footer --}}
                    <tfoot class="table-light fw-bold">
                        @if (!empty($kpraTotal) && $kpraTotal > 0)
                            <tr class="table-light">
                                <td>{{ $srNo++ }}</td>

                                <td class="fw-bold">{{ $kpraPayee->description ?? 'KPRA' }}</td>
                                <td>—</td>
                                <td>—</td>
                                <td>{{ $kpraPayee->account_number ?? '' }}</td>
                                <td>{{ $kpraPayee->account_title ?? '' }}</td>
                                <td>{{ $kpraPayee->bank_name ?? '' }}</td>
                                <td class="text-end text-primary">{{ number_format($kpraTotal) }}</td>
                            </tr>
                        @endif

                        @if (!empty($fbrTotal) && $fbrTotal > 0)
                            <tr class="table-light">
                                <td>{{ $srNo++ }}</td>

                                <td class="fw-bold">{{ $fbrPayee->description ?? 'FBR' }}</td>
                                <td>—</td>
                                <td>—</td>
                                <td>{{ $fbrPayee->account_number ?? '' }}</td>
                                <td>{{ $fbrPayee->account_title ?? '' }}</td>
                                <td>{{ $fbrPayee->bank_name ?? '' }}</td>
                                <td class="text-end text-primary">{{ number_format($fbrTotal) }}</td>
                            </tr>
                        @endif

                        <tr class="table-warning fw-bold">
                            <td colspan="7" class="text-end">GRAND TOTAL (All Banks):</td>
                            {{-- <td class="text-end">{{ number_format($overallTotals['kpra_inf']) }}</td>
                            <td class="text-end">{{ number_format($overallTotals['kpra_dept']) }}</td>
                            <td class="text-end">{{ number_format($overallTotals['it_inf']) }}</td>
                            <td class="text-end">{{ number_format($overallTotals['it_dept']) }}</td> --}}
                            <td class="text-end text-primary">
                                {{ number_format($grandTotal ?? $overallTotals['payable']) }}
                            </td>
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

                const isTotalRow = (tr) => tr.classList.contains('table-warning') || tr.classList.contains(
                    'table-light');

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

                // Prevent server reload; do local filter instead
                if (searchForm) {
                    searchForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        applyLocalFilter(tableEl, {
                            search: searchInput.value
                        });
                    });
                }

                // Instant filter while typing
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

                        // keep header search in sync
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

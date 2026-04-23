@extends('layouts.masterVertical')


@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row ">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible mb-3" role="alert">
                <i class='bx bx-check-circle me-1'></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif



        @if ($history->isEmpty())
            <div class="alert alert-info">
                <i class='bx bx-info-circle me-1'></i> No payment records found yet.
            </div>
        @else
            {{-- Summary Cards --}}
            {{-- <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-muted small mb-1">Total Records</div>
                            <div class="fw-bold fs-5">{{ $history->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-muted small mb-1">Total Amount Paid (Rs)</div>
                            <div class="fw-bold fs-5 text-success">
                                {{ number_format($history->sum('amount'), 0) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-muted small mb-1">Total Bill Amount (Rs)</div>
                            <div class="fw-bold fs-5 text-primary">
                                {{ number_format($history->sum('amount'), 0) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            {{-- History Table --}}
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('payment.newspapers.paid-amount') }}" class="back-button">
                            <i class='bx bx-arrow-back'></i>
                        </a>
                        <h5 class="mb-0">Payment History</h5>
                        <span class="badge bg-label-primary">{{ $history->count() }} record(s)</span>
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

                        <button
                            style="background: linear-gradient(135deg, #AAD9C9, #5DB698); border-style: none; color:black;"
                            type="button" class="btn btn-sm rounded-pill btn-primary" data-bs-toggle="modal"
                            data-bs-target="#advancedFilterModal">
                            <i class='bx bx-search'></i> Advanced
                        </button>

                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('payment.newspapers.paid-amount.history.export.excel', request()->query()) }}"
                            class="custom-excel-button me-2">Export Excel</a>
                        <a href="{{ route('payment.newspapers.paid-amount.history.export.pdf', request()->query()) }}"
                            class="custom-pdf-button">Export PDF</a>
                    </div>
                    {{-- Advanced Filter Modal --}}
                    <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="GET" action="{{ url()->current() }}">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Advanced Filters</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Batch No</label>
                                                <input type="text" name="batch_no" class="form-control"
                                                    value="{{ request('batch_no') }}" placeholder="e.g., Apr-2026-1">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Payee Type</label>
                                                <select name="payee_type" class="form-select select2">
                                                    <option value="">All Types</option>
                                                    <option value="newspaper"
                                                        {{ request('payee_type') == 'newspaper' ? 'selected' : '' }}>
                                                        Newspaper
                                                    </option>
                                                    <option value="newspaper_partner"
                                                        {{ request('payee_type') == 'newspaper_partner' ? 'selected' : '' }}>
                                                        Newspaper Partner</option>
                                                    <option value="agency"
                                                        {{ request('payee_type') == 'agency' ? 'selected' : '' }}>Agency
                                                    </option>
                                                    <option value="kpra"
                                                        {{ request('payee_type') == 'kpra' ? 'selected' : '' }}>KPRA
                                                    </option>
                                                    <option value="fbr"
                                                        {{ request('payee_type') == 'fbr' ? 'selected' : '' }}>FBR</option>
                                                </select>
                                            </div>

                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select select2">
                                                    <option value="">All Statuses</option>
                                                    <option value="paid"
                                                        {{ request('status') == 'paid' ? 'selected' : '' }}>
                                                        Paid</option>
                                                    <option value="reversed"
                                                        {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed
                                                    </option>
                                                </select>
                                            </div>

                                            {{-- Banck name dropdown --}}
                                            <div class="col-md-6">
                                                <label class="form-label">Bank Name</label>
                                                <select name="bank_name" class="form-select select2">
                                                    <option value="">All Banks</option>
                                                    @foreach ($banks as $bankName)
                                                        <option value="{{ $bankName }}"
                                                            {{ request('bank_name') == $bankName ? 'selected' : '' }}>
                                                            {{ $bankName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            {{-- Newspaper name dropdown --}}
                                            <div class="col-md-6">
                                                <label class="form-label">Newspaper Name</label>
                                                <select name="newspaper_name" class="form-select select2">
                                                    <option value="">Select Newspaper</option>
                                                    @foreach ($newspapersName as $newspaperName)
                                                        <option value="{{ $newspaperName }}"
                                                            {{ request('newspaper_name') == $newspaperName ? 'selected' : '' }}>
                                                            {{ $newspaperName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            {{-- agency name dropdown --}}
                                            <div class="col-md-6">
                                                <label class="form-label">Adv.Agency Name</label>
                                                <select name="advAgency_name" class="form-select select2">
                                                    <option value="">Select adv.Agency</option>
                                                    @foreach ($advAgenceisName as $advAgencyName)
                                                        <option value="{{ $advAgencyName }}"
                                                            {{ request('advAgency_name') == $advAgencyName ? 'selected' : '' }}>
                                                            {{ $advAgencyName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">cheque No</label>
                                                <input type="text" name="cheque_no" class="form-control"
                                                    value="{{ request('cheque_no') }}" placeholder="cheque number">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Cheque Date</label>
                                                <input type="text" name="cheque_date" id="cheque_date"
                                                    class="form-control" value="{{ request('cheque_date') }}"
                                                    placeholder="DD-MM-YYYY">
                                            </div>
                                            {{-- <div class="col-md-6 mb-3">
                                                <label class="form-label">Submission Date</label>
                                                <input type="text" name="created_at" id="submission_date"
                                                    class="form-control" value="{{ request('submission_date') }}"
                                                    placeholder="DD-MM-YYYY">
                                            </div> --}}
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
                    {{-- <a href="{{ route('payment.newspapers.paid-amount') }}"
                        class="btn btn-outline-secondary btn-sm rounded-pill">
                        <i class='bx bx-list-check me-1'></i> Pending Batches
                    </a> --}}
                </div>


                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 js-local-filter-table" id="historyTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Batch No</th>
                                <th>Payee Name</th>
                                <th>Type</th>
                                <th>Bank</th>
                                <th>Cheque No</th>
                                <th class="text-end">Amount Paid (Rs)</th>
                                <th class="text-end">Total Amount (Rs)</th>
                                <th>Status</th>
                                <th>Cheque Date</th>
                            </tr>
                        </thead>
                        <tbody class="js-local-filter-rows">
                            @foreach ($history as $i => $entry)
                                @php
                                    $typeLabels = [
                                        'newspaper' => ['NP', 'bg-label-success'],
                                        'newspaper_partner' => ['NP partner', 'bg-label-success'],
                                        'agency' => ['Agency', 'bg-label-warning'],
                                        'kpra' => ['KPRA', 'bg-label-info'],
                                        'fbr' => ['FBR', 'bg-label-secondary'],
                                    ];
                                    [$label, $color] = $typeLabels[$entry->payee_type] ?? ['?', 'bg-label-dark'];
                                @endphp
                                <tr>
                                    <td class="">{{ $history->firstItem() + $i }}</td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ $entry->batch_no }}</span>
                                    </td>
                                    <td class="fw-semibold">{{ $entry->payee_name }}</td>
                                    <td><span class="badge {{ $color }}">{{ $label }}</span></td>
                                    <td>{{ optional($entry->mediaBankDetail)->bank_name ?? '—' }}</td>
                                    <td><code>{{ $entry->cheque_no ?? '—' }}</code></td>
                                    <td class="text-end fw-bold text-success">
                                        {{ $entry->amount !== null ? number_format($entry->amount, 0) : '—' }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($entry->amount, 0) }}
                                    </td>

                                    <td>
                                        <span
                                            class="badge {{ $entry->status === 'paid' ? 'bg-label-success' : 'bg-label-danger' }}">
                                            {{ $entry->status }}

                                        </span>
                                    </td>
                                    <td class="small">
                                        {{ $entry->created_at->format('d M Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-success fw-bold">
                            <tr>
                                <td colspan="6" class="text-end">Grand Total:</td>
                                <td class="text-end text-success">
                                    {{ number_format($history->sum('amount'), 0) }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($history->sum('amount'), 0) }}
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $history->links() }}
                </div>
            </div>
        @endif

    </div>
@endpush

@push('scripts')
    <script>
        flatpickr("#cheque_date", {
            mode: "range",
            dateFormat: "d-m-Y",
            allowInput: true
        });
        flatpickr("#submission_date", {
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

            function applyLocalFilter(query) {
                const q = normalize(query).trim();
                const tbody = document.querySelector('tbody.js-local-filter-rows');
                if (!tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.forEach((tr) => {
                    const hay = normalize(tr.innerText || tr.textContent || '');
                    tr.style.display = !q || hay.includes(q) ? '' : 'none';
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
@push('styles')
    <style>
        .table th {
            white-space: nowrap;
        }
    </style>
@endpush

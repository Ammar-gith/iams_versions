@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        {{-- ── Flash Messages ────────────────────────────────────────────── --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible mb-3" role="alert">
                <i class='bx bx-check-circle me-1'></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible mb-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- ── One card per pending batch ──────────────────────────────── --}}
        @forelse ($pendingBatches as $batchNo => $rows)
            @php
                $formId = 'form-' . Str::slug($batchNo);
                $tableId = 'tbl-' . Str::slug($batchNo);
                $totalId = 'total-' . Str::slug($batchNo);
                $batchIndex = $loop->index;
            @endphp

            <div class="card mb-4 js-runtime-batch-card gap-2">
                <div class="row card-header ">
                    <div class="col-md-12 d-flex align-items-center justify-content-between ">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                            <h5 class="mb-0">Pay Amount</h5>
                            {{-- @if (empty($pendingBatches))
                                <span class="text-muted ms-2">— All batches are fully paid</span>
                            @endif --}}
                        </div>

                        <div class="d-flex gap-2">
                            {{-- Global Search Form --}}

                            <label for="search" class="form-label mt-2">Search:</label>
                            <div class="input-group position-relative ">
                                <i class='bx bx-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted'
                                    style="z-index: 5; pointer-events: none;"></i>
                                <input type="text" name="search"
                                    class="form-control rounded-pill form-control-sm ps-4 js-runtime-search-input"
                                    placeholder="Search..." value="{{ request('search') }}">

                            </div>
                        </div>
                        {{-- <a href="{{ route('payment.newspapers.paid-amount.history') }}"
                            class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class='bx bx-history me-1'></i> View Payment
                        </a> --}}
                    </div>
                    {{-- </div> --}}
                    {{-- <div class="row"> --}}
                    <div class="col-md-12 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0">Batch</h6>
                            <span class="badge bg-label-primary fs-6">{{ $batchNo }}</span>
                            <span class="badge bg-label-warning">{{ count($rows) }} payee(s) pending</span>
                        </div>
                        <span class="fw-bold text-primary">
                            Total: Rs {{ number_format(array_sum(array_column($rows, 'amount')), 0) }}
                        </span>

                    </div>
                </div>

                <form method="POST" action="{{ route('payment.newspapers.paid-amount.store') }}" id="{{ $formId }}">
                    @csrf

                    <div class="table-responsive">
                        <table class="table mb-0 js-runtime-filter-table" id="{{ $tableId }}">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:36px">#</th>
                                    <th>Payee Name</th>
                                    <th>Type</th>
                                    <th>Bank Name</th>
                                    <th>Account No</th>
                                    <th class="text-end">Total Amount (Rs)</th>
                                    <th style="min-width:160px">Cheque No <span class="text-danger">*</span></th>
                                    <th style="min-width:160px">Cheque Date <span class="text-danger">*</span></th>
                                    {{-- <th style="min-width:150px">Amount Paid (Rs) <span class="text-danger">*</span></th> --}}
                                    <th style="width:48px"></th>
                                </tr>
                            </thead>
                            <tbody class="js-runtime-filter-rows">
                                @foreach ($rows as $i => $row)
                                    @php
                                        $typeLabels = [
                                            'newspaper' => ['NP', 'bg-label-success'],
                                            'newspaper_partner' => ['NP partner', 'bg-label-success'],
                                            'agency' => ['Agency', 'bg-label-warning'],
                                            'kpra' => ['KPRA', 'bg-label-info'],
                                            'fbr' => ['FBR', 'bg-label-secondary'],
                                        ];
                                        [$label, $color] = $typeLabels[$row['payee_type']] ?? ['?', 'bg-label-dark'];
                                    @endphp
                                    <tr>
                                        <td class="row-index">{{ $i + 1 }}</td>

                                        {{-- hidden fields --}}
                                        <input type="hidden" name="batches[{{ $batchIndex }}][batch_no]"
                                            value="{{ $batchNo }}">
                                        <input type="hidden"
                                            name="batches[{{ $batchIndex }}][rows][{{ $i }}][payee_id]"
                                            value="{{ $row['payee_id'] ?? '' }}">
                                        <input type="hidden"
                                            name="batches[{{ $batchIndex }}][rows][{{ $i }}][payee_type]"
                                            value="{{ $row['payee_type'] }}">
                                        <input type="hidden"
                                            name="batches[{{ $batchIndex }}][rows][{{ $i }}][ledger_batch_no]"
                                            value="{{ $row['ledger_batch_no'] ?? $batchNo }}">
                                        <input type="hidden"
                                            name="batches[{{ $batchIndex }}][rows][{{ $i }}][media_bank_detail_id]"
                                            value="{{ $row['media_bank_detail_id'] ?? '' }}">
                                        <input type="hidden"
                                            name="batches[{{ $batchIndex }}][rows][{{ $i }}][removed]"
                                            value="0">
                                        <input type="hidden"
                                            name="batches[{{ $batchIndex }}][rows][{{ $i }}][amount]"
                                            value="{{ $row['amount'] }}">

                                        <td class="fw-semibold">
                                            {{ $row['payee_name'] }}
                                            @if (!empty($row['carry_forward']))
                                                <span class="text-muted small d-block">(unpaid from batch
                                                    {{ $row['ledger_batch_no'] }})</span>
                                            @endif
                                        </td>
                                        <td><span class="badge {{ $color }}">{{ $label }}</span></td>
                                        <td>{{ $row['bank_name'] }}</td>
                                        <td class="">{{ $row['account_number'] ?: '—' }}</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($row['amount'], 0) }}
                                        </td>
                                        <td>
                                            <input type="text"
                                                name="batches[{{ $batchIndex }}][rows][{{ $i }}][cheque_no]"
                                                class="form-control form-control-sm" placeholder="Cheque / ref no" required>
                                        </td>
                                        <td>
                                            <input type="text" id="cheque_date"
                                                name="batches[{{ $batchIndex }}][rows][{{ $i }}][cheque_date]"
                                                class="form-control form-control-sm" placeholder="DD-MM-YYYY" required>
                                        </td>
                                        {{-- <td>
                                            <input type="number"
                                                name="batches[{{ $batchIndex }}][rows][{{ $i }}][paid_amount]"
                                                class="form-control form-control-sm" placeholder="0" min="0"
                                                step="0.01" required>
                                        </td> --}}
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-xs rounded-circle remove-row"
                                                title="Remove row">
                                                <i class='bx bx-x'></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot class="table-warning fw-bold">
                                <tr>
                                    <td colspan="5" class="text-end">Batch Total:</td>
                                    <td class="text-end text-primary" id="{{ $totalId }}">
                                        {{ number_format(array_sum(array_column($rows, 'amount')), 0) }}
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>


                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class='bx bx-info-circle'></i>
                            Rows removed with ❌ will NOT be saved.
                        </small>
                        <button type="submit" class="btn custom-primary-button rounded-pill px-4 save-btn"
                            data-table="{{ $tableId }}">
                            <i class='bx bx-save me-1'></i> Save Batch {{ $batchNo }}
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="alert alert-success">
                <i class='bx bx-check-circle me-1'></i>
                No record to found.
            </div>
        @endforelse


    </div>
@endpush

@push('styles')
    <style>
        .table-warning {
            background-color: #fff3cd !important;
        }

        .table th {
            white-space: nowrap;
        }

        .btn-xs {
            padding: .2rem .45rem;
            font-size: .75rem;
            line-height: 1;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ── Remove row & re-index ──────────────────────────────────────
            document.querySelectorAll('table[id^="tbl-"]').forEach(function(table) {

                table.addEventListener('click', function(e) {
                    const btn = e.target.closest('.remove-row');
                    if (!btn) return;

                    const row = btn.closest('tr');
                    // Mark as removed but keep it in the form so backend can persist it as "processed"
                    // (prevents the same row from re-appearing in other unpaid listings).
                    const removedInp = row.querySelector('input[name$="[removed]"]');
                    if (removedInp) removedInp.value = '1';

                    // Disable cheque fields so browser doesn't block submit
                    row.querySelectorAll('input[name$="[cheque_no]"], input[name$="[cheque_date]"]')
                        .forEach(function(inp) {
                            inp.required = false;
                            inp.disabled = true;
                        });

                    // Hide row from UI
                    row.style.display = 'none';

                    reIndex(table);
                    updateTotal(table);

                    // Disable save btn if no rows left
                    const form = table.closest('form');
                    const saveBtn = form?.querySelector('.save-btn');
                    if (saveBtn) saveBtn.disabled = table.querySelectorAll(
                        'tbody tr:not([style*=\"display: none\"])').length === 0;
                });
            });

            function reIndex(table) {
                table.querySelectorAll('tbody tr').forEach(function(tr, idx) {
                    const cell = tr.querySelector('.row-index');
                    if (cell) cell.textContent = idx + 1;

                    // Rename array indexes in input names
                    tr.querySelectorAll('input[name]').forEach(function(input) {
                        // Replace rows[OLD] with rows[idx]
                        input.name = input.name.replace(/\[rows\]\[\d+\]/, '[rows][' + idx + ']');
                    });
                });
            }

            function updateTotal(table) {
                let total = 0;
                table.querySelectorAll('tbody tr').forEach(function(tr) {
                    const removed = tr.querySelector('input[name$=\"[removed]\"]')?.value === '1';
                    if (removed) return;
                    const inp = tr.querySelector('input[name$=\"[amount]\"]');
                    total += parseFloat(inp?.value) || 0;
                });

                // Find the tfoot total cell
                const totalCell = table.querySelector('tfoot td.text-primary');
                if (totalCell) {
                    totalCell.textContent = total.toLocaleString('en-PK', {
                        maximumFractionDigits: 0
                    });
                }
            }

            // ── Validate rows before submit ────────────────────────────────
            document.querySelectorAll('form[id^="form-"]').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const tableId = form.querySelector('.save-btn')?.dataset.table;
                    const table = tableId ? document.getElementById(tableId) : null;

                    if (table && table.querySelectorAll('tbody tr').length === 0) {
                        e.preventDefault();
                        alert('Please keep at least one payee row before saving.');
                    }
                });
            });

            // ── Runtime search (filters rows while typing) ──────────────────
            function normalize(s) {
                return (s || '').toString().toLowerCase();
            }

            function isRemovedRow(tr) {
                return tr.querySelector('input[name$="[removed]"]')?.value === '1';
            }

            function applyRuntimeSearch(query) {
                const q = normalize(query).trim();
                document.querySelectorAll('.js-runtime-batch-card').forEach(function(card) {
                    const tbody = card.querySelector('tbody.js-runtime-filter-rows');
                    const rows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];

                    let anyVisible = false;
                    rows.forEach(function(tr) {
                        // keep removed rows hidden regardless of search
                        if (isRemovedRow(tr)) {
                            tr.style.display = 'none';
                            return;
                        }

                        const hay = normalize(tr.innerText || tr.textContent || '');
                        const show = !q || hay.includes(q);
                        tr.style.display = show ? '' : 'none';
                        if (show) anyVisible = true;
                    });

                    card.style.display = (!q || anyVisible) ? '' : 'none';
                });
            }

            const searchInput = document.querySelector('.js-runtime-search-input');
            const searchBtn = document.querySelector('.js-runtime-search-btn');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    applyRuntimeSearch(searchInput.value);
                });
            }
            if (searchBtn && searchInput) {
                searchBtn.addEventListener('click', function() {
                    applyRuntimeSearch(searchInput.value);
                });
            }
        });
    </script>
    <script>
        flatpickr("#cheque_date", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"
        });
    </script>
@endpush

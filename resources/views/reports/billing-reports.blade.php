@extends('layouts.masterVertical')

@push('style')
    <style>
        /* Icon box style (matching your theme) */
        .icon-box {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Card hover effect */
        .card.stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card.stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center mb-2">
                        <a href="{{ url()->previous() }}" class="back-button me-2"><i class='bx bx-arrow-back'></i></a>
                        <h5 class="mt-2">Billing Report</h5>
                    </div>
                    {{-- Export Buttons --}}
                    <div class="d-flex justify-content-end mb-1">
                        <a href="{{ route('reports.billing.export.excel') }}"
                            class="btn custom-excel-button rounded-pill btn-sm me-2">Export Excel</a>
                        <a href="{{ route('reports.billing.export.pdf') }}"
                            class="btn custom-pdf-button rounded-pill btn-sm">Export PDF</a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Filter Form (like reports.index) --}}
                    <form method="GET" action="{{ route('reports.billing') }}" class="row g-3 mb-4">
                        {{-- Search field --}}
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control rounded-pill"
                                placeholder="Search by invoice or INF..." value="{{ request('search') }}">
                        </div>

                        {{-- From date --}}
                        <div class="col-md-3">
                            <input type="date" name="from" class="form-control rounded-pill  date"
                                value="{{ request('from') }}" placeholder="From date">
                        </div>

                        {{-- To date --}}
                        <div class="col-md-3">
                            <input type="date" name="to" class="form-control rounded-pill date"
                                value="{{ request('to') }}" placeholder="To date">
                        </div>

                        {{-- Filter & Reset buttons --}}
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-success rounded-pill flex-fill">Filter</button>
                            <a href="{{ route('reports.billing') }}"
                                class="btn btn-outline-warning rounded-pill flex-fill">Reset</a>
                        </div>
                    </form>


                    {{-- SUMMARY CARDS (professional style) --}}
                    <div class="row g-3 mb-4">
                        {{-- Total Bills --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Bills</h6>
                                        <h4 class="fw-bold text-dark mb-0">{{ $totalBills }}</h4>
                                        <small class="text-muted">All Records</small>
                                    </div>
                                    <div class="icon-box bg-dark-subtle text-dark">
                                        <i class='bx bx-receipt fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Total Amount --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Amount (Rs.)</h6>
                                        <h4 class="fw-bold text-success mb-0">{{ number_format($totalAmount) }}</h4>
                                        <small class="text-muted">Sum of printed bills</small>
                                    </div>
                                    <div class="icon-box bg-success-subtle text-success">
                                        <i class='bx bx-wallet fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Average Bill --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Average Bill</h6>
                                        <h4 class="fw-bold text-info mb-0">{{ number_format($avgAmount) }}</h4>
                                        <small class="text-muted">Per bill average</small>
                                    </div>
                                    <div class="icon-box bg-info-subtle text-info">
                                        <i class='bx bx-stats fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Billed Count --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Billed</h6>
                                        <h4 class="fw-bold text-primary mb-0">{{ $statusCounts['billed'] ?? 0 }}</h4>
                                        <small class="text-muted">Successfully billed</small>
                                    </div>
                                    <div class="icon-box bg-primary-subtle text-primary">
                                        <i class='bx bxs-check-circle fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Unbilled Count --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Unbilled</h6>
                                        <h4 class="fw-bold text-warning mb-0">{{ $statusCounts['unbilled'] ?? 0 }}</h4>
                                        <small class="text-muted">Pending billing</small>
                                    </div>
                                    <div class="icon-box bg-warning-subtle text-warning">
                                        <i class='bx bx-time fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Date Range Applied --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Date Range</h6>
                                        <h5 class="fw-bold mb-0">
                                            @if ($created_from && $created_to)
                                                {{ \Carbon\Carbon::parse($created_from)->toFormattedDateString() }} -
                                                {{ \Carbon\Carbon::parse($created_to)->toFormattedDateString() }}
                                            @else
                                                All Records
                                            @endif
                                        </h5>
                                        <small class="text-muted">Applied filter</small>
                                    </div>
                                    <div class="icon-box bg-secondary-subtle text-secondary">
                                        <i class='bx bx-calendar fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive mt-3">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>INF No.</th>
                                    <th>Invoice No.</th>
                                    <th>Invoice Date</th>
                                    <th>Publication Date</th>
                                    <th>Printed Total Bill</th>
                                    <th>Newspaper</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($billings as $key => $billing)
                                    <tr>
                                        <td>{{ $billings->firstitem() + $loop->index }}</td>
                                        <td>{{ $billing->advertisement->inf_number ?? '' }}</td>
                                        <td>{{ $billing->invoice_no ?? '' }}</td>
                                        <td>{{ $billing->invoice_date ? \Carbon\Carbon::parse($billing->invoice_date)->toFormattedDateString() : '' }}
                                        </td>
                                        <td>{{ $billing->publication_date ? \Carbon\Carbon::parse($billing->publication_date)->toFormattedDateString() : '' }}
                                        </td>
                                        <td>{{ number_format($billing->printed_total_bill ?? 0) }}</td>
                                        <td>
                                            @if (!empty($billing->newspaper_titles))
                                                {{ implode(', ', $billing->newspaper_titles) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($billing->status == 'billed')
                                                <span
                                                    class="badge bg-label-primary rounded-pill">{{ $billing->status }}</span>
                                            @else
                                                <span
                                                    class="badge bg-label-warning rounded-pill">{{ $billing->status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $billing->created_at ? \Carbon\Carbon::parse($billing->created_at)->toFormattedDateString() : '' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No billing records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Pagination --}}
                <div class="card-footer">
                    {{ $billings->links() }}
                </div>

            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        // Initialize flatpickr on date inputs
        flatpickr(".date", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"
        });

        // If you're using Select2, initialize it inside the modal
        $(document).ready(function() {
            $('.select2').select2({
                dropdownParent: $('#advancedFilterModal')
            });
        });
    </script>
@endpush

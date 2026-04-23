@extends('layouts.masterVertical')
@push('style')
    <style>
        .icon-box {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Make the Select2 container rounded */
        .select2-container--default .select2-selection--single {
            border-radius: 50rem !important;
            /* Bootstrap's rounded-pill value */
            border: 1px solid #ced4da;
            height: calc(2.25rem + 2px);
            /* Match input height */
            padding: 0.05rem 0.15rem;
        }

        /* Adjust the arrow position */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 10px;
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="col-12">
            <div class="card">
                {{-- Card Header with title and back button --}}
                <div class="card-header  d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-end">
                        <a href="{{ url()->previous() }}" class="back-button me-3">
                            <i class='bx bx-arrow-back'></i>
                        </a>
                        <h5 class="mt-2">Newspaper Wise PLA Amount Report</h5>
                    </div>

                    {{-- Export Buttons --}}
                    <div class="d-flex justify-content-end mb-1">
                        <a href="{{ route('reports.newspaper.pla.export.excel') }}" class="custom-excel-button me-2">
                            Export Excel
                        </a>

                        <a href="{{ route('reports.newspaper.pla.export.pdf') }}" class="custom-pdf-button">
                            Export PDF
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('reports.newspaper.pla.amount') }}" class="row g-3 mb-4">


                        <div class="col-md-3">
                            <select name="newspaper_id" class="form-control select2">
                                <option value="">All Newspapers</option>
                                @foreach ($newspapers as $newspaper)
                                    <option value="{{ $newspaper->id }}"
                                        {{ $newspaper_id == $newspaper->id ? 'selected' : '' }}>
                                        {{ $newspaper->title }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- <input type="text" name="search" class="form-control" placeholder="Search by Newspaper"
                                value="{{ $search }}"> --}}
                        </div>

                        <div class="col-md-3">
                            <input type="date" name="from" id="fromDate" class="form-control rounded-pill"
                                value="{{ $from }}" placeholder="From date">
                        </div>

                        <div class="col-md-3">
                            <input type="date" name="to" id="toDate" class="form-control rounded-pill"
                                value="{{ $to }}" placeholder="To date">
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-success rounded-pill flex-fill">Filter</button>
                            <a href="{{ route('reports.newspaper.pla.amount') }}"
                                class="btn btn-outline-warning rounded-pill flex-fill">Reset</a>
                        </div>
                    </form>
                    <div class="row g-3 mb-4">

                        {{-- Total PLA Amount --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total PLA Amount</h6>
                                        <h4 class="fw-bold text-primary mb-0">
                                            {{ number_format($totalAmount) }}
                                        </h4>
                                        <small class="text-muted">All Newspapers</small>
                                    </div>

                                    <div class="icon-box bg-primary-subtle text-primary">
                                        <i class='bx bx-wallet fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Total Records --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Records</h6>
                                        <h4 class="fw-bold text-success mb-0">
                                            {{ $newspapersPlaAmount->total() }}
                                        </h4>
                                        <small class="text-muted">PLA Transactions</small>
                                    </div>

                                    <div class="icon-box bg-success-subtle text-success">
                                        <i class='bx bx-list-ul fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Date Range --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Date Range</h6>
                                        <h5 class="fw-bold mb-0">
                                            @if ($from && $to)
                                                {{ \Carbon\Carbon::parse($from)->toFormattedDateString() }} -
                                                {{ \Carbon\Carbon::parse($to)->toFormattedDateString() }}
                                            @else
                                                All Records
                                            @endif
                                        </h5>
                                        <small class="text-muted">Applied Filter</small>
                                    </div>

                                    <div class="icon-box bg-warning-subtle text-warning">
                                        <i class='bx bx-calendar fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Average Amount --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Average Amount</h6>
                                        <h4 class="fw-bold text-info mb-0">
                                            {{ $newspapersPlaAmount->count() > 0 ? number_format($totalAmount / $newspapersPlaAmount->count()) : 0 }}
                                        </h4>
                                        <small class="text-muted">Per Transaction</small>
                                    </div>

                                    <div class="icon-box bg-info-subtle text-info">
                                        <i class='bx bx-bar-chart-alt-2 fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    {{-- <div class="card mb-4">
                        <div class="card-header">
                            <h6>Newspaper Wise Totals</h6>
                        </div>

                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Newspaper</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($newspaperTotals as $total)
                                        <tr>
                                            <td>{{ $total->newspaper->title ?? '-' }}</td>
                                            <td>{{ number_format($total->total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div> --}}

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>S. No.</th>
                                    <th colspan="5">Newspaper Name</th>
                                    <th>Amount</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($newspapersPlaAmount as $item)
                                    <tr>
                                        <td>{{ $newspapersPlaAmount->firstItem() + $loop->index }}</td>
                                        <td colspan="5">
                                            {{ $item->newspaper->title }}
                                        </td>
                                        <td>{{ number_format($item->newspaper_amount) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->created_at ? $item->created_at : '')->toFormattedDateString() }}


                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}

                    <div class="mt-4">
                        {{ $newspapersPlaAmount->appends(request()->query())->links() }}
                    </div>

                </div> {{-- card-body --}}
            </div> {{-- card --}}
        </div> {{-- col-12 --}}
    </div> {{-- row --}}
@endpush

@push('scripts')
    <script>
        flatpickr("#fromDate", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"
        });

        flatpickr("#toDate", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"
        });
    </script>
@endpush

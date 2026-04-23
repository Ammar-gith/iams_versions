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
                {{-- Card Header --}}
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-end">
                        <a href="{{ url()->previous() }}" class="back-button me-3">
                            <i class='bx bx-arrow-back'></i>
                        </a>
                        <h5 class="mt-2">Office Wise Advertisement Report</h5>
                    </div>
                    <div class="d-flex justify-content-end mb-1">
                        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="custom-excel-button me-2">
                            Export Excel
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="custom-pdf-button">
                            Export PDF
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('reports.offices') }}" class="row g-3 mb-4">
                        {{-- Office Dropdown --}}
                        <div class="col-md-3">
                            <select name="office_id" class="form-control select2 rounded-pill">
                                <option value="">All Offices</option>
                                @foreach ($allOffices as $office)
                                    <option value="{{ $office->id }}" {{ $office_id == $office->id ? 'selected' : '' }}>
                                        {{ $office->ddo_name ?? $office->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date Filters --}}
                        <div class="col-md-3">
                            <input type="date" name="from" id="fromDate" class="form-control rounded-pill"
                                value="{{ $from ?? '' }}" placeholder="From date">
                        </div>

                        <div class="col-md-3">
                            <input type="date" name="to" id="toDate" class="form-control rounded-pill"
                                value="{{ $to ?? '' }}" placeholder="To date">
                        </div>

                        {{-- Buttons --}}
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-success rounded-pill flex-fill">Filter</button>
                            <a href="{{ route('reports.offices') }}"
                                class="btn btn-outline-warning rounded-pill flex-fill">Reset</a>
                        </div>
                    </form>

                    {{-- Summary Cards --}}
                    <div class="row g-3 mb-4">
                        {{-- Total Offices --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Offices</h6>
                                        <h4 class="fw-bold text-primary mb-0">
                                            {{ number_format(count($data)) }}
                                        </h4>
                                        <small class="text-muted">Departments / DDOs</small>
                                    </div>
                                    <div class="icon-box bg-primary-subtle text-primary">
                                        <i class='bx bx-building-house fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Total Advertisements --}}
                        @php $totalAds = collect($data)->sum('count'); @endphp
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Advertisements</h6>
                                        <h4 class="fw-bold text-success mb-0">
                                            {{ number_format($totalAds) }}
                                        </h4>
                                        <small class="text-muted">All Offices</small>
                                    </div>
                                    <div class="icon-box bg-success-subtle text-success">
                                        <i class='bx bx-file fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Average per Office --}}
                        @php $avg = count($data) > 0 ? round($totalAds / count($data)) : 0; @endphp
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Average per Office</h6>
                                        <h4 class="fw-bold text-info mb-0">
                                            {{ number_format($avg) }}
                                        </h4>
                                        <small class="text-muted">Ads / Office</small>
                                    </div>
                                    <div class="icon-box bg-info-subtle text-info">
                                        <i class='bx bx-bar-chart-alt-2 fs-2'></i>
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
                    </div>

                    {{-- Main Table --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">S. No.</th>
                                    <th width="70%">Office Name (DDO)</th>
                                    <th width="20%" class="text-end">Total Advertisements</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item['label'] }}</td>
                                        <td class="text-end">{{ number_format($item['count']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            No office records found for the selected filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (count($data))
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-end">Total</th>
                                        <th class="text-end">{{ number_format($totalAds) }}</th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

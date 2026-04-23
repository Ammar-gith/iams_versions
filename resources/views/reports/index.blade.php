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

        /* Card hover effect */
        .card.stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card.stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Tab container – remove default bottom border */
        .nav-tabs {
            border-bottom: 1px solid rgb(184, 221, 184);
            gap: 5px;
            /* spacing between tabs */
        }

        /* Base tab style */
        .nav-tabs .nav-link {
            color: #2c3e50;
            /* dark text */
            border: none;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            border-radius: 10px 1px 10px 0px;
            /* fully rounded */
            transition: all 0.3s ease;
            background: transparent;
        }

        /* Hover effect for inactive tabs */
        .nav-tabs .nav-link:not(.active):hover {
            background: rgba(40, 167, 69, 0.1);
            /* light green background */
            color: #1f5f2e;
        }

        /* Active tab – green gradient left to right */
        .nav-tabs .nav-link.active {
            background: rgba(5, 146, 38, 0.1);
            /* 10% opacity green */
            /* green to teal */
            color: #1b8534;
            border: none;
            box-shadow: 0 4px 12px rgba(75, 197, 103, 0.3);
            /* soft shadow */
            font-weight: 600;
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
                        <h5 class="mt-2">Status Wise Advertisement Report</h5>
                    </div>

                    {{-- Export Buttons --}}
                    <div class="d-flex justify-content-end mb-1">
                        <a href="{{ route('reports.export.excel', ['statusId' => $statusId, 'search' => $search, 'from' => $from, 'to' => $to]) }}"
                            class="custom-excel-button me-2">Export Excel</a>
                        <a href="{{ route('reports.export.pdf', ['statusId' => $statusId, 'search' => $search, 'from' => $from, 'to' => $to]) }}"
                            class="custom-pdf-button">Export PDF</a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('reports.index') }}" class="row g-3 mb-4">
                        <input type="hidden" name="status_id" value="{{ $statusId }}">

                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control rounded-pill"
                                placeholder="Search by INF or client" value="{{ $search }}">
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
                            <a href="{{ route('reports.index', ['status_id' => $statusId]) }}"
                                class="btn btn-outline-warning rounded-pill flex-fill">Reset</a>
                        </div>
                    </form>
                    <div class="row g-3 mb-4">

                        {{-- Total Advertisements --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Advertisements</h6>
                                        <h4 class="fw-bold text-indigo mb-0">
                                            {{ array_sum($statusCounts) }}
                                        </h4>
                                        <small class="text-muted">All Statuses</small>
                                    </div>

                                    <div class="icon-box bg-indigo-subtle text-indigo">
                                        <i class='bx bx-file fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- new  --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">New Advertisements</h6>
                                        <h4 class="fw-bold text-success mb-0">
                                            {{ $statusCounts[3] ?? 0 }}
                                        </h4>
                                        <small class="text-muted">Recently Submitted</small>
                                    </div>

                                    <div class="icon-box bg-success-subtle text-success">
                                        <i class='bx bx-plus-circle fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- inprogress --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">In Progress</h6>
                                        <h4 class="fw-bold text-warning mb-0">
                                            {{ $statusCounts[4] ?? 0 }}
                                        </h4>
                                        <small class="text-muted">Under Processing</small>
                                    </div>

                                    <div class="icon-box bg-warning-subtle text-warning">
                                        <i class="bx bx-loader bx-spin fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Approved --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Approved Ads</h6>
                                        <h4 class="fw-bold text-primary mb-0">
                                            {{ $statusCounts[10] ?? 0 }}
                                        </h4>
                                        <small class="text-muted">Approved by DG</small>
                                    </div>

                                    <div class="icon-box bg-primary-subtle text-primary">
                                        <i class="bx bxs-check-circle fs-2 bx-tada"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Released --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Released Ads</h6>
                                        <h4 class="fw-bold text-info mb-0">
                                            {{ $statusCounts[8] ?? 0 }}
                                        </h4>
                                        <small class="text-muted">Sent to Newspapers</small>
                                    </div>

                                    <div class="icon-box bg-info-subtle text-info">
                                        <i class='bx bx-send bx-fade-right fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Rejected Ads --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Rejected Ads</h6>
                                        <h4 class="fw-bold text-danger mb-0">
                                            {{ $statusCounts[7] ?? 0 }}
                                        </h4>
                                        <small class="text-muted">Rejected Requests</small>
                                    </div>

                                    <div class="icon-box bg-danger-subtle text-danger">
                                        <i class='bx bx-x-circle bx-burst fs-2'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Date Range --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="card stat-card shadow-sm border-0 h-100">
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
                    {{-- Tabs for statuses --}}
                    <ul class="nav nav-tabs mb-3">
                        @foreach ($statuses as $id => $label)
                            <li class="nav-item">
                                <a class="nav-link {{ $statusId == $id ? 'active' : '' }}"
                                    href="{{ route('reports.index', ['status_id' => $id]) }}">
                                    {{ $label }} ({{ $statusCounts[$id] ?? 0 }})
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>S. No.</th>
                                    <th>INF No.</th>
                                    <th>Office/Department</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ads as $ad)
                                    <tr>
                                        <td>{{ $ads->firstItem() + $loop->index }}</td>
                                        <td>{{ $ad->inf_number }}</td>
                                        <td>
                                            @if ($ad->office_id)
                                                {{ $ad->office->ddo_name ?? '' }}
                                            @elseif ($ad->department_id)
                                                {{ $ad->department->name ?? '' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match ($ad->status_id) {
                                                    3 => 'bg-label-success',
                                                    4 => 'bg-label-warning',
                                                    10 => 'bg-label-primary',
                                                    8 => 'bg-label-dark',
                                                    7 => 'bg-label-danger',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge rounded-pill {{ $statusClass }}">
                                                {{ $statuses[$ad->status_id] ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>{{ $ad->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No advertisements found for {{ $statuses[$statusId] ?? 'selected' }} status.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($ads instanceof \Illuminate\Pagination\LengthAwarePaginator && $ads->hasPages())
                        <div class="mt-4">
                            {{ $ads->appends(request()->query())->links() }}
                        </div>
                    @endif
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

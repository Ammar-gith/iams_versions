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

        .search-input-wrap {
            position: relative;
            min-width: 250px;
        }

        .search-input-wrap .search-icon {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
        }

        .search-input-wrap input.form-control {
            padding-left: 32px;
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="{{ url()->previous() }}" class="back-button me-2"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="mb-0">Advertisements List</h5>
                </div>
                <div class="d-flex gap-2">
                    {{-- Global Search Form --}}
                    <form method="GET" action="{{ route('advertisements.index') }}" class="d-flex js-local-search-form">
                        <div class="search-input-wrap">
                            <i class='bx bx-search search-icon'></i>
                            <input type="text" name="search"
                                class="form-control form-control-sm rounded-pill js-local-search-input"
                                placeholder="Search..." value="{{ request('search') }}">
                        </div>
                    </form>
                    {{-- Advanced Filter Button --}}
                    <button style=" background: linear-gradient(135deg, #AAD9C9, #5DB698); border-style: none; color:black;"
                        type="button" class="btn btn-sm rounded-pill btn-primary" data-bs-toggle="modal"
                        data-bs-target="#advancedFilterModal">
                        <i class='bx bx-search'></i> Advanced
                    </button>
                </div>

                {{-- Export Buttons --}}
                <div class="d-flex justify-content-end mb-1">
                    <a href="{{ route('advertisements.export.excel', request()->query()) }}"
                        class="custom-excel-button me-2">Export Excel</a>
                    <a href="{{ route('advertisements.export.pdf', request()->query()) }}" class="custom-pdf-button">Export
                        PDF</a>
                </div>
            </div>

            {{-- Advanced Filter Modal --}}
            <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-labelledby="advancedFilterModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET" action="{{ route('advertisements.index') }}">
                            <div class="modal-header">
                                <h5 class="modal-title" id="advancedFilterModalLabel">Advanced Filters</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                {{-- inf Number --}}
                                <div class="mb-3">
                                    <label for="inf-number" class="form-label">Inf Number</label>
                                    <input type="text" name="inf_number" id="inf_number" class="form-control"
                                        placeholder="inf number" value="{{ request('inf_number') }}">
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
                                <div class="mb-3">
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
                                </div>

                                {{-- Date Range --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Publication Date</label>
                                        <input type="text" name="publication_date" id="publication_date"
                                            class="form-control" value="{{ request('publication_date') }}"
                                            placeholder="Select Date Range">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Submission Date</label>
                                        <input type="text" name="submission_date" id="submission_date"
                                            class="form-control" value="{{ request('submission_date') }}"
                                            placeholder="Select Date Range">
                                    </div>
                                </div>
                                {{-- Hidden field to preserve global search if needed --}}
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            </div>
                            <div class="modal-footer">
                                <a href="{{ route('advertisements.index') }}" class="btn btn-secondary">Reset</a>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Get the authenticated logged in user --}}
            @php
                $user = Auth::User();
            @endphp


            {{-- Show ads if any --}}
            @if ($advertisements->isNotEmpty())
                <div class="table-responsive ">
                    <table class="table w-100 js-local-filter-table">
                        <thead>
                            <tr>
                                <th style="padding-right: 0 !important;">S. No.</th>
                                @if (
                                    $user->hasRole([
                                        'Superintendent',
                                        'Diary Dispatch',
                                        'Super Admin',
                                        'Deputy Director',
                                        'Director General',
                                        'Secretary',
                                    ]))
                                    @can('view inf number')
                                        <th>INF No.</th>
                                    @endcan
                                @endif
                                @if ($is_department_user)
                                    <th>Department</th>
                                @endif
                                @if ($is_office_user)
                                    <th>Office</th>
                                @endif
                                @if (
                                    $user->hasRole([
                                        'Superintendent',
                                        'Diary Dispatch',
                                        'Super Admin',
                                        'Deputy Director',
                                        'Director General',
                                        'Secretary',
                                        'Media',
                                    ]))
                                    <th>Department / Office</th>
                                @endif
                                <th>Submission Date</th>
                                <th>Publication Date</th>
                                {{-- <th>Submitted by</th> --}}
                                @if (!$user->hasRole('Media'))
                                    <th class="text-center">Status</th>
                                @endif
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0 js-local-filter-rows">
                            @foreach ($advertisements as $key => $advertisement)
                                <tr>
                                    <td>{{ $advertisements->firstItem() + $loop->index }}</td> <!-- Serial Number -->
                                    @if (
                                        $user->hasRole([
                                            'Superintendent',
                                            'Diary Dispatch',
                                            'Super Admin',
                                            'Deputy Director',
                                            'Director General',
                                            'Secretary',
                                        ]))
                                        <td>{{ $advertisement->inf_number }}</td>
                                    @endif
                                    @if (
                                        $is_department_user ||
                                            ($user->hasRole([
                                                'Superintendent',
                                                'Diary Dispatch',
                                                'Super Admin',
                                                'Deputy Director',
                                                'Director General',
                                                'Secretary',
                                                'Media',
                                            ]) &&
                                                !$advertisement->office_id))
                                        <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                            {{ \Illuminate\Support\Str::words($advertisement->department->name ?? '-', 5, '...') }}
                                        </td>
                                    @elseif(
                                        $is_office_user ||
                                            ($user->hasRole([
                                                'Superintendent',
                                                'Diary Dispatch',
                                                'Super Admin',
                                                'Deputy Director',
                                                'Director General',
                                                'Secretary',
                                                'Media',
                                            ]) &&
                                                $advertisement->office_id))
                                        <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                            {{ \Illuminate\Support\Str::words($advertisement->office->ddo_name ?? '-', 5, '...') }}
                                        </td>
                                    @endif
                                    <td>{{ \Carbon\Carbon::parse($advertisement->created_at)->toFormattedDateString() }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($advertisement->publish_on_or_before)->toFormattedDateString() }}
                                    </td>
                                    {{-- <td>{{ $advertisement->user->name }}</td> --}}
                                    @php
                                        $statusClasses = [
                                            'New' => 'bg-label-success',
                                            'Approved' => 'bg-label-primary',
                                            'forwarded by DD' => 'bg-label-warning',
                                            'Rejected' => 'bg-label-danger',
                                            'Draft' => 'bg-label-label-secondary',
                                            'In progress' => 'bg-label-warning',
                                        ];
                                        $class = $statusClasses[$advertisement->status->title] ?? 'bg-secondary';

                                        $forwardedBy = $advertisement->forwarded_by_role_id;
                                        $forwardedTo = $advertisement->forwarded_to_role_id;

                                    @endphp
                                    @if (!$user->hasRole('Media'))
                                        <td class="text-center align-middle">
                                            @if ($user->hasRole('Superintendent') || ($user->hasRole('Client Office') && $forwardedTo == 3))
                                                <span class="badge rounded-pill  {{ $class }}">New</span>
                                            @elseif ($user->hasRole('Deputy Director') && $advertisement->status->title == 'In progress')
                                                <span class="badge rounded-pill bg-label-info">In progress / DG
                                                    Approval</span>
                                            @elseif ($user->hasRole('Director General') && $advertisement->status->title == 'In progress')
                                                <span class="badge rounded-pill bg-label-info">forwarded by DD</span>
                                            @elseif ($user->hasRole('Secretary') && $advertisement->status->title == 'In progress')
                                                <span class="badge rounded-pill bg-label-info">forwarded by DG</span>
                                            @elseif ($user->hasRole('Client Office') && $user->office_id == null)
                                                <span class="badge rounded-pill bg-label-success">Pending for dept.
                                                    Approval</span>
                                            @elseif ($user->hasRole('Client Office') && $user->office_id != null)
                                                <span class="badge rounded-pill bg-label-success">Sent for dept.
                                                    Approval</span>
                                            @else
                                                @if (!$user->hasRole('Media'))
                                                    <span
                                                        class="badge rounded-pill {{ $class }}">{{ $advertisement->status->title ?? '' }}</span>
                                                @endif
                                            @endif
                                        </td>
                                    @endif
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- send back to office for correction  --}}
                                            <div class="action-item custom-tooltip d-flex">
                                                @if ($is_department_user && $advertisement->status->title == 'Pending Department Approval')
                                                    <form
                                                        action="{{ route('advertisements.update', $advertisement->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn p-0 border-0 bg-transparent"
                                                            name="action" value="department_send_back">
                                                            <i class='bx bx-left-down-arrow-circle bx-icon'></i>
                                                        </button>
                                                        <span class="tooltip-text">Send back to Office</span>
                                                    </form>
                                                @endif
                                            </div>


                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('advertisements.show', $advertisement->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View Ad</span>
                                            </div>

                                            {{-- Edit (process) --}}
                                            @can('view process action')
                                                <div class="action-item custom-tooltip">
                                                    <a href="{{ route('advertisements.edit', $advertisement->id) }}">
                                                        <i class='bx bx-edit-alt fs-4 bx-icon'></i>
                                                    </a>
                                                    <span class="tooltip-text">Edit Ad</span>
                                                </div>
                                            @endcan

                                            {{-- Forward ads to Client ipr department --}}
                                            <div class="action-item custom-tooltip d-flex">
                                                @if ($is_department_user && $advertisement->status->title == 'Pending Department Approval')
                                                    <form
                                                        action="{{ route('advertisements.update', $advertisement->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn p-0 border-0 bg-transparent"
                                                            name="action" value="department_approve">
                                                            <i class='bx bx-right-top-arrow-circle bx-icon'></i>
                                                        </button>
                                                        <span class="tooltip-text">approve & forwarded to ipr dept.</span>
                                                    </form>
                                                @endif
                                            </div>



                                            {{-- Edit (media form) --}}
                                            @can('view media form edit action')
                                                @if ($user->adv_agency_id != null)
                                                    <div class="action-item custom-tooltip">
                                                        <a href="{{ route('billings.agencies.create', $advertisement->id) }}">
                                                            <i class='bx bx-receipt fs-4 bx-icon'></i>
                                                        </a>
                                                        <span class="tooltip-text">Agency Bill</span>
                                                    </div>
                                                @else
                                                    <div class="action-item custom-tooltip">
                                                        <a
                                                            href="{{ route('billings.newspapers.create', $advertisement->id) }}">
                                                            <i class='bx bx-receipt fs-4 bx-icon'></i>
                                                        </a>
                                                        <span class="tooltip-text">Generate Bill</span>
                                                    </div>
                                                @endif
                                            @endcan

                                            {{-- Track --}}
                                            @can('Track Ad Btn')
                                                <div class="action-item custom-tooltip">
                                                    <a type="button" data-bs-toggle="modal"
                                                        data-bs-target="#trackAdModal{{ $advertisement->id }}">
                                                        <i class="bx bx-trip fs-4 bx-icon"></i>

                                                    </a>
                                                    <span class="tooltip-text">Track Ad</span>
                                                </div>
                                            @endcan

                                            {{-- Tracking Modal --}}
                                            <div class="modal fade" id="trackAdModal{{ $advertisement->id }}"
                                                tabindex="-1" aria-labelledby="trackAdModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header"
                                                            style="padding: .8rem 1.8rem; border-bottom: .13rem solid #e7e7e7;">
                                                            <h5 class="modal-title" style="color: var(--dark-text);">Ad
                                                                Change Timeline</h5>
                                                            <div class="action-buttons d-flex gap-2">
                                                                <div class="y-hover">
                                                                    <a href="javascript:void(0);"
                                                                        class="text-decoration-none">
                                                                        <i
                                                                            class='bx bx-share-alt bx-modal-icons button-y'></i>
                                                                    </a>
                                                                </div>
                                                                <div class="x-hover">
                                                                    <button type="button" class="button-x"
                                                                        data-bs-dismiss="modal"><i
                                                                            class='bx bx-x bx-modal-icons'></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="timeline-container">
                                                                @php
                                                                    // Separate workflow actions from field changes
                                                                    $workflowLogs = $advertisement->changeLogs
                                                                        ->whereNotNull('action')
                                                                        ->sortBy('changed_at');
                                                                    $fieldChangeLogs = $advertisement->changeLogs
                                                                        ->whereNull('action')
                                                                        ->sortBy('changed_at');

                                                                    // Combine and sort all logs by date
                                                                    $allLogs = $advertisement->changeLogs->sortBy(
                                                                        'changed_at',
                                                                    );

                                                                    // Group logs by Date and Role
                                                                    $logsByDate = $allLogs->groupBy(function ($log) {
                                                                        return $log->role .
                                                                            '_' .
                                                                            \Carbon\Carbon::parse(
                                                                                $log->changed_at,
                                                                            )->format('Y-m-d');
                                                                    });
                                                                @endphp

                                                                @forelse($logsByDate as $group => $logs)
                                                                    @php
                                                                        [$role, $date] = explode('_', $group);
                                                                        // Separate workflow actions and field changes for this group
                                                                        $groupWorkflowLogs = $logs->whereNotNull(
                                                                            'action',
                                                                        );
                                                                        $groupFieldLogs = $logs->whereNull('action');
                                                                    @endphp

                                                                    {{-- Date box --}}
                                                                    <div class="timeline-date">
                                                                        <div class="date-box">
                                                                            <div class="day">
                                                                                {{ \Carbon\Carbon::parse($date)->format('d') }}
                                                                                {{ strtoupper(\Carbon\Carbon::parse($date)->format('M')) }},
                                                                                {{ \Carbon\Carbon::parse($date)->format('Y') }}
                                                                            </div>
                                                                            <div class="month">
                                                                            </div>
                                                                            <div class="year">
                                                                                {{ strtoupper(\Carbon\Carbon::parse($date)->format('D')) }}
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    {{-- Role/user header --}}
                                                                    <div class="timeline-day-content">
                                                                        <div
                                                                            style="background-color: #E3F2ED; padding: 1rem; margin-bottom: 1rem; border-radius: 6px;">
                                                                            <div
                                                                                class="user-info mb-2 d-flex align-items-center justify-content-between">
                                                                                <div class="d-flex align-items-center">
                                                                                    <img src="{{ asset('assets/img/avatars/1.png') }}"
                                                                                        class="rounded-circle me-2"
                                                                                        width="40" height="40">
                                                                                    <div>
                                                                                        <strong>{{ $logs->first()->user->name }}</strong><br>
                                                                                        <small
                                                                                            class="text-muted">{{ $role }}</small>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            {{-- Workflow Actions --}}
                                                                            @foreach ($groupWorkflowLogs as $log)
                                                                                <div class="change-card bg-white shadow-sm rounded mb-2"
                                                                                    style="padding: .5rem; border-left: 4px solid #0d6efd;">
                                                                                    <div
                                                                                        class="d-flex justify-content-between align-items-center">
                                                                                        <span class="badge bg-primary">
                                                                                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                                                                        </span>
                                                                                        <span>{{ \Carbon\Carbon::parse($log->changed_at)->format('h:i A') }}</span>
                                                                                    </div>
                                                                                    @if ($log->assigned_to_id && $log->assignedTo)
                                                                                        <div class="mt-1">
                                                                                            <strong>Assigned to:</strong>
                                                                                            {{ $log->assignedTo->name }}
                                                                                        </div>
                                                                                    @endif
                                                                                    @if ($log->from_status && $log->to_status)
                                                                                        <div class="mt-1">
                                                                                            <strong>Status:</strong>
                                                                                            {{ getStatusName($log->from_status) }}
                                                                                            →
                                                                                            {{ getStatusName($log->to_status) }}
                                                                                        </div>
                                                                                    @endif
                                                                                    @if ($log->metadata && is_array($log->metadata))
                                                                                        @if (isset($log->metadata['inf_number']))
                                                                                            <div class="mt-1">
                                                                                                <strong>INF #:</strong>
                                                                                                {{ $log->metadata['inf_number'] }}
                                                                                            </div>
                                                                                        @endif
                                                                                        @if (isset($log->metadata['rejection_reasons']))
                                                                                            <div class="mt-1">
                                                                                                <strong>Reasons:</strong>
                                                                                                {{ is_array($log->metadata['rejection_reasons']) ? implode(', ', $log->metadata['rejection_reasons']) : $log->metadata['rejection_reasons'] }}
                                                                                            </div>
                                                                                        @endif
                                                                                    @endif
                                                                                    @if ($log->comments)
                                                                                        <div class="mt-1 text-muted">
                                                                                            <strong>Comments:</strong>
                                                                                            {{ $log->comments }}
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            @endforeach

                                                                            {{-- Field Changes --}}
                                                                            @foreach ($groupFieldLogs as $log)
                                                                                @if ($log->field)
                                                                                    <div class="change-card bg-white shadow-sm rounded mb-2"
                                                                                        style="padding: .5rem">
                                                                                        <div
                                                                                            class="d-flex justify-content-between align-items-center">
                                                                                            <span
                                                                                                class="badge bg-danger">{{ fieldLabel($log->field) }}
                                                                                                changed</span>
                                                                                            <span>{{ \Carbon\Carbon::parse($log->changed_at)->format('h:i A') }}</span>
                                                                                        </div>
                                                                                        <div
                                                                                            class="d-flex justify-content-start align-items-center">
                                                                                            <span
                                                                                                class="text-muted ms-1">From:
                                                                                            </span>
                                                                                            <span
                                                                                                class="ms-1">{{ displayValue($log->field, $log->old_value) }}</span>
                                                                                            <i
                                                                                                class="bx bx-right-arrow-alt scaleX-n1-rtl mx-2"></i>
                                                                                            <span
                                                                                                class="text-muted ms-1">To:
                                                                                            </span>
                                                                                            <span
                                                                                                class="ms-1">{{ displayValue($log->field, $log->new_value) }}</span>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                            @endforeach

                                                                        </div>
                                                                    </div>
                                                                @empty
                                                                    <p class="text-muted">No changes found.</p>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($advertisements instanceof \Illuminate\Pagination\LengthAwarePaginator && $advertisements->hasPages())
                    <div class="mt-2 p-2">
                        {{ $advertisements->appends(request()->query())->links() }}
                    </div>
                @endif
        </div>
    @else
        <div class="text-center text-muted py-4">No ads to show</div>
        @endif
    </div>
    {{-- ! / Page Content --}}

    {{-- Modals for Department Actions --}}
    {{-- @if ($is_department_user)
        <!-- Send Back Modal -->
        <div class="modal fade" id="sendBackModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('advertisements.update', $advertisement->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Send Back to Office</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="send_back_reason" class="form-label">Reason for Sending Back</label>
                                <textarea class="form-control" id="send_back_reason" name="remarks" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="action" value="department_send_back"
                                class="btn btn-warning">Send Back</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif --}}
@endpush

@push('scripts')
    <script>
        flatpickr("#submission_date", {
            mode: "range",
            dateFormat: "d-m-Y",
            allowInput: true
        });
        flatpickr("#publication_date", {
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

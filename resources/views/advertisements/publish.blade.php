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


{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-item-center">
                    <a href="{{ url()->previous() }}" class="back-button me-2"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="me-3 mt-2">Published Advertisements</h5>

                </div>

                <div class="d-flex gap-2">
                    {{-- Global Search Form --}}
                    <form method="GET" action="{{ route('advertisements.published') }}" class="d-flex js-local-search-form">
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
                    <a href="{{ route('advertisements.published.export.excel', request()->query()) }}"
                        class="custom-excel-button me-2">Export Excel</a>
                    <a href="{{ route('advertisements.published.export.pdf', request()->query()) }}"
                        class="custom-pdf-button">Export PDF</a>
                </div>
            </div>


            {{-- Advanced Filter Modal --}}
            <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-labelledby="advancedFilterModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET" action="{{ route('advertisements.published') }}">
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
                                <a href="{{ route('advertisements.published') }}"
                                    class="custom-secondary-button">Reset</a>
                                <button type="submit" class="custom-primary-button">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @php
                use Carbon\Carbon;
                $user = Auth::user();
            @endphp

            @if ($publishedAdvertisements->isNotEmpty())
                <div class="table-responsive">
                    <table class="table js-local-filter-table">
                        <thead>
                            <tr>
                                <th>S. No.</th>
                                @if (
                                    $user->hasRole([
                                        'Superintendent',
                                        'Diary Dispatch',
                                        'Super Admin',
                                        'Deputy Director',
                                        'Director General',
                                        'Secretary',
                                    ]))
                                    <th>INF Number</th>
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
                                <th>Publication Date</th>
                                @unless ($user->hasRole('Client Office'))
                                    <th>No. of Lines</th>
                                @endunless
                                @unless ($user->hasRole('Media'))
                                    <th>
                                        Media
                                    </th>
                                    {{-- <th>
                                        Published at
                                    </th> --}}
                                @endunless
                                <th>Status</th>

                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody class="js-local-filter-rows">
                            @foreach ($publishedAdvertisements as $key => $publishedAdvertisement)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    @if (
                                        $user->hasRole([
                                            'Superintendent',
                                            'Diary Dispatch',
                                            'Super Admin',
                                            'Deputy Director',
                                            'Director General',
                                            'Secretary',
                                        ]))
                                        <td>{{ $publishedAdvertisement->inf_number }}</td>
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
                                                !$publishedAdvertisement->office_id))
                                        <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                            {{ \Illuminate\Support\Str::words($publishedAdvertisement->department->name ?? '-', 5, '...') }}
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
                                                $publishedAdvertisement->office_id))
                                        <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                            {{ \Illuminate\Support\Str::words($publishedAdvertisement->office->ddo_name ?? '-', 5, '...') }}
                                        </td>
                                    @endif

                                    <td>{{ Carbon::parse($publishedAdvertisement->publish_on_or_before)->toFormattedDateString() }}
                                    </td>

                                    @unless ($user->hasRole('Client Office'))
                                        <td class="text-center">
                                            <span class="d-block">Eng. = {{ $publishedAdvertisement->english_lines }} ,
                                                <br>
                                                Urdu =
                                                {{ $publishedAdvertisement->urdu_lines }}</span>
                                        </td>
                                    @endunless
                                    @unless ($user->hasRole('Media'))
                                        <td>
                                            @foreach ($publishedAdvertisement->newspapers->where('pivot.is_published', 1)->unique('pivot.agency_id') as $newspaper)
                                                @if (is_null($newspaper->pivot->agency_id))
                                                    <span>{{ $newspaper->title }},<br></span>
                                                @else
                                                    {{ \App\Models\AdvAgency::find($newspaper->pivot->agency_id)?->name ?? '—' }}
                                                @endif
                                            @endforeach
                                        </td>
                                    @endunless

                                    @php
                                        $statusClasses = [
                                            'New' => 'bg-label-success',
                                            'Approved' => 'bg-label-primary',
                                            'Pending' => 'bg-label-info',
                                            'Rejected' => 'bg-label-danger',
                                            'Draft' => 'bg-label-secondary',
                                            'In progress' => 'bg-label-warning',
                                        ];
                                        $class =
                                            $statusClasses[$publishedAdvertisement->status->title] ?? 'bg-secondary';
                                    @endphp

                                    <td>
                                        <span class="badge rounded-pill bg-label-primary">
                                            {{ $publishedAdvertisement ? 'Published' : 'Unpublished' }}
                                        </span>
                                    </td>

                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">
                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a
                                                    href="{{ route('advertisements.published.show', $publishedAdvertisement->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View Ad</span>
                                            </div>

                                            {{-- Track --}}
                                            @can('Track Ad Btn')
                                                <div class="action-item custom-tooltip">
                                                    <a type="button" data-bs-toggle="modal"
                                                        data-bs-target="#trackAdModal{{ $publishedAdvertisement->id }}">
                                                        <i class="bx bx-trip fs-4 bx-icon"></i>
                                                    </a>
                                                    <span class="tooltip-text">Track Ad</span>
                                                </div>
                                            @endcan

                                            {{-- Archive --}}
                                            <div class="action-item custom-tooltip">
                                                <form
                                                    action="{{ route('advertisements.archive', $publishedAdvertisement->id) }}"
                                                    method="POST" class="d-inline archive-form">
                                                    @csrf
                                                    <a type="button" class="btn p-0 border-0 bg-transparent archive-btn">
                                                        <i class="bx bx-archive-in fs-4 bx-icon"></i>
                                                    </a>
                                                    <span class="tooltip-text">Archive Ad</span>

                                                    <!-- Custom Confirmation Alert -->
                                                    <div class="custom-alert-overlay d-none">
                                                        <div class="custom-alert-box">
                                                            <p>Are you sure you want to archive this ad?</p>
                                                            <div class="d-flex justify-content-end gap-2 mt-3">
                                                                <button type="button"
                                                                    class="custom-secondary-button cancel-alert">Cancel</button>
                                                                <button type="submit" class="custom-danger-button">Yes,
                                                                    Archive</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>

                                            {{-- Tracking Modal --}}
                                            <div class="modal fade" id="trackAdModal{{ $publishedAdvertisement->id }}"
                                                tabindex="-1" aria-labelledby="trackAdModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header"
                                                            style="padding: .8rem 1.8rem; border-bottom: .13rem solid #e7e7e7;">
                                                            <h5 class="modal-title" style="color: var(--dark-text);">
                                                                Ad
                                                                Change Timeline</h5>
                                                            <div class="action-buttons d-flex gap-2">
                                                                <div>
                                                                    <a href="javascript:void(0);"
                                                                        class="text-decoration-none">
                                                                        <i
                                                                            class='bx bx-share-alt bx-modal-icons button-y'></i>
                                                                    </a>
                                                                </div>
                                                                <div class="">
                                                                    <button type="button" class="button-x"
                                                                        data-bs-dismiss="modal"><i
                                                                            class='bx bx-x bx-modal-icons'></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="timeline-container">
                                                                @php
                                                                    // Group logs by Date and Role
                                                                    $logsByDate = $publishedAdvertisement->changeLogs->groupBy(
                                                                        function ($log) {
                                                                            return $log->role .
                                                                                '_' .
                                                                                \Carbon\Carbon::parse(
                                                                                    $log->changed_at,
                                                                                )->format('Y-m-d');
                                                                        },
                                                                    );
                                                                @endphp

                                                                @forelse($logsByDate as $group => $logs)
                                                                    @php
                                                                        [$role, $date] = explode('_', $group);
                                                                    @endphp

                                                                    {{-- Date box --}}
                                                                    <div class="timeline-date">
                                                                        <div class="date-box">
                                                                            <div class="day">
                                                                                {{ \Carbon\Carbon::parse($date)->format('d') }}
                                                                            </div>
                                                                            <div class="month">
                                                                                {{ strtoupper(\Carbon\Carbon::parse($date)->format('M')) }},
                                                                                {{ \Carbon\Carbon::parse($date)->format('Y') }}
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

                                                                            {{-- Changes --}}
                                                                            @foreach ($logs as $log)
                                                                                <div class="change-card bg-white shadow-sm rounded"
                                                                                    style="padding: .5rem">
                                                                                    <div
                                                                                        class="d-flex justify-content-between align-items-center">
                                                                                        <span
                                                                                            class="danger-badge">{{ fieldLabel($log->field) }}
                                                                                            changed</span>
                                                                                        <span>{{ \Carbon\Carbon::parse($log->changed_at)->format('h:i A') }}</span>
                                                                                    </div>
                                                                                    <div
                                                                                        class="d-flex justify-content-start align-items-center">
                                                                                        <span class="text-muted ms-1">From:
                                                                                        </span>
                                                                                        <span
                                                                                            class="ms-1">{{ displayValue($log->field, $log->old_value) }}</span>
                                                                                        <i
                                                                                            class="bx bx-right-arrow-alt scaleX-n1-rtl mx-2"></i>
                                                                                        <span class="text-muted ms-1">To:
                                                                                        </span>
                                                                                        <span
                                                                                            class="ms-1">{{ displayValue($log->field, $log->new_value) }}</span>
                                                                                    </div>
                                                                                </div>
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
            @else
                <div class="text-center text-muted py-4">No ads to show</div>
            @endif

        </div>
    </div>
    {{-- ! / Page Content --}}
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
    {{-- Archive Modal --}}
    <script>
        document.querySelectorAll('.archive-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const alertBox = this.closest('form').querySelector('.custom-alert-overlay');
                alertBox.classList.remove('d-none');
            });
        });

        document.querySelectorAll('.cancel-alert').forEach(btn => {
            btn.addEventListener('click', function() {
                const alertBox = this.closest('.custom-alert-overlay');
                alertBox.classList.add('d-none');
            });
        });
    </script>

    {{-- Unarchive Modal --}}
    <script>
        // Show Unarchive Modal
        document.querySelectorAll('.unarchive-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('unarchive-alert-overlay').classList.remove('d-none');
            });
        });

        // Close (Cancel) from any modal
        document.querySelectorAll('.cancel-alert').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.custom-alert-overlay').classList.add('d-none');
            });
        });
    </script>
@endpush

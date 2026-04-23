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
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="{{ url()->previous() }}" class="back-button me-2"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="me-3">Archived Ads List</h5>

                </div>
                <div class="d-flex gap-2">
                    {{-- Global Search Form --}}
                    <form method="GET" action="{{ route(Route::currentRouteName()) }}" class="d-flex js-local-search-form">
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
                    <a href="{{ route('advertisements.archived.export.excel', request()->query()) }}" class="custom-excel-button me-2">Export
                        Excel</a>
                    <a href="{{ route('advertisements.archived.export.pdf', request()->query()) }}" class="custom-pdf-button">Export PDF</a>
                </div>
            </div>
            {{-- Advanced Filter Modal --}}
            <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-labelledby="advancedFilterModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET" action="{{ route(Route::currentRouteName()) }}">
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
                                <a href="{{ route(Route::currentRouteName()) }}"
                                    class="custom-secondary-button">Reset</a>
                                <button type="submit" class="custom-primary-button">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @php
                $user = Auth::user();
                $is_department_user = $user->department_id && is_null($user->office_id);
                $is_office_user = $user->department_id && !is_null($user->office_id);

            @endphp
            @if ($archivedAds->isNotEmpty())
                <div class="table-responsive text-nowrap">
                    <table class="table js-local-filter-table">
                        <thead>
                            <tr>
                                <th>S. No.</th>
                                <th>INF No.</th>
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
                                    ]))
                                    <th>Department / Office</th>
                                @endif
                                <th>Ad submitted by</th>
                                <th>Submission Date</th>
                                <th>Publication Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0 js-local-filter-rows">
                            @foreach ($archivedAds as $key => $ad)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $ad->inf_number ?? '-' }}</td>
                                    @if (
                                        $is_department_user ||
                                            ($user->hasRole([
                                                'Superintendent',
                                                'Diary Dispatch',
                                                'Super Admin',
                                                'Deputy Director',
                                                'Director General',
                                                'Secretary',
                                            ]) &&
                                                !$ad->office_id))
                                        <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                            {{ \Illuminate\Support\Str::words($ad->department->name ?? '-', 5, '...') }}
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
                                            ]) &&
                                                $ad->office_id))
                                        <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                            {{ \Illuminate\Support\Str::words($ad->office->ddo_name ?? '-', 5, '...') }}
                                        </td>
                                    @endif
                                    <td>{{ $ad->user->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ad->created_at)->toFormattedDateString() }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ad->publish_on_or_before)->toFormattedDateString() }}
                                    </td>
                                    <td>
                                        <form action="{{ route('advertisements.unarchive', $ad->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf

                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('advertisements.show', $ad->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View Ad</span>
                                            </div>

                                            {{-- Unarchive --}}
                                            <div class="action-item custom-tooltip">
                                                <a type="button" class="unarchive-btn">
                                                    <i class="bx bx-archive-out fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">Unarchive Ad</span>
                                            </div>

                                            <!-- Custom Unarchive Confirmation Modal -->
                                            <div class="custom-alert-overlay d-none" id="unarchive-alert-overlay">
                                                <div class="custom-alert-box">
                                                    <p>Are you sure you want to unarchive this ad?</p>
                                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                                        <button type="button"
                                                            class="custom-secondary-button cancel-alert">Cancel</button>
                                                        <form action="{{ route('advertisements.unarchive', $ad->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            <button type="submit" class="custom-primary-button">Yes,
                                                                Move
                                                                to Ads</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- {{ $ads->links() }} --}}
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

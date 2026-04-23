@extends('layouts.masterVertical')


{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-start align-items-center">
                <div class="d-flex align-item-center ">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="me-3">Unpublished Advertisements</h5>
                    @if ($unpublishedAdvertisements->isEmpty())
                        <span class="text-muted">No ads to show</span>
                    @endif

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
                    <a href="javascript:void(0)" class="custom-excel-button me-2 js-export-excel">Export Excel</a>
                    <a href="javascript:void(0)" class="custom-pdf-button js-export-pdf">Export PDF</a>
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
                                <div class="mb-3">
                                    <label for="inf_number" class="form-label">Inf Number</label>
                                    <input type="text" name="inf_number" id="inf_number" class="form-control"
                                        placeholder="inf number" value="{{ request('inf_number') }}">
                                </div>
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
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            </div>
                            <div class="modal-footer">
                                <a href="{{ route(Route::currentRouteName()) }}" class="custom-secondary-button">Reset</a>
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

            @if ($unpublishedAdvertisements->isNotEmpty())
                <div class="table-responsive text-nowrap">
                    <table class="table js-local-filter-table">
                        <thead>
                            <tr>
                                <th>S. No.</th>
                                <th>INF Number</th>
                                <th>Office</th>
                                <th>Publication Date</th>
                                @unless ($user->hasRole('Client Office'))
                                    <th>No. of Lines</th>
                                @endunless
                                @unless ($user->hasRole('Media'))
                                    <th>Media</th>
                                @endunless
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="js-local-filter-rows">
                            @foreach ($unpublishedAdvertisements as $key => $unpublishedAdvertisement)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $unpublishedAdvertisement->inf_number }}</td>
                                    <td>{{ $unpublishedAdvertisement->office->name ?? '-' }}</td>
                                    <td>{{ Carbon::parse($unpublishedAdvertisement->publish_on_or_before)->toFormattedDateString() }}
                                    </td>

                                    @unless ($user->hasRole('Client Office'))
                                        <td class="text-center">
                                            <span class="d-block">Eng. = {{ $unpublishedAdvertisement->english_lines }} ,
                                                Urdu =
                                                {{ $unpublishedAdvertisement->urdu_lines }}</span>
                                        </td>
                                    @endunless

                                    @unless ($user->hasRole('Media'))
                                        <td>
                                            @foreach ($unpublishedAdvertisement->newspapers->where('pivot.is_published', 0) as $newspaper)
                                                <span>
                                                    {{ $newspaper->title }}
                                                </span>
                                            @endforeach
                                        </td>
                                    @endunless
                                    @php
                                        $statusClasses = [
                                            'New' => 'bg-success',
                                            'Approved' => 'bg-primary',
                                            'Pending' => 'bg-info',
                                            'Rejected' => 'bg-danger',
                                            'Draft' => 'bg-secondary',
                                            'In progress' => 'bg-warning',
                                        ];
                                        $class =
                                            $statusClasses[$unpublishedAdvertisement->status->title] ?? 'bg-secondary';
                                    @endphp
                                    <td>
                                        <span class="badge rounded-pill bg-danger">
                                            {{ $unpublishedAdvertisement ? 'Unpublished' : '' }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">
                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a
                                                    href="{{ route('advertisements.unpublished', $unpublishedAdvertisement->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View Ad</span>
                                            </div>

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

                                            {{-- Archive --}}
                                            <div class="action-item custom-tooltip">
                                                <form
                                                    action="{{ route('advertisements.archive', $unpublishedAdvertisement->id) }}"
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
                                            <div class="modal fade" id="trackAdModal{{ $unpublishedAdvertisement->id }}"
                                                tabindex="-1" aria-labelledby="trackAdModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header"
                                                            style="padding: .8rem 1.8rem; border-bottom: .13rem solid #e7e7e7;">
                                                            <h5 class="modal-title" style="color: var(--dark-text);">Ad
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
                                                                    $logsByDate = $unpublishedAdvertisement->changeLogs->groupBy(
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
            @endif
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush

@push('style')
    <style>
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

@push('scripts')
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

            function downloadFile(filename, content, mime) {
                const blob = new Blob([content], {
                    type: mime
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            }

            function exportVisibleTableToCsv() {
                const table = document.querySelector('table.js-local-filter-table');
                if (!table) return;

                const rows = [];
                const head = table.querySelectorAll('thead tr th');
                if (head && head.length) {
                    rows.push(Array.from(head).map(th => `"${(th.innerText || th.textContent || '').trim().replaceAll('"', '""')}"`).join(','));
                }

                table.querySelectorAll('tbody.js-local-filter-rows tr').forEach((tr) => {
                    if (tr.style.display === 'none') return;
                    const cols = Array.from(tr.querySelectorAll('td')).map(td => `"${(td.innerText || td.textContent || '').trim().replaceAll('"', '""')}"`);
                    rows.push(cols.join(','));
                });

                downloadFile('unpublished_ads_' + new Date().toISOString().slice(0, 10) + '.csv', rows.join('\n'), 'text/csv;charset=utf-8;');
            }

            function exportVisibleTableToPdf() {
                const table = document.querySelector('table.js-local-filter-table');
                if (!table) return;

                const w = window.open('', '_blank');
                const style = `
                    <style>
                        body{font-family: Arial, sans-serif; padding:16px;}
                        h2{margin:0 0 12px 0;}
                        table{width:100%; border-collapse:collapse; font-size:12px;}
                        th,td{border:1px solid #ddd; padding:6px; vertical-align:top;}
                        th{background:#f5f5f5;}
                    </style>
                `;

                // clone and remove hidden rows
                const clone = table.cloneNode(true);
                clone.querySelectorAll('tbody tr').forEach(tr => {
                    if (tr.style.display === 'none') tr.remove();
                });

                w.document.write('<html><head><title>Unpublished Ads</title>' + style + '</head><body>');
                w.document.write('<h2>Unpublished Advertisements</h2>');
                w.document.write(clone.outerHTML);
                w.document.write('</body></html>');
                w.document.close();
                w.focus();
                w.print();
            }

            document.addEventListener('DOMContentLoaded', function() {
                const input = document.querySelector('.js-local-search-input');
                if (input) {
                    input.addEventListener('input', function() {
                        applyLocalFilter(input.value);
                    });
                    if (input.value) applyLocalFilter(input.value);
                }

                const excelBtn = document.querySelector('.js-export-excel');
                if (excelBtn) excelBtn.addEventListener('click', exportVisibleTableToCsv);
                const pdfBtn = document.querySelector('.js-export-pdf');
                if (pdfBtn) pdfBtn.addEventListener('click', exportVisibleTableToPdf);

                // date range pickers for advanced filters
                if (document.getElementById('submission_date')) {
                    flatpickr("#submission_date", {
                        mode: "range",
                        dateFormat: "d-m-Y",
                        allowInput: true
                    });
                }
                if (document.getElementById('publication_date')) {
                    flatpickr("#publication_date", {
                        mode: "range",
                        dateFormat: "d-m-Y",
                        allowInput: true
                    });
                }
            });
        })();
    </script>
@endpush

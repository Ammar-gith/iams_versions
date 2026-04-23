@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-start align-items-center">
                <h5 class="me-3">Offices Advertisements List</h5>
                @if ($advertisements->isEmpty())
                    <span class="text-muted">No ads to show</span>
                @endif
            </div>

            {{-- Get the authenticated logged in user --}}
            @php
                $user = Auth::User();
            @endphp

            {{-- Show ads if any --}}
            @if ($advertisements->isNotEmpty())
                <div class="table-responsive text-nowrap">
                    <table class="table w-100">
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

                                @if (
                                    $user->hasRole([
                                        'Superintendent',
                                        'Diary Dispatch',
                                        'Super Admin',
                                        'Deputy Director',
                                        'Director General',
                                        'Secretary',
                                        'Media',
                                        'Client Office',
                                    ]))
                                    <th>Office</th>
                                @endif
                                <th>Submission Date</th>
                                <th>Publication Date</th>
                                {{-- <th>Submitted by</th> --}}
                                @if (!$user->hasRole('Media'))
                                    {{-- <th class="text-center">Status</th> --}}
                                @endif
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($advertisements as $key => $advertisement)
                                <tr>
                                    <td>{{ ++$key }}</td> <!-- Serial Number -->
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
                                            <td>{{ $advertisement->inf_number }}</td>
                                        @endcan
                                    @endif
                                    @if (
                                        $is_office_user ||
                                            ($user->hasRole([
                                                'Superintendent',
                                                'Diary Dispatch',
                                                'Super Admin',
                                                'Deputy Director',
                                                'Director General',
                                                'Secretary',
                                                'Media',
                                                'Client Office'
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
                                    {{-- @php
                                        $statusClasses = [
                                            'New' => 'bg-success',
                                            'Approved' => 'bg-primary',
                                            'forwarded by DD' => 'bg-warning',
                                            'Rejected' => 'bg-danger',
                                            'Draft' => 'bg-label-secondary',
                                            'In progress' => 'bg-warning',
                                        ];
                                        $class = $statusClasses[$advertisement->status->title] ?? 'bg-secondary';

                                        $forwardedBy = $advertisement->forwarded_by_role_id;
                                        $forwardedTo = $advertisement->forwarded_to_role_id;

                                    @endphp
                                    @if (!$user->hasRole('Media'))
                                        <td class="text-center align-middle">
                                            @if ($user->hasRole('Superintendent') || ($user->hasRole('Client Office') && $forwardedTo == 3))
                                                <span class="badge rounded-pill {{ $class }}">New</span>
                                            @elseif ($user->hasRole('Deputy Director') && $advertisement->status->title == 'In progress')
                                                <span class="badge rounded-pill bg-info">In progress / DG Approval</span>
                                            @elseif ($user->hasRole('Director General') && $advertisement->status->title == 'In progress')
                                                <span class="badge rounded-pill bg-info">forwarded by DD</span>
                                            @elseif ($user->hasRole('Secretary') && $advertisement->status->title == 'In progress')
                                                <span class="badge rounded-pill bg-info">forwarded by DG</span>
                                            @elseif ($user->hasRole('Client Office') && $user->office_id == null)
                                                <span class="badge rounded-pill bg-success">Pending for dept.
                                                    Approval</span>
                                            @elseif ($user->hasRole('Client Office') && $user->office_id != null)
                                                <span class="badge rounded-pill bg-success">Sent for dept. Approval</span>
                                            @else
                                                @if (!$user->hasRole('Media'))
                                                    <span
                                                        class="badge rounded-pill {{ $class }}">{{ $advertisement->status->title ?? '' }}</span>
                                                @endif
                                            @endif
                                        </td>
                                    @endif --}}
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- send back to office for correction  --}}
                                            <div class="action-item custom-tooltip d-flex">
                                                @if ($is_department_user && $advertisement->status->title == 'Pending Department Approval')
                                                    <form action="{{ route('advertisements.update', $advertisement->id) }}"
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
                                                                    // Group logs by Date and Role
                                                                    $logsByDate = $advertisement->changeLogs->groupBy(
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
                    <div class="custom-pagination">
                        {{-- {{ $advertisements->links() }} --}}
                    </div>
                </div>
            @endif
        </div>
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
@endpush

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
    </style>
@endpush


@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Helper function to resolve IDs to readable names --}}
    @php
        use App\Models\Newspaper;
        use App\Models\Advertisement;
        use App\Models\Status;
        use App\Models\Office;
        use App\Models\AdWorthParameter;
        use Spatie\Permission\Models\Role;
        use Illuminate\Support\Facades\Cache;

        if (!function_exists('resolveFieldValue')) {
            function resolveFieldValue($subject, $field, $value)
            {
                if ($value === null || $value === '') {
                    return '';
                }

                $resolveRelation = function ($modelClass, $id, $displayColumn = 'name') {
                    if (!$id) {
                        return '';
                    }
                    $cacheKey = $modelClass . '_' . $id;
                    return Cache::remember($cacheKey, now()->addHour(), function () use (
                        $modelClass,
                        $id,
                        $displayColumn,
                    ) {
                        $model = $modelClass::find($id);
                        return $model ? $model->{$displayColumn} ?? $model->id : $id;
                    });
                };

                // Role IDs
                if (in_array($field, ['forwarded_to_role_id', 'forwarded_by_role_id'])) {
                    return $resolveRelation(Role::class, $value, 'name');
                }

                // Status ID – uses 'title'
                if ($field === 'status_id') {
                    $id = is_array($value) ? $value[0] ?? null : $value;
                    if (!$id) {
                        return '';
                    }
                    $status = Status::find($id);
                    return $status ? $status->title ?? $status->id : "Deleted status ({$id})";
                }

                // Office ID – uses 'ddo_name'
                if ($field === 'office_id') {
                    $id = is_array($value) ? $value[0] ?? null : $value;
                    if (!$id) {
                        return '';
                    }
                    $office = Office::find($id);
                    return $office ? $office->ddo_name ?? $office->id : "Deleted office ({$id})";
                }

                // Ad worth ID – uses 'range'
                if ($field === 'ad_worth_id') {
                    $id = is_array($value) ? $value[0] ?? null : $value;
                    if (!$id) {
                        return '';
                    }
                    $range = AdWorthParameter::find($id);
                    return $range ? $range->range ?? $range->id : "Deleted range ({$id})";
                }

                // Newspaper array fields (including *_NP_log)
                if ($field === 'newspaper_id' || preg_match('/_NP_log$/i', $field)) {
                    $ids = [];
                    if (is_array($value)) {
                        $ids = $value;
                    } elseif (is_string($value)) {
                        $decoded = json_decode($value, true);
                        if (is_array($decoded)) {
                            $ids = $decoded;
                        } elseif (strpos($value, ',') !== false) {
                            $ids = array_map('trim', explode(',', $value));
                        } else {
                            $ids = [$value];
                        }
                    } else {
                        $ids = [$value];
                    }

                    $ids = array_filter($ids, fn($id) => is_numeric($id) && $id !== '' && $id !== null);
                    if (empty($ids)) {
                        return '';
                    }

                    $newspapers = Newspaper::whereIn('id', $ids)->pluck('title', 'id')->toArray();
                    $names = [];
                    foreach ($ids as $id) {
                        $idInt = (int) $id;
                        if (isset($newspapers[$idInt]) && !empty(trim($newspapers[$idInt]))) {
                            $names[] = $newspapers[$idInt];
                        } elseif (isset($newspapers[$idInt])) {
                            $names[] = "ID: {$idInt} (title empty)";
                        } else {
                            $names[] = "ID: {$idInt} (deleted)";
                        }
                    }
                    return implode(', ', $names);
                }

                // If subject is Advertisement, use its dedicated resolver
                if ($subject instanceof Advertisement && method_exists($subject, 'getDisplayValueForField')) {
                    return $subject->getDisplayValueForField($field, $value);
                }

                // Generic *_id fields
                if (str_ends_with($field, '_id') && !is_array($value)) {
                    $fieldModelMap = [
                        'user_id' => \App\Models\User::class,
                        'department_id' => \App\Models\Department::class,
                        'office_id' => Office::class,
                        'ad_category_id' => \App\Models\AdCategory::class,
                        'ad_worth_id' => AdWorthParameter::class,
                        'status_id' => Status::class,
                        'adv_agency_id' => \App\Models\AdvAgency::class,
                        'classified_ad_type_id' => \App\Models\ClassifiedAdType::class,
                    ];
                    if (isset($fieldModelMap[$field])) {
                        return $resolveRelation($fieldModelMap[$field], $value, 'name');
                    }
                }

                if (is_array($value)) {
                    return json_encode($value, JSON_PRETTY_PRINT);
                }

                return $value;
            }
        }
    @endphp

    {{-- Page Content --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-end mt-">
                        <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                        <h5 class="mt-2">Audit Trail</h5>
                    </div>


                    <div class="d-flex gap-2">
                        {{-- Global Search Form --}}
                        <form method="GET" action="{{ route(Route::currentRouteName()) }}" class="d-flex">
                            <div class="input-group" style="min-width: 250px;">
                                <input type="text" name="search" class="form-control rounded-left form-control-sm"
                                    placeholder="Search..." value="{{ request('search') }}">
                                <button
                                    style="background: linear-gradient(135deg, #AAD9C9, #5DB698); border: none; color: black;"
                                    class="btn btn-sm rounded-right btn-primary" type="submit">
                                    <i class='bx bx-search'></i>
                                </button>
                            </div>
                        </form>
                        {{-- Advanced Filter Button --}}
                        <button
                            style=" background: linear-gradient(135deg, #AAD9C9, #5DB698); border-style: none; color:black;"
                            type="button" class="btn btn-sm rounded-pill btn-primary" data-bs-toggle="modal"
                            data-bs-target="#advancedFilterModal">
                            <i class='bx bx-search'></i> Advanced
                        </button>
                    </div>
                    {{-- Export Buttons --}}
                    <div class="d-flex justify-content-end mb-1">
                        <a href="" class="custom-excel-button me-2">Export
                            Excel</a>
                        <a href="" class="custom-pdf-button">Export PDF</a>
                    </div>

                </div>
                {{-- Advanced Filter Modal --}}
                <div class="modal fade" id="advancedFilterModal" tabindex="-1" aria-labelledby="advancedFilterModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="GET" action="{{ route('audit-logs.index') }}">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="advancedFilterModalLabel">Advanced Filters</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    {{-- inf Number --}}
                                    {{-- <div class="mb-3">
                                        <label for="inf-number" class="form-label">Inf Number</label>
                                        <input type="text" name="inf_number" id="inf_number" class="form-control"
                                            placeholder="inf number" value="{{ request('inf_number') }}">
                                    </div> --}}
                                    {{-- Department Dropdown --}}
                                    {{-- <div class="mb-3">
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
                                    </div> --}}

                                    {{-- Office Dropdown --}}
                                    {{-- <div class="mb-3">
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
                                    </div> --}}

                                    <div class="">
                                        <label for="causer_id" class="form-label">User</label>
                                        <select name="causer_id" id="causer_id" class="form-select select2">
                                            <option value="">All Users</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ request('causer_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Date Range --}}
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="publication_from" class="form-label">From</label>
                                            <input type="date" name="publication_from" id="publication_from"
                                                class="form-control date" placeholder="DD-MM-YYYY"
                                                value="{{ request('publication_from') }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="publication_to" class="form-label">To</label>
                                            <input type="date" name="publication_to" id="publication_to"
                                                class="form-control date" placeholder="DD-MM-YYYY"
                                                value="{{ request('publication_to') }}">
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
                <div class="card-body">

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Subject</th>
                                    <th>Field</th>
                                    <th>Old Value</th>
                                    <th>New Value</th>
                                    <th>IP Address</th>
                                    <th>URL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    @php
                                        $properties = $activity->properties ?? collect();
                                        $old = $properties->get('old', []);
                                        $new = $properties->get('attributes', $properties->get('new', []));
                                        $changedFields = array_keys(array_merge($old, $new));
                                        $hasChanges = !empty($changedFields);

                                        $subject = $activity->subject;
                                        if ($subject) {
                                            $subjectDisplay = class_basename($subject) . ': ';
                                            $subjectDisplay .=
                                                $subject->name ?? ($subject->title ?? 'ID ' . $subject->getKey());
                                        } else {
                                            $subjectDisplay = '-';
                                        }

                                        $userDisplay =
                                            $activity->causer->name ?? ($activity->causer->email ?? 'System');
                                        $action = $activity->description;
                                        $ip = $properties->get('ip', 'N/A');
                                        $url = $properties->get('url', $properties->get('page', 'N/A'));
                                    @endphp

                                    @if ($hasChanges)
                                        @foreach ($changedFields as $field)
                                            <tr>
                                                <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                                <td>{{ $userDisplay }}</td>
                                                <td><span class="badge bg-info">{{ $action }}</span></td>
                                                <td><small class="text-muted">{{ $subjectDisplay }}</small></td>
                                                <td><code>{{ $field }}</code></td>
                                                <td>
                                                    {{ resolveFieldValue($subject, $field, $old[$field] ?? null) }}
                                                </td>
                                                <td>
                                                    {{ resolveFieldValue($subject, $field, $new[$field] ?? null) }}
                                                </td>
                                                <td><small>{{ $ip }}</small></td>
                                                <td><small class="text-truncate d-inline-block"
                                                        style="max-width: 150px;">{{ $url }}</small></td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td>{{ $userDisplay }}</td>
                                            <td><span class="badge bg-secondary">{{ $action }}</span></td>
                                            <td>{{ $subjectDisplay }}</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>{{ $ip }}</td>
                                            <td>{{ $url }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">No audit logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center mt-3">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush
@push('scripts')
    <script>
        flatpickr(".date", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"
        });
    </script>
@endpush

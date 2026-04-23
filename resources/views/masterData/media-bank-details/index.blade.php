@extends('layouts.masterVertical')

@push('content')
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                    <h5>Media bank details</h5>
                    <a href="{{ route('master.mediaBankDetail.create') }}" class="custom-primary-button">Add bank detail</a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success mx-3 mb-0">{{ session('success') }}</div>
                @endif

                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>S. No.</th>
                                <th>Type</th>
                                <th>Media</th>
                                <th>Bank</th>
                                <th>Account title</th>
                                <th>Account no</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($rows as $i => $r)
                                @php
                                    $isNp = !empty($r->newspaper_id);
                                    $type = $isNp ? 'Newspaper' : 'Agency';
                                    $mediaLabel = $r->media_name ?: ($isNp ? ($r->newspaper->title ?? '—') : ($r->agency->name ?? '—'));
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $type }}</td>
                                    <td class="fw-semibold">{{ $mediaLabel }}</td>
                                    <td>{{ $r->bank_name }}</td>
                                    <td>{{ $r->account_title }}</td>
                                    <td><code>{{ $r->account_number }}</code></td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center gap-1">
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('master.mediaBankDetail.show', $r->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View</span>
                                            </div>
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('master.mediaBankDetail.edit', $r->id) }}">
                                                    <i class='bx bx-edit-alt fs-4 bx-icon'></i>
                                                </a>
                                                <span class="tooltip-text">Edit</span>
                                            </div>
                                            <div class="action-item custom-tooltip">
                                                <a type="button" class="deletebtn" onclick="deleteMediaBank('{{ $r->id }}')">
                                                    <i class="bx bx-trash fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">Delete</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteMediaBank(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                cancelButtonColor: "#d33",
                confirmButtonColor: "#347842",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ url('/delete-media-bank-detail') }}/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    }).then(r => r.json().then(j => ({
                        j,
                        ok: r.ok
                    }))).then(({
                        j,
                        ok
                    }) => {
                        if (ok && j.success) {
                            Swal.fire({
                                title: "Deleted!",
                                text: j.success,
                                icon: "success"
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: j.error || "Delete failed.",
                                icon: "error"
                            });
                        }
                    }).catch(() => {
                        Swal.fire({
                            title: "Error!",
                            text: "Network error.",
                            icon: "error"
                        });
                    });
                }
            });
        }
    </script>
@endpush


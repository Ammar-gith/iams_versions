@extends('layouts.masterVertical')

{{-- Custom CSS --}}
@push('style')
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Table --}}
    <div class="row">
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                    <h5>Statuses List</h5>
                    <a href="{{ route('master.status.create') }}" class="custom-primary-button">Add Status</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>S. No.</th>
                                <th>Title</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($statuses as $key => $status)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $status->title }}</td>
                                    {{-- @php
                                        $id = Crypt::encrypt($unit->id);
                                    @endphp --}}
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('master.status.show', $status->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View Status</span>
                                            </div>

                                            {{-- Edit (Update) --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('master.status.edit', $status->id) }}">
                                                    <i class='bx bx-edit-alt fs-4 bx-icon'></i>
                                                </a>
                                                <span class="tooltip-text">Edit Status</span>
                                            </div>

                                            {{-- Delete --}}
                                            <div class="action-item custom-tooltip">
                                                <a type="button" class="deletebtn"
                                                    onclick="deleteStatus('{{ $status->id }}')">
                                                    <i class="bx bx-trash fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">Delete Status</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- ! / End Table --}}
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/ui-modals.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function deleteStatus(status_id) {
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
                    $.ajax({
                        url: '/status/' + status_id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "The status has been deleted.",
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Error!",
                                text: "There was an issue deleting the status.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush

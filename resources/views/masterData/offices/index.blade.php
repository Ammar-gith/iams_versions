@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Table --}}
    <div class="row">
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                    <h5>Offices List</h5>
                    <a href="{{ route('master.office.create') }}" class="custom-primary-button">Add Office</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>S. No.</th>
                                <th>Office Name</th>
                                <th>DDO Code</th>
                                <th>District</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Opening Dues</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($offices as $key => $office)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                        {{ \Illuminate\Support\Str::words($office->ddo_name, 100, '...') }}
                                    </td>
                                    <td>{{ $office->ddo_code }}</td>
                                    <td>{{ $office->district->name }}</td>
                                    <td>{{ $office->officeCategory->title ?? '' }}</td>
                                    <td>
                                        @if($office->status == 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">In Active</span>
                                        @endif
                                    </td>
                                    <td>{{ $office->opening_dues }}</td>

                                    {{-- @php
                                        $id = Crypt::encrypt($unit->id);
                                    @endphp --}}
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('master.office.show', $office->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View Office</span>
                                            </div>

                                            {{-- Edit (Update) --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('master.office.edit', $office->id) }}">
                                                    <i class='bx bx-edit-alt fs-4 bx-icon'></i>
                                                </a>
                                                <span class="tooltip-text">Edit Office</span>
                                            </div>

                                            {{-- Delete --}}
                                            <div class="action-item custom-tooltip">
                                                <a type="button" class="deletebtn"
                                                    onclick="deleteOffice('{{ $office->id }}')">
                                                    <i class="bx bx-trash fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">Delete Office</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="custom-pagination">
                        {{ $offices->links() }}
                    </div>
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
        function deleteOffice(office_id) {
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
                        url: '/delete-office/' + office_id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "The office has been deleted.",
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Error!",
                                text: "There was an issue deleting the office.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush

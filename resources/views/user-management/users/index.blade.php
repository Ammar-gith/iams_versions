@extends('layouts.masterVertical')

{{-- Custom CSS --}}
@push('style')
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Table --}}
    <div class="row custom-paddings">
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                    <h5>Users List</h5>
                    <a href="{{ route('userManagement.user.create') }}" class="custom-primary-button">Add User</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>S. No.</th>
                                <th>Name</th>
                                <th>Username</th>
                                {{-- <th>Image</th> --}}
                                <th>Role</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($users as $key => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $loop->index }}</td>
                                    <td>{{ $user->name }}</td> <!-- Serial Number -->
                                    <td>{{ $user->username }}</td>
                                    {{-- <td>
                                        <img src="{{ $user->image ? asset('storage/' . $user->image) : asset('images/default_user.png') }}"
                                            alt="User Image" class="rounded-circle" width="50" height="50">
                                    </td> --}}

                                    {{-- <td>
                                        <img src="{{ asset('storage/user_images/KWuYvwxb62MKWxGfhEmr3q76wWxeTqwivrwlvDIZ.jpg') }}"
                                            alt="user_image" width="100">
                                    </td> --}}
                                    <td>
                                        @if (!empty($user->getRoleNames()))
                                            @foreach ($user->getRoleNames() as $roleName)
                                                <span class="badge rounded-pill bg-success">{{ $roleName }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    {{-- @php
                                        $id = Crypt::encrypt($unit->id);
                                    @endphp --}}
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('userManagement.user.show', $user->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View User</span>
                                            </div>

                                            {{-- Edit (Update) --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('userManagement.user.edit', $user->id) }}">
                                                    <i class='bx bx-edit-alt fs-4 bx-icon'></i>
                                                </a>
                                                <span class="tooltip-text">Edit User</span>
                                            </div>

                                            {{-- Delete --}}
                                            <div class="action-item custom-tooltip">
                                                <a type="button" class="deletebtn"
                                                    onclick="deleteUser('{{ $user->id }}')">
                                                    <i class="bx bx-trash fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">Delete User</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4 p-2">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ! / Table --}}
@endpush


@push('scripts')
    <script src="{{ asset('assets/js/ui-modals.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function deleteUser(user_id) {
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
                        url: '/delete-user/' + user_id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "The user has been deleted successfully.",
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Error!",
                                text: "There was an issue deleting the user.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush

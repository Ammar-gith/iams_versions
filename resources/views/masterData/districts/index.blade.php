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
                    <h5>Districts List</h5>
                    <a href="{{ route('master.district.create') }}" class="custom-primary-button">Add District</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>S. No.</th>
                                <th>Name</th>
                                <th>Province</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($districts as $key => $district)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $district->name }}</td>
                                    <td>{{ $district->province->name }}</td>
                                    {{-- @php
                                        $id = Crypt::encrypt($unit->id);
                                    @endphp --}}
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('master.district.show', $district->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View District</span>
                                            </div>

                                            {{-- Edit (Update) --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('master.district.edit', $district->id) }}">
                                                    <i class='bx bx-edit-alt fs-4 bx-icon'></i>
                                                </a>
                                                <span class="tooltip-text">Edit District</span>
                                            </div>

                                            {{-- Delete --}}
                                            <div class="action-item custom-tooltip">
                                                <a type="button" class="deletebtn"
                                                    onclick="deleteProvince('{{ $district->id }}')">
                                                    <i class="bx bx-trash fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">Delete District</span>
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
        function deleteDistrict(district_id) {
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
                        url: '/delete-district/' + district_id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "The District has been deleted.",
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Error!",
                                text: "There was an issue deleting the district.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush

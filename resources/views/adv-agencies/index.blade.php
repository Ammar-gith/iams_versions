@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <!-- Table -->
    <div class="row">
        <div class="card mb-4" style="padding: 0;">

            {{-- Header --}}
            <div class="card-header-table col-md-12 d-flex justify-content-between align-items-center">
                <h5 class="h5-reset-margin h5-padding">Advertising Agencies List</h5>
                @if ($adv_agencies->isEmpty())
                    <span class="text-muted">No Advertising Agencies to show</span>
                @endif
                <a href="{{ route('advAgency.create') }}" class="custom-primary-button">Add Adv. Agency</a>
            </div>

            {{-- Body --}}
            @if ($adv_agencies->isNotEmpty())
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th style="padding-right: 0 !important;">S. No.</th>
                                <th>Name</th>
                                <th>Registration Date</th>
                                <th>Status</th>
                                <th>Phone</th>
                                <th>KPRA Registered</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($adv_agencies as $adv_agency)
                                <tr>
                                    <td>{{ $adv_agency->id }}</td>
                                    <td>{{ $adv_agency->name }}</td>
                                    <td>
                                        {{ $adv_agency->registration_date ? \Carbon\Carbon::parse($adv_agency->registration_date)->toFormattedDateString() : 'N/A' }}
                                    </td>
                                    <td>{{ $adv_agency->status_id }}</td>
                                    <td>{{ $adv_agency->phone_hq }}</td>
                                    <td>{{ $adv_agency->registered_with_kpra == 1 ? 'Registered' : 'Not Registered' }}</td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('advAgency.show', $adv_agency->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View</span>
                                            </div>

                                            {{-- Edit (Update) --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('advAgency.edit', $adv_agency->id) }}">
                                                    <i class='bx bx-edit-alt fs-4 bx-icon'></i>
                                                </a>
                                                <span class="tooltip-text">Edit</span>
                                            </div>

                                            {{-- Delete --}}
                                            <div class="action-item custom-tooltip">
                                                <a type="button" class="deletebtn"
                                                    onclick="deleteAdvAgency('{{ $adv_agency->id }}')">
                                                    <i class="bx bx-trash fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">Delete</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="custom-pagination">
                        {{ $adv_agencies->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/ui-modals.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        function deleteAdvAgency(adv_agency_id) {
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
                        url: '/delete-adv-agency/' + adv_agency_id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "The Adv. Agency has been deleted.",
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Error!",
                                text: "There was an issue deleting the Adv. Agency.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush

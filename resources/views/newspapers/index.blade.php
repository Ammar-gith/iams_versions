@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Table --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">

            {{-- Header --}}
            <div class="card-header-table col-md-12 d-flex justify-content-between align-items-center">
                <h5 class="h5-reset-margin h5-padding">Newspapers List</h5>
                @if ($newspapers->isEmpty())
                    <span class="text-muted">No Newspapers to show</span>
                @endif
                <a href="{{ route('newspaper.create') }}" class="custom-primary-button">Add Newspaper</a>
            </div>

            {{-- Body --}}
            @if ($newspapers->isNotEmpty())
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="padding-right: 0 !important;">S. No.</th>
                                <th>Title</th>
                                <th>Language</th>
                                <th>Category</th>
                                <th>District</th>
                                {{-- <th>Circulation</th> --}}
                                <th>Rate</th>
                                <th>KPRA Reg.</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($newspapers as $key => $newspaper)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $newspaper->title }}</td>
                                    <td>{{ $newspaper->language->title ?? '' }}</td>
                                    <td>{{ $newspaper->category->title ?? '' }}</td>
                                    <td>{{ $newspaper->district->name ?? '' }}</td>
                                    {{-- <td>{{ $newspaper->language_id }}</td> --}}
                                    <td>{{ $newspaper->rate }}</td>
                                    <td>{{ $newspaper->register_with_kapra }}</td>
                                    <td>{{ $newspaper->status_label }}</td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('newspaper.show', $newspaper->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View</span>
                                            </div>

                                            {{-- Edit (Update) --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('newspaper.edit', $newspaper->id) }}">
                                                    <i class='bx bx-edit-alt fs-4 bx-icon'></i>
                                                </a>
                                                <span class="tooltip-text">Edit</span>
                                            </div>

                                            {{-- Delete --}}
                                            <div class="action-item custom-tooltip">
                                                <a type="button" class="deletebtn"
                                                    onclick="deleteNewspaper('{{ $newspaper->id }}')">
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
                        {{ $newspapers->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
    {{-- End Table --}}
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/ui-modals.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function deleteNewspaper(newspaper_id) {
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
                        url: '/delete-newspaper/' + newspaper_id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "The newspaper has been deleted.",
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Error!",
                                text: "There was an issue deleting the newspaper.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush

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
                    <h5 class="card-header text-primary">{{ $taxType->description }}</h5>
                    <a href="{{ url()->previous() }}" class="custom-primary-button">← Back</a>
                </div>
                <div class="menu-divider mb-4"></div>
                <div class="table-responsive text-nowrap card-body">
                    <table class="table">
                        {{-- <thead class="table-light">
                            <tr>
                                <th>S. No.</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($books as $key => $book)
                                <tr>
                                    <td>{{ ++$key }}</td> <!-- Serial Number -->
                                    <td>{{ $book->title }}</td>
                                    <td>{{ $book->author->name }}</td>
                                    <td>{{ $book->price }}</td>
                                    @php
                                        $id = Crypt::encrypt($book->id);
                                    @endphp
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ url('editbook/' . $id) }}">
                                                    <i class="bx bx-edit-alt me-1"></i>
                                                    Edit
                                                </a>
                                                <button type="button" class="dropdown-item deletebtn"
                                                    onclick="deleteBook('{{ $id }}')">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody> --}}
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
        function deleteBook(book_id) {
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
                        url: '/delete-book/' + book_id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "The book has been deleted.",
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Error!",
                                text: "There was an issue deleting the book.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush

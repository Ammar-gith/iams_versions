@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @elseif (session('danger'))
        <div class="alert alert-danger">
            {{ session('danger') }}
        </div>
    @endif

    <!-- Table -->
    <div class="row">
        <div class="card">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <h5>{{ $advAgency->name }}</h5>
                <a href="{{ url()->previous() }}" class="custom-primary-button">← Back</a>
            </div>
            <div class="menu-divider mb-4"></div>
            <div class="table-responsive text-nowrap">
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
@endpush

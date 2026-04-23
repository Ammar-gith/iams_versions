@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('department.update', $department->id) }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Update Department</h5>
                </div>

                <div class="form-padding">
                    {{-- department Name --}}
                    <div class="row mb-3 mx-5  ">
                        <div class="col-sm-6">
                            <label class=" col-form-label text-sm-end" for="name">Name</label>
                            <input type="text" name="name" id="alignment-name" class="form-control" required
                                value="{{ $department->name }}" />
                        </div>
                        @error('name')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Department category --}}
                    <div class="row mb-3 mx-5  ">
                        <label class=" col-form-label text-sm-start" for="category_id">Category</label>
                        <div class="col-sm-12">
                            @foreach ($department_categories as $department_category)
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="department_category_{{ $department_category->id }}"
                                        name="category_id" value="{{ $department_category->id }}" class="form-check-input "
                                        {{ $selected_category == $department_category->id ? 'selected' : '' }}>
                                    <label class="form-check-label "
                                        for="department_category_{{ $department_category->id }}">
                                        {{ $department_category->title }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('department_category_id')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Office status --}}
                    <div class="row mb-3 mx-5  ">
                        <label class=" col-form-label text-sm-start" for="name">Status</label>
                        <div class="col-sm-12">
                            @foreach ($statuses as $status)
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="status_{{ $status->id }}" name="status_id"
                                        value="{{ $status->id }}" class="form-check-input "
                                        {{ $selected_status == $status->id ? 'checked' : '' }}>
                                    <label class="form-check-label " for="status_{{ $status->id }}">
                                        {{ $status->title }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('status_id')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Update</button>

                    {{-- Cancel --}}
                    <a href="{{ route('master.department.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- End Form --}}
@endpush

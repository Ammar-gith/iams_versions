@extends('layouts.masterVertical')

@push('content')
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('mediaBankDetail.update', $row->id) }}" method="POST" class="card-body"
                style="padding: 0;">
                @csrf

                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Edit media bank detail</h5>
                </div>

                @php
                    $mediaType = old('media_type') ?? (!empty($row->newspaper_id) ? 'newspaper' : 'agency');
                @endphp

                <div class="form-padding">
                    <div class="row mb-3 mx-5">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="media_type">Media type</label>
                            <select name="media_type" id="media_type" class="form-select" required>
                                <option value="newspaper" @selected($mediaType === 'newspaper')>Newspaper</option>
                                <option value="agency" @selected($mediaType === 'agency')>Agency</option>
                            </select>
                            @error('media_type')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="media_name">Media name (optional)</label>
                            <input type="text" name="media_name" id="media_name" class="form-control"
                                value="{{ old('media_name', $row->media_name) }}" placeholder="Optional display name" />
                            @error('media_name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3 mx-5" id="np_row">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="newspaper_id">Newspaper</label>
                            <select name="newspaper_id" id="newspaper_id" class="form-select">
                                <option value="">— Select —</option>
                                @foreach ($newspapers as $n)
                                    <option value="{{ $n->id }}"
                                        @selected((old('newspaper_id') ?? $row->newspaper_id) == $n->id)>
                                        {{ $n->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('newspaper_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3 mx-5" id="ag_row">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="agency_id">Agency</label>
                            <select name="agency_id" id="agency_id" class="form-select">
                                <option value="">— Select —</option>
                                @foreach ($agencies as $a)
                                    <option value="{{ $a->id }}" @selected((old('agency_id') ?? $row->agency_id) == $a->id)>
                                        {{ $a->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('agency_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3 mx-5">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="bank_name">Bank name</label>
                            <input type="text" name="bank_name" id="bank_name" class="form-control" required
                                value="{{ old('bank_name', $row->bank_name) }}" />
                            @error('bank_name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="account_title">Account title</label>
                            <input type="text" name="account_title" id="account_title" class="form-control" required
                                value="{{ old('account_title', $row->account_title) }}" />
                            @error('account_title')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3 mx-5">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="account_number">Account number</label>
                            <input type="text" name="account_number" id="account_number" class="form-control" required
                                value="{{ old('account_number', $row->account_number) }}" />
                            @error('account_number')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="buttons-div flex">
                    <button type="submit" class="custom-primary-button">Update</button>
                    <a href="{{ route('master.mediaBankDetail.index') }}" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const type = document.getElementById('media_type');
            const npRow = document.getElementById('np_row');
            const agRow = document.getElementById('ag_row');
            const npSel = document.getElementById('newspaper_id');
            const agSel = document.getElementById('agency_id');

            function sync() {
                if (type.value === 'agency') {
                    npRow.classList.add('d-none');
                    agRow.classList.remove('d-none');
                    if (npSel) npSel.value = '';
                } else {
                    agRow.classList.add('d-none');
                    npRow.classList.remove('d-none');
                    if (agSel) agSel.value = '';
                }
            }

            type.addEventListener('change', sync);
            sync();
        });
    </script>
@endpush


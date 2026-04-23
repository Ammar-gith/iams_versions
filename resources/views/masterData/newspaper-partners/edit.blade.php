@extends('layouts.masterVertical')

@push('content')
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('newspaperPartner.update', $partner->id) }}" method="POST" class="card-body"
                style="padding: 0;">
                @csrf
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Edit newspaper partner</h5>
                </div>

                <div class="form-padding">
                    <div class="row mb-3 mx-5">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end">Newspaper</label>
                            <input type="text" class="form-control" readonly
                                value="{{ $partner->newspaper->title ?? '—' }}" />
                        </div>
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="partner_name">Partner name</label>
                            <input type="text" name="partner_name" id="partner_name" class="form-control" required
                                value="{{ old('partner_name', $partner->partner_name) }}" />
                            @error('partner_name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3 mx-5">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="share_percentage">Share %</label>
                            <input type="number" name="share_percentage" id="share_percentage" class="form-control" required
                                step="0.01" min="0.01" max="100"
                                value="{{ old('share_percentage', $partner->share_percentage) }}" />
                            @error('share_percentage')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="media_bank_detail_id">Bank account</label>
                            <select name="media_bank_detail_id" id="media_bank_detail_id" class="form-select" required>
                                @foreach ($banks as $b)
                                    <option value="{{ $b->id }}" @selected(old('media_bank_detail_id', $partner->media_bank_detail_id) == $b->id)>
                                        {{ $b->bank_name }} — {{ $b->account_number }} ({{ $b->account_title }})
                                    </option>
                                @endforeach
                            </select>
                            @error('media_bank_detail_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3 mx-5">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="sort_order">Sort order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" min="0"
                                value="{{ old('sort_order', $partner->sort_order) }}" />
                        </div>
                        <div class="col-sm-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                    @checked(old('is_active', $partner->is_active))>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="buttons-div flex">
                    <button type="submit" class="custom-primary-button">Update</button>
                    <a href="{{ route('master.newspaperPartner.index') }}" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endpush

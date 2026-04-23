@extends('layouts.masterVertical')

@push('content')
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $partner->partner_name }}</h5>
                    <a href="{{ route('master.newspaperPartner.edit', $partner->id) }}" class="custom-primary-button btn-sm">Edit</a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Newspaper</dt>
                        <dd class="col-sm-9">{{ $partner->newspaper->title ?? '—' }}</dd>
                        <dt class="col-sm-3">Share %</dt>
                        <dd class="col-sm-9">{{ number_format((float) $partner->share_percentage, 2) }}%</dd>
                        <dt class="col-sm-3">Bank</dt>
                        <dd class="col-sm-9">{{ $partner->mediaBankDetail->bank_name ?? '—' }}</dd>
                        <dt class="col-sm-3">Account title</dt>
                        <dd class="col-sm-9">{{ $partner->mediaBankDetail->account_title ?? '—' }}</dd>
                        <dt class="col-sm-3">Account no.</dt>
                        <dd class="col-sm-9">{{ $partner->mediaBankDetail->account_number ?? '—' }}</dd>
                        <dt class="col-sm-3">Active</dt>
                        <dd class="col-sm-9">{{ $partner->is_active ? 'Yes' : 'No' }}</dd>
                        <dt class="col-sm-3">Sort</dt>
                        <dd class="col-sm-9">{{ $partner->sort_order }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endpush

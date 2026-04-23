@extends('layouts.masterVertical')

@push('content')
    <x-breadcrumb :items="$breadcrumbs" />

    @php
        $isNp = !empty($row->newspaper_id);
        $type = $isNp ? 'Newspaper' : 'Agency';
        $mediaLabel = $row->media_name ?: ($isNp ? ($row->newspaper->title ?? '—') : ($row->agency->name ?? '—'));
    @endphp

    <div class="row">
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Media bank detail</h5>
                    <a href="{{ route('master.mediaBankDetail.edit', $row->id) }}" class="custom-primary-button btn-sm">Edit</a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Type</dt>
                        <dd class="col-sm-9">{{ $type }}</dd>

                        <dt class="col-sm-3">Media</dt>
                        <dd class="col-sm-9">{{ $mediaLabel }}</dd>

                        <dt class="col-sm-3">Bank name</dt>
                        <dd class="col-sm-9">{{ $row->bank_name }}</dd>

                        <dt class="col-sm-3">Account title</dt>
                        <dd class="col-sm-9">{{ $row->account_title }}</dd>

                        <dt class="col-sm-3">Account number</dt>
                        <dd class="col-sm-9"><code>{{ $row->account_number }}</code></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endpush


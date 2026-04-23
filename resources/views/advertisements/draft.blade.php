@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header col-md-12 d-flex justify-content-start align-items-center">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="me-3">Draft Advertisements</h5>
                    @if ($draftAds->isEmpty())
                        <span class="text-muted">No ads to show</span>
                    @endif
                </div>
                @if ($draftAds->isNotEmpty())
                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scrop="col">Department</th>
                                    <th scope="col">Office</th>
                                    <th scope="col">Ad Category</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($draftAds as $key => $draftAd)
                                    <tr>
                                        <td>{{ ++$key }}</td> <!-- Serial Number -->
                                        <td>{{ $draftAd->department->name }}</td>
                                        <td>{{ $draftAd->office->name }}</td>
                                        <td>{{ $draftAd->classified_ad_type->type }}</td>
                                        <td><span
                                                class="badge rounded-pill bg-label-danger">{{ $draftAd->status->title }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-3">

                                                {{-- View --}}
                                                <a href="{{ route('advertisements.draft.show', $draftAd->id) }}"
                                                    class="text-decoration-none" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="View Ad">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>

                                                {{-- Edit (process) --}}
                                                @can('view process action')
                                                    <a href="{{ route('advertisements.draft.edit', $draftAd->id) }}"
                                                        class="text-decoration-none" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Edit Ad">
                                                        <i class="bx bx-cog fs-4 bx-icon"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush

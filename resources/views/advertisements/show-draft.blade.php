@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="col-xxl">
            <div class="card">
                {{-- Title (Header) --}}
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                        <h5 class="h5-reset-margin">
                            <h5 class="h5-reset-margin">
                                {{ $pageTitle == 'Inprogress Ad Details &#x2053; IAMS-IPR' ? 'Inprogress Ad Details' : 'Advertisement Details' }}
                            </h5>
                        </h5>
                    </div>
                </div>

                {{-- Body --}}
                <div class="card-body mt-3">
                    {{--  Advertisement files --}}
                    <div class="row mb-3   ">
                        {{-- Covering Letter --}}
                        <div class="col-sm-4">
                            <label class="form-label-x mb-3" for="covering-letters">Covering Letter File</label>
                            <br>
                            {{-- @if ($covering_letter_files->isNotEmpty())
                                @foreach ($covering_letter_files as $covering_letter_file)
                                    <a href="{{ route('advertisements.full-file-show', [$advertisement->id, $covering_letter_file->id]) }}">
                                        <img src="{{ $covering_letter_file->getUrl('thumb') }}" alt="Covering Letter" class="img-fluid">
                                    </a>
                                @endforeach
                            @else
                                <p>No covering letter uploaded.</p>
                            @endif --}}
                        </div>

                        {{-- Urdu Ad files --}}
                        <div class="col-sm-4">
                            <label class="form-label-x mb-3" for="covering-letters">Urdu Ad File</label>
                            <br>
                            {{-- @if ($urdu_ad_files->isNotEmpty())
                                @foreach ($urdu_ad_files as $urdu_ad_file)
                                    <a href="{{ route('advertisements.full-file-show', [$advertisement->id, $urdu_ad_file->id]) }}">
                                        <img src="{{ $urdu_ad_file->getUrl('thumb') }}" alt="Urdu Ad" class="img-fluid">
                                    </a>
                                @endforeach
                            @else
                                <p>No urdu ad uploaded.</p>
                            @endif --}}
                        </div>

                        {{-- English Ad files --}}
                        <div class="col-sm-4">
                            <label class="form-label-x mb-3" for="covering-letters">English Ad File</label>
                            <br>
                            {{-- @if ($english_ad_files->isNotEmpty())
                                @foreach ($english_ad_files as $english_ad_file)
                                    <a href="{{ route('advertisements.full-file-show', [$advertisement->id, $english_ad_file->id]) }}">
                                        <img src="{{ $english_ad_file->getUrl('thumb') }}" alt="English Ad" class="img-fluid">
                                    </a>
                                @endforeach
                            @else
                                <p>No english ad uploaded.</p>
                            @endif --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--! / End Page Content --}}
@endpush

@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Form --}}
    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('newsPosRate.update', $news_pos_rate->id) }}" method="POST" class="card-body" enctype="multipart/form-data" style="padding: 0;">
                @csrf

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">Update Position & Rate</h5>
                </div>

                <div class="form-padding">
                    {{-- Newspaper position --}}
                    <div class="row mb-3 mx-5 ">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="position">Positions</label>
                            <input type="text" name="position" id="alignment-position" class="form-control" required
                                value="{{ $news_pos_rate->position }}" />
                        </div>
                        @error('position')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Newspaper rate --}}
                    <div class="row mb-3 mx-5  ">
                        <div class="col-sm-6">
                            <label class=" col-form-label text-sm-end" for="rates">Rates</label>
                            <input type="text" name="rates" id="alignment-rates" class="form-control" required
                                value="{{ $news_pos_rate->rates }}" />
                        </div>
                        @error('rate')
                            <span class="alert alert-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save User --}}
                    <button type="submit" class="custom-primary-button">Update</button>

                    {{-- Cancel --}}
                    <a href="{{ route('master.newsPosRate.index') }}" type="button" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    {{-- ! / End Form --}}
@endpush

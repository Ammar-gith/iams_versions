@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <h5 class="h5-reset-margin">Cheque Receipts-NP's</h5>
                @if ($chequeReceiptsNps->isEmpty())
                    <span class="text-muted">No Bills to show</span>
                @endif
            </div>

            {{-- Get the authenticated logged in user --}}
            {{-- @php
                $user = Auth::User();
            @endphp --}}

            {{-- Show ads if any --}}
            {{-- @if ($billClassifiedAds->isNotEmpty()) --}}
            <div class="table-responsive text-nowrap">
                <table class="table w-100">
                    <thead>
                        <tr>
                            <th style="padding-right: 0 !important;">S. No.</th>
                            <th>Challan ID</th>
                            <th>INF No.</th>
                            <th>Office</th>
                            <th>Cheque Number</th>
                            <th>Cheque Date</th>
                            <th>Amount</th>
                            <th>SBP Verify Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($chequeReceiptsNps as $key => $chequeReceiptNp)
                            <tr>
                                <td>{{ ++$key }}</td> <!-- Serial Number -->
                                <td>{{ $chequeReceiptNp->id }}</td> <!-- Serial Number -->
                                <td>{{ implode(', ', $chequeReceiptNp->inf_number ?? '') }}</td>
                                <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                    {{ \Illuminate\Support\Str::words($chequeReceiptNp->office->ddo_name ?? '', 500, '...') }}
                                </td>
                                <td>{{ $chequeReceiptNp->cheque_number ?? '' }}</td>
                                <td>{{ \Carbon\Carbon::parse($chequeReceiptNp->cheque_date)->toFormattedDateString() }}
                                <td>{{ $chequeReceiptNp->total_amount ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($chequeReceiptNp->sbp_verification_date)->toFormattedDateString() ?? '-' }}
                                </td>

                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center align-items-center">

                                        {{-- View --}}
                                        <div class="action-item custom-tooltip">
                                            <a href="">
                                                <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                            </a>
                                            <span class="tooltip-text">View Ad</span>
                                        </div>


                                        <div class="action-item custom-tooltip">
                                            <a data-bs-toggle="modal" data-bs-target="#editModal{{ $chequeReceiptNp->id }}">
                                                <i class='bx bx-edit fs-4 bx-icon'></i>
                                                {{-- <span class="bx-icon test-sm">Newspaper Bills</span> --}}
                                            </a>
                                            <span class="tooltip-text">Edit</span>
                                        </div>


                                        {{-- Edit (media form) --}}
                                        <div class="action-item custom-tooltip">
                                            <a
                                                href="{{ route('billings.chequeReceipts.newspapers.receipt', $chequeReceiptNp->id) }}">
                                                <i class='bx bx-money fs-4 bx-icon'></i>
                                            </a>
                                            <span class="tooltip-text">Receipts</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- <div class="custom-pagination">
                        {{ $advertisements->links() }}
                    </div> --}}
            </div>
            {{-- @endif --}}
        </div>
    </div>
    {{-- ! / Page Content --}}
    {{-- Modal for cheque receipt Nps verification --}}
    <!-- Modal -->
    @foreach ($chequeReceiptsNps as $chequeReceiptNp)
        <div class="modal fade" id="editModal{{ $chequeReceiptNp->id }}" tabindex="-1" aria-labelledby="editModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editModalLabel">Edit</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Page Content --}}
                        <div class="row custom-paddings">
                            {{-- Newspaper Billing --}}
                            {{-- @if ($newspapers->isNotEmpty()) --}}
                            <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">

                                {{-- Title (Header) --}}
                                <div class="card-header-table">
                                    <div class="d-flex align-items-center gap-3">
                                        <a href="{{ url()->previous() }}" class="back-button"><i
                                                class='bx bx-arrow-back'></i></a>
                                        <h5 class="h5-reset-margin">Cheque Receipt NP's</h5>
                                    </div>
                                </div>

                                {{-- Body --}}
                                <form method="POST"
                                    action="{{ route('billings.chequeReceipts.newspapers.update', $chequeReceiptNp->id) }}"
                                    enctype="multipart/form-data" class="card-body" style="padding: 0;">
                                    @csrf

                                    {{-- Data --}}
                                    <div class="form-padding">
                                        <div class="row g-4">
                                            <div class="col-md-12">
                                                <div class="row g-1">

                                                    {{-- Treasury Verify Date --}}
                                                    <div class="col-md-12">
                                                        <label class="form-label-x col-form-label" for="memo-date">Treasury
                                                            Verify Date
                                                            Date</label>
                                                        <input type="date" id="tr-challan-verification-date"
                                                            name="tr_challan_verification_date" class="form-control"
                                                            value="{{ $chequeReceiptNp->tr_challan_verification_date?->format('Y-m-d') ?: '-' }}" />
                                                    </div>

                                                    {{-- SBP Verify Date --}}
                                                    <div class="col-md-12">
                                                        <label class="form-label-x col-form-label" for="cheque-date">SBP
                                                            Verify
                                                            Date
                                                        </label>
                                                        <input type="date" id="sbp-verification-date"
                                                            name="sbp_verification_date" class="form-control"
                                                            value="{{ $chequeReceiptNp->sbp_verification_date?->format('Y-m-d') ?: '-' }}" />
                                                    </div>

                                                    {{-- Cheque Number --}}
                                                    <div class="col-md-12">
                                                        <label class="form-label-x col-form-label" for="cheque-no">Challan #
                                                        </label>
                                                        <input type="text" id="cheque-no" name="challan_number"
                                                            class="form-control"
                                                            value="{{ $chequeReceiptNp->challan_number ?? '' }}" />
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>


                            </div>
                            {{-- @endif --}}
                        </div>
                        {{-- ! / Page Content --}}
                        <div class="modal-footer">
                            <button type="button" class="custom-secondary-button" data-bs-dismiss="modal">Close</button>
                            {{-- Save challans --}}
                            <button type="submit" class="custom-primary-button">Submit</button>
                        </div>
                        {{-- Buttons --}}
                        {{-- <div class="buttons-div flex">
                                    {{-- Save challans --}
                                    <button type="submit" class="custom-primary-button">Submit</button>
                                </div> --}}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endpush

@push('scripts')
    <script>
        fliptkpr("#tr-challan-verification-date", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"
        });
        flatpickr("#sbp-verification-date", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"
        });
    </script>
@endpush

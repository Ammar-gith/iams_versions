@extends('layouts.masterVertical')
@push('style')
    <style>
        .custom-table th,
        .custom-table td {
            padding: 15px 20px;
            /* Bigger cell padding */
            font-size: 15px;
            /* Slightly bigger text */
            vertical-align: middle;
            /* Center content */
        }

        .custom-table th {
            background-color: #343a40;
            /* Dark header */
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-table tr:nth-child(even) {
            background-color: #f8f9fa;
            /* Zebra effect */
        }

        .custom-table {
            border: 2px solid #dee2e6;
            /* Stronger border */
            border-radius: 8px;
            /* Rounded corners */
            overflow: hidden;
        }

        .custom-table td,
        .custom-table th {
            border: 1px solid #dee2e6;
            /* Cell borders */
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        {{-- Title (Header) --}}
        <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                <h5 class="h5-reset-margin">Newspaper Bill Details</h5>
            </div>
            @if (!empty($inf_number))
                <div class="inf-badge">
                    <span class="icon"><i class='bx bxs-purchase-tag'></i></span>
                    <span class="label">INF No.</span>
                    <span class="number">{{ $inf_number }}</span>
                </div>
            @endif
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-striped align-middle text-center custom-table">
                <thead class="table-dark">
                    <tr>

                        {{-- <th>S.No.</th> --}}
                        <th>Newspaper</th>
                        <th>Position</th>
                        <th>Rate</th>
                        {{-- <th>Space</th> --}}
                        <th>Total<br>Space</th>
                        <th>Ins.</th>
                        <th>Est. Cost</th>
                        <th>2% KPRA<br>Tax on 85%<br> Newspaper<br>Amount</th>
                        <th>10% KPRA <br>Tax on 15%<br> Agency<br>Amount</th>
                        <th>Total Amount<br> with Taxes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($billdetails as $billdetail)
                        @foreach ($billdetail->newspaper_titles as $index => $newspaper)
                            <tr>
                                {{-- <td>{{ $index++ }}</td> --}}
                                <td>{{ $newspaper }}</td>
                                <td>{{ $billdetail->placements[$index] ?? '' }}</td>
                                <td>{{ $billdetail->rates_with_placement[$index] ?? '' }}</td>
                                {{-- <td>{{ $billdetail->spaces[$index] ?? '' }}</td> --}}
                                <td>{{ $billdetail->total_spaces[$index] ?? '' }}</td>
                                <td>{{ $billdetail->insertions[$index] ?? '' }}</td>
                                <td>{{ $billdetail->total_cost_per_newspaper[$index] ?? '' }}</td>
                                <td>{{ $billdetail->kpra_2_percent_on_85_percent_newspaper[$index] ?? '' }}</td>
                                <td>{{ $billdetail->kpra_10_percent_on_15_percent_agency[$index] ?? '' }}</td>
                                <td>{{ $billdetail->total_amount_with_taxes[$index] ?? '' }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" class="text-center fw-bold">TOTAL</td>
                            <td class="fw-bold">{{ $billdetail->printed_no_of_insertion }}</td>
                            <td class="fw-bold">{{ $billdetail->printed_bill_cost }}</td>
                            <td></td>
                            <td></td>
                            <td class="fw-bold">{{ $billdetail->printed_total_bill }}</td>
                        </tr>
                        <tr>
                            <td colspan="8" class="text-center fw-bold">TOTAL DUES</td>
                            <td class="text-danger fw-bold">{{ $billdetail->printed_bill_cost }}</td>
                        </tr>
                        <tr>
                            <td colspan="8" class="text-center fw-bold">KPRA Sale<br> Tax On<br> Services</td>
                            <td class="text-danger fw-bold">{{ $billdetail->kpra_tax }}</td>
                        </tr>
                        <tr>
                            <td colspan="8" class="text-center fw-bold">TOTAL BILL<br> AMOUNT</td>
                            <td class="text-danger fw-bold">{{ $billdetail->printed_total_bill }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ! / Page Content --}}
@endpush

@push('scripts')
@endpush

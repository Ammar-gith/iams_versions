@extends('layouts.masterVertical')
{{-- @push('styles')
    <style>
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
        }

        .info-box h6 {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-box p {
            color: #212529;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .table td {
            vertical-align: middle;
        }

        .table-light td {
            background-color: #f8f9fa;
        }

        .table-success td {
            background-color: #d1e7dd;
        }

        .table-info td {
            background-color: #cfe2ff;
        }

        .table-warning td {
            background-color: #fff3cd;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #f8f9fa;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s;
        }

        .back-button:hover {
            background: #0d6efd;
            color: white;
        }

        .list-group-item {
            padding: 0.5rem 1rem;
        }
    </style>
@endpush --}}

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row custom-paddings">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5-reset-margin">PLA Account Details</h5>
                </div>
            </div>

            <div class="card-body">
                {{-- Basic Account Information --}}
                <div class="row mb-4 mt-4">
                    <div class="col-md-10">
                        <div class="d-flex justify-content-between info-box">
                            <h6>Office/Deptt</h6>
                            <p class="fw-bold">{{ $plaAcountData->office->ddo_name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="d-flex justify-content-between info-box">
                            <h6>Account ID</h6>
                            <p class="fw-bold">{{ $plaAcountData->id }}</p>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="d-flex justify-content-between info-box">
                            <h6>Cheque Number</h6>
                            <p class="fw-bold">{{ $plaAcountData->cheque_no }}</p>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between info-box">
                            <h6>Cheque Date</h6>
                            <p class="fw-bold">
                                {{ \Carbon\Carbon::parse($plaAcountData->cheque_date)->toFormattedDateString() }}</p>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between info-box">
                            <h6>Challan Number</h6>
                            <p class="fw-bold">{{ $plaAcountData->challan_no }}</p>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="d-flex justify-content-between info-box">
                            <h6>Total Amount</h6>
                            <p class="fw-bold text-success">Rs.
                                {{ number_format(round($plaAcountData->total_cheque_amount)) }} /-</p>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Detailed Breakdown --}}
                <h5 class="mb-4">Newspaper Amounts Breakdown</h5>

                @if ($plaAcountData->plaAccountItems->isNotEmpty())
                    @php
                        // Group items by inf_number
                        $groupedItems = $plaAcountData->plaAccountItems->groupBy('inf_number');
                        $grandTotal = 0;
                        $totalTaxes = 0;
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>INF Number</th>
                                    <th>Newspaper</th>
                                    <th>Agency</th>
                                    <th class="text-end">Commission (Rs.)</th>
                                    <th class="text-end">Amount (Rs.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupedItems as $infNumber => $items)
                                    @php
                                        $infSubtotal = $items->sum('newspaper_amount');
                                        $infCommissionTotal = $items->sum('agency_commission_amount');
                                        $newspaperKpraTax = $items->sum('kpra_2_percent_on_85_percent_newspaper');
                                        // dd($newspaperKpraTax);
                                        $agencyKpraTax = $items->sum('kpra_10_percent_on_15_percent_agency');
                                        $totalTaxes = $newspaperKpraTax + $agencyKpraTax;
                                        $grandTotal = $infSubtotal + $infCommissionTotal + $totalTaxes;
                                    @endphp
                                    {{-- INF header row --}}
                                    <tr class="table-info">
                                        <td colspan="5" class="fw-bold">
                                            INF: {{ $infNumber }}
                                            <span class="badge bg-primary ms-2">{{ $items->count() }} newspaper(s)</span>
                                        </td>
                                    </tr>
                                    {{-- Newspaper rows --}}
                                    @foreach ($items as $item)
                                        <tr>
                                            <td></td> {{-- Empty for INF number column --}}
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class='bx bx-news text-primary me-2'></i>
                                                    <div>
                                                        <div class="fw-medium">
                                                            {{ $item->newspaper->title ?? 'Unknown' }}
                                                        </div>
                                                        @if ($item->newspaper && $item->newspaper->newspaper_code)
                                                            <small class="text-muted">Code:
                                                                {{ $item->newspaper->newspaper_code }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($item->adv_agency_id && $item->agency)
                                                    {{ $item->agency->name ?? 'Agency #' . $item->adv_agency_id }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if ($item->agency_commission_amount > 0)
                                                    {{ number_format($item->agency_commission_amount) }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($item->newspaper_amount) }}</td>
                                        </tr>
                                    @endforeach
                                    {{-- Subtotal for INF --}}
                                    <tr class="table-light">
                                        <td colspan="2" class="text-end fw-bold">Subtotal for INF {{ $infNumber }}:
                                        </td>
                                        <td></td>
                                        <td class="text-end fw-bold">
                                            @if ($infCommissionTotal > 0)
                                                Total Commission: {{ number_format($infCommissionTotal) }}
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($infSubtotal) }}</td>
                                    </tr>
                                @endforeach
                                {{-- Grand Total Row --}}
                                <tr class="table-success">
                                    <td colspan="4" class="text-end fw-bold fs-5">GRAND TOTAL:</td>
                                    <td class="text-end fw-bold fs-5">{{ number_format($grandTotal) }}</td>
                                </tr>
                                @if (abs($grandTotal - $plaAcountData->total_cheque_amount) > 1)
                                    <tr class="table-warning">
                                        <td colspan="4" class="text-end">
                                            <small class="text-muted">Stored total:</small>
                                        </td>
                                        <td class="text-end">
                                            <small class="text-muted">Rs.
                                                {{ number_format(round($plaAcountData->total_cheque_amount)) }}</small>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <div class="d-flex">
                            <i class='bx bx-info-circle fs-4 me-3'></i>
                            <div>
                                <h6 class="alert-heading mb-1">No detailed breakdown available</h6>
                                <p class="mb-0">This PLA account doesn't have newspaper amount details or the data format
                                    is incorrect.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endpush

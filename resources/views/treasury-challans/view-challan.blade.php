@extends('layouts.masterVertical')

@push('style')
    <style>
        @page {
            size: legal landscape;
            margin: 15mm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 13px;
            color: #000;
        }

        .challan-container {
            display: flex;
            width: 100%;
            justify-content: space-between;

        }

        .challan {
            border: 1px solid #000;
            box-sizing: border-box;
        }

        .challan-no {
            background-color: #60d88e;
        }

        .challan-header {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            background-color: #77e28e;
        }


        .challan-subheader {
            text-align: center;
            font-size: 12px;
            margin-top: 2px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }

        .challan-table,
        .challan-table td,
        .challan-table th {
            border: 1px solid #000;
            border-collapse: collapse;
            padding: 3px 5px;
            vertical-align: middle;
        }

        .challan-table {
            width: 100%;
            margin: 2px;
        }

        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }

        .bold {
            font-weight: bold;
        }

        .amount-section {
            display: flex;
            justify-content: space-between;
            margin-top: 3px;
        }

        .amount-section span {
            font-weight: bold;
        }

        .green-box {
            background-color: #60d88e;
            color: black;
            font-weight: bold;
            text-align: center;
            padding: 3px;
        }

        .dashed-line {
            border-left: 1px dashed #000;
            height: auto;
            margin: 0 4px;
        }

        .amount-row {
            font-weight: bold;
            border-top: 1px solid #000;
            text-align: right;
            padding: 2px 0;
            font-size: 12px;
        }

        .amount-words {
            padding: 10px 0px 10px 5px;
            text-align: left;
        }

        /* Update the main signature area styles from the previous prompt (No changes here) */
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            font-size: 0.8em;
            width: 100%;
        }

        .signature-area div {
            width: 30%;
            border-top: 1px solid #333;
            padding-top: 3px;
            text-align: center;
            margin: 0 5px;
        }

        /* .challan-container::before {
                            content: "✂ CUT HERE ✂";
                            /* Added top and bottom scissor icons *
                            position: absolute;
                            top: 0;
                            bottom: 0;
                            left: 50%;
                            transform: translateX(-50%);
                            z-index: 10;
                            pointer-events: none;

                            /* Styling the line and text *
                            font-size: 0.75em;
                            color: #999;
                            font-weight: bold;
                            /* Use 'vertical-rl' to display the text vertically *
                            writing-mode: vertical-rl;
                            letter-spacing: 2px;
                            border-right: 1px dotted #999;
                            padding: 10px 0;
                            /* Increase margin/height to space out the content *
                            height: 100%;
                            display: flex;
                            /* Use flexbox to center content vertically *
                            align-items: center;
                            justify-content: center;
                            line-height: 1.2;
                            text-align: center;
                        } */

        @media print {
            .print-btn {
                display: none;
            }

            /* Hide non-print elements */
            .layout-navbar,
            .layout-menu,
            .card-header,
            .content-footer,
            .h5-reset-margin,
            .breadcrumb {
                display: none !important;
            }


        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    {{-- Apply the custom class to the container that holds the three challans --}}
    <div class="row challan-view-container">
        <div class="card mb-4" style="padding: 0;">
            {{-- This header remains visible on screen to allow printing --}}
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <h5 class="h5-reset-margin">Challan Form</h5>
                <div>
                    {{-- Note: 'window.print()' is usually the simplest print trigger --}}
                    <a href="javascript:void(0)" onclick="window.print()" class="btn btn-warning btn-sm rounded-pill" type="button"><i
                            class="bx bx-printer"></i> Print Challans</a>
                    {{-- Assuming the PDF download route is correct --}}
                    <a href="{{ route('billings.treasury-challans.downloadChallanPdf', $treasuryChallan->id) }}"
                        class="btn btn-info btn-sm rounded-pill" type="button"><i class="bx bx-download"></i> Download PDF</a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="container-fluid mt-3 mb-3">
                    <div class="challan-container">
                        <!-- Challan Copy 1 -->
                        @for ($i = 0; $i <= 2; $i++)
                            <div class="challan">
                                <div class="challan-header">CHALLAN OF CHEQUE PAID INTO THE TREASURY/SUB-TREASURY</div>
                                <div class="challan-subheader">SBP / NBP, PESHAWAR.</div>

                                <table class="challan-table">
                                    <tr>
                                        <td colspan="2" class="bold challan-no">Challan No:</td>
                                        <td colspan="3" class="challan-no"> {{ $treasuryChallan->memo_number }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-center">Be filled by remitter. To be filled by the
                                            Departmental Officer of
                                            the Treasury</td>
                                    </tr>
                                    <tr>
                                        <td>BY Whom Tendered</td>
                                        <td>Names & designation</td>
                                        <td>Full particulars</td>
                                        <td>Amount (Rs)</td>
                                        <td class="vertical-text">Order to the Bank<br>Provincial</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="4">Cheque No:<br><br> {{ $treasuryChallan->cheque_number }}</td>
                                        <td rowspan="4">Director General Information<br>Govt of KP<br>(PR 4075)</td>
                                        <td rowspan="4">{{ $treasuryChallan->office->ddo_name }}</td>
                                        <td rowspan="4">{{ $finalAmount }}/-</td>
                                        <td class="text-center green-box">G-11217</td>
                                    </tr>
                                    <tr></tr>
                                    <tr></tr>
                                    <tr></tr>

                                    <tr>
                                        <td colspan="1">Cheque Date:</td>
                                        <td colspan="1"> {{ $treasuryChallan->cheque_date->format('d-m-Y') }}</td>
                                        <td colspan="2">
                                            @if (!empty($infNumbers))
                                                @foreach ($infNumbers as $number)
                                                    <span class="inf-item">{{ $number }}</span><br>
                                                @endforeach
                                            @else
                                                <span class="inf-item">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>

                                <div class="amount-words"><span class="fw-500" style="font-weight: 600;">Amount in Words:
                                    </span>
                                    {{ $rupeesWords }}
                                </div>
                                <div class="amount-row"><span>Total Amount: </span>{{ $finalAmount }}/-</div>

                                {{-- Signature Area --}}
                                <div class="signature-area">

                                    {{-- 1. Cashier / Received By --}}
                                    <div>
                                        <span class="text-bold text-uppercase">Date</span><br>
                                        ACCOUNTANT
                                    </div>

                                    {{-- 2. Authorised Officer --}}
                                    <div>
                                        Head of Account Verification by Treasury Officer
                                    </div>

                                </div>
                            </div>

                            <!-- Divider -->
                            @if ($i < 2)
                                <div class="dashed-line"></div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush

@push('scripts')
@endpush

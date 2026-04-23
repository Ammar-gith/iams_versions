@extends('layouts.masterVertical')

@push('style')
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.85em;
            color: #333;
            /* background-color: #f7f7f7; */
        }

        .layout-menu,
        .layout-navbar,
        .card-header,
        .content-footer {
            display: none;
        }

        /* ==== SAFE DEPOSIT SLIP LAYOUT ==== */
        .deposit-slips-container {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .deposit-slip {
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: white;
            vertical-align: top;
            padding: 10px;
        }

        .dashed-line {
            border-left: 1px dashed #000;
            height: auto;
            margin: 0 4px;
        }


        .nba-logo {
            height: 35px;
        }

        .deposit-slip-title {
            font-weight: 600;
            font-size: 1.3em;
            color: #238839;
            text-align: center;
        }

        .copy-info {
            font-size: 0.75em;
            color: #666;
            text-align: right;
        }

        /* Replace flex with table for header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .field-group {
            margin-bottom: 6px;
            font-size: 0.9em;
        }

        .field-group input {
            border: none;
            border-bottom: 1px solid #555;
            background: transparent;
        }

        .details-box {
            border: 1px solid #aaa;
            border-radius: 3px;
            padding: 8px;
            margin-top: 10px;
            background-color: #fcfcfc;
            font-size: 0.85em;
        }

        .details-box1 {}

        .pk-nbp-a-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .pk-nbp-a-grid td {
            border: 1px solid #aaa;
            text-align: center;
            font-weight: bold;
            padding: 4px 0;
        }

        /* FIXED: Transfer / Cash section using table */
        .transfer-cash-section {
            width: 100%;
            border: 1px solid #aaa;
            border-top: none;
            background-color: #77e28e;
            font-weight: bold;
            text-align: center;
        }

        .transfer-cash-section td {
            width: 50%;
            padding: 5px 0;
        }

        .table-denomination {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 0.8em;
        }

        .table-denomination th,
        .table-denomination td {
            border: 1px solid #aaa;
            padding: 5px;
            vertical-align: top;
        }

        .total-amount-display {
            font-weight: bold;
            padding: 5px 10px;
            text-align: right;
            border-top: 2px solid #333;
            margin-top: 5px;
        }

        .amount-in-words {
            margin-top: 5px;
            font-size: 0.9em;
            font-weight: 600;
        }

        /* FIXED: Signature area using table */
        .signature-area {
            width: 100%;
            border-collapse: collapse;
            margin-top: 60px;
            font-size: 0.8em;
        }

        .signature-area td {
            width: 33%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 5px;
            vertical-align: top;
        }

        @page {
            size: legal landscape;
            margin: 0.4in;
        }

        .cut-line-cell {
            width: 1%;
            border-right: 1px dotted #8d8989;
            border-left: none;
            margin: 0;
            padding: 0;
            background: repeating-linear-gradient(to bottom,
                    #000 0,
                    #000 2px,
                    transparent 2px,
                    transparent 6px);
        }


        @media print {
            body {
                font-size: 0.75em !important;
                color: black !important;
                background: white !important;
                -webkit-print-color-adjust: exact;
            }

            .deposit-slip {
                border: 1px solid black !important;
            }

            .deposit-slip-title {
                color: black !important;
            }

            .table-denomination th,
            .table-denomination td {
                border-color: black !important;
            }

            .signature-area td {
                border-color: black !important;
            }
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row challan-view-container">
        <div class="card mb-4" style="padding:0;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="h5-reset-margin">Deposit Slip</h5>
                <div>
                    <a href="javascript:void(0)" onclick="window.print()" class="btn btn-warning btn-sm"><i
                            class="bx bx-printer"></i> Print Slips</a>
                    <a href="{{ route('billings.treasury-challans.downloadDepositSlipPdf', $treasuryChallan->id) }}"
                        class="btn btn-info btn-sm"><i class="bx bx-download"></i> Download PDF</a>
                </div>
            </div>

            <div class="card-body p-0">
                <table class="deposit-slips-container">
                    <tr>
                        @for ($i = 0; $i < 2; $i++)
                            <td class="deposit-slip">
                                <table class="header-table">
                                    <tr>
                                        <td style="width:25%;">
                                            <img src="{{ asset('assets/img/nbp-logo/NBP.png') }}" alt="NBP Logo"
                                                class="nba-logo">
                                        </td>
                                        <td class="deposit-slip-title" style="width:50%;">
                                            DEPOSIT SLIP
                                        </td>
                                        <td class="copy-info" style="width:25%;">
                                            @if ($i == 0)
                                                <span>BANK COPY (White)</span><br>
                                            @else
                                                <span>CUSTOMER COPY (Green)</span><br>
                                            @endif
                                            <span style="font-size: 0.9em;">(Not official unless validated)</span>
                                        </td>
                                    </tr>
                                </table>

                                <div class="field-group">
                                    <strong>Sr.No:</strong> <input type="text"
                                        value="{{ $treasuryChallan->memo_number }}" readonly>
                                    <strong style="margin-left:10px;">Date:</strong>
                                    <input type="text" value="{{ now()->format('d-m-Y') }}" readonly>
                                </div>

                                <div class="field-group">
                                    <strong>PAY-IN-SLIP TYPE:</strong> <span style="border-bottom:1px solid #555;">CASH /
                                        TRANSFER</span>
                                </div>

                                <div class="details-box">

                                    <div class="field-group">
                                        <strong>Brach:</strong> <input style="width: 100%;" type="text"
                                            value="{{ $treasuryChallan->bank_name }}" readonly>
                                    </div>

                                    <div class="field-group">
                                        <strong>Title Of Account:</strong> <input type="text" value="Govt Receipt"
                                            readonly>
                                        <strong style="margin-left:10px;">A/C Type:</strong>
                                        <span>Current<input type="text" readonly></span>
                                        <span>Saving<input type="text" readonly></span>
                                    </div>

                                    <div class="field-group">
                                        <strong>A/C No:</strong> <input type="text"
                                            value="{{ $treasuryChallan->bank_account_number }}" readonly>
                                        <strong style="margin-left:10px;">Currency:</strong>
                                        <span>PKR<input type="text" readonly></span>
                                        <span>USD<input type="text" readonly></span>
                                        <span>EURO<input type="text" readonly></span>
                                        <span>GBP<input type="text" readonly></span>
                                    </div>


                                    <table class="pk-nbp-a-grid">
                                        <tr>
                                            <td>P K</td>
                                            <td>N</td>
                                            <td>B</td>
                                            <td>P</td>
                                            <td>A</td>
                                        </tr>
                                    </table>

                                    <table class="transfer-cash-section">
                                        <tr>
                                            <td>Transfer</td>
                                            <td>Cash</td>
                                        </tr>
                                    </table>

                                    <table class="table-denomination">
                                        <thead>
                                            <tr>
                                                <th>Bank/Branch: (Drawee)</th>
                                                <th>Cheque/Instrument No.</th>
                                                <th>Denomination</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="text-align:center;">G-11217</td>
                                                <td style="text-align:center;">{{ $treasuryChallan->cheque_number }}</td>
                                                <td>
                                                    5000 x ___<br>
                                                    1000 x ___<br>
                                                    500 x ___<br>
                                                    100 x ___<br>
                                                    50 x ___<br>
                                                    20 x ___<br>
                                                    10 x ___<br>
                                                    5 x ___<br>
                                                    Coins ___<br>
                                                    <strong>Total Cash</strong>
                                                </td>
                                                <td style="text-align:center; vertical-align:bottom;">{{ $finalAmount }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="total-amount-display">
                                        TOTAL RS: {{ $finalAmount }}/-
                                    </div>

                                    <div class="amount-in-words">
                                        Total Amount in Words: {{ $rupeesWords }}
                                    </div>

                                    <table class="signature-area">
                                        <tr>
                                            <td>Received by:<br>Cashier’s Signature</td>
                                            <td>Authorised Officer</td>
                                            <td>
                                                Depositor’s Signature<br>
                                                Name: Asad Khan<br>
                                                CNIC: _______________<br>
                                                Cell: 0346-9150145
                                            </td>
                                        </tr>
                                    </table>
                            </td>

                            <!-- Cut Line Separator -->
                            @if ($i < 1)
                                <td class="cut-line-cell"></td>
                            @endif
                        @endfor
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endpush

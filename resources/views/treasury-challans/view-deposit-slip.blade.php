@extends('layouts.masterVertical')

@push('style')
    <style>
        /* Base styles for the entire page */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.85em;
            /* Slightly larger base font for better readability */
            color: #333;
            background-color: #f7f7f7;
            /* Light background */
        }

        /* --- Layout specific to the deposit slips --- */
        .deposit-slips-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            /* Reduced gap */
            padding: 15px 10px;
            width: 100%;
            max-width: 1200px;
            margin: auto;
            position: relative;
        }

        .deposit-slip {
            border: 1px solid #ccc;
            /* Lighter border */
            border-radius: 4px;
            /* Slightly rounded corners */
            width: 49%;
            /* Each slip takes almost half the container width */
            box-sizing: border-box;
            padding: 10px;
            /* Increased internal padding */
            background-color: white;
            position: relative;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            /* Softer shadow */
        }

        /* Updated: Cut Line Separator for Screen View with Scissor Icons */
        .deposit-slips-container::before {
            content: "✂ CUT HERE ✂";
            /* Added top and bottom scissor icons */
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            pointer-events: none;

            /* Styling the line and text */
            font-size: 0.75em;
            color: #999;
            font-weight: bold;
            /* Use 'vertical-rl' to display the text vertically */
            writing-mode: vertical-rl;
            letter-spacing: 2px;
            border-right: 1px dotted #999;
            padding: 10px 0;
            /* Increase margin/height to space out the content */
            height: 100%;
            display: flex;
            /* Use flexbox to center content vertically */
            align-items: center;
            justify-content: center;
            line-height: 1.2;
            text-align: center;
        }

        /* --- Individual Slip Components --- */
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            /* Separator line */
            padding-bottom: 5px;
        }

        .bank-branding {
            display: flex;
            align-items: center;
            font-size: 1.1em;
            font-weight: bold;
        }

        .nba-logo {
            width: 100%;
            height: 35px;
            margin-right: 5px;
            /* border-radius: 50%; */
            /* Make logo placeholder round */
        }

        .deposit-slip-title {
            font-weight: 600;
            /* Medium bold */
            font-size: 1.3em;
            color: #238839;
            /* Primary color for title */
            text-align: center;
            flex-grow: 1;
        }

        .copy-info {
            text-align: right;
            font-size: 0.75em;
            line-height: 1.2;
            color: #666;
        }

        .field-group {
            display: flex;
            align-items: flex-end;
            /* Align labels to the bottom of the input line */
            margin-bottom: 8px;
            font-size: 0.9em;
        }

        .field-group strong {
            margin-right: 5px;
            white-space: nowrap;
            /* Prevent label wrapping */
        }

        .field-group input,
        .input-line {
            border: none;
            border-bottom: 1px solid #555;
            /* Solid, clearer bottom line */
            padding: 2px 0;
            flex-grow: 1;
            font-size: 1em;
            background: transparent;
        }

        .date-input {
            width: 40px;
            /* Fixed width for date input segments */
            text-align: center;
            margin: 0 2px;
            outline: none;
        }

        .sr-no-input {
            width: 60px;
            margin-left: 5px;
            text-align: center;
            outline: none;
        }

        .branch-title-input {
            outline: none;
            border: none;
            text-align: center;
        }

        /* Account Details & Metadata */
        .details-box {
            border: 1px solid #aaa;
            border-radius: 3px;
            padding: 8px;
            margin-top: 10px;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 10px;
            background-color: #fcfcfc;
        }

        .account-fields {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .account-metadata {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 0.8em;
        }

        .checkbox-group {
            display: flex;
            gap: 5px;
            margin-top: 5px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
        }

        .checkbox-item span {
            border: 1px solid #555;
            width: 14px;
            height: 14px;
            text-align: center;
            line-height: 12px;
            padding: 0 8px;
            margin-left: 3px;
        }

        /* PK NBP A and Type Section */
        .pk-nbp-a-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            border: 1px solid #aaa;
            margin-top: 10px;
        }

        .pk-nbp-a-grid div {
            border-right: 1px solid #aaa;
            padding: 4px 0;
            text-align: center;
            font-weight: bold;
            font-size: 0.9em;
        }

        .pk-nbp-a-grid div:last-child {
            border-right: none;
        }

        .transfer-cash-section {
            display: flex;
            justify-content: space-around;
            border: 1px solid #aaa;
            border-top: none;
            padding: 5px 0;
            font-weight: bold;
            background-color: #77e28e;
        }

        /* Denomination Table */
        .table-denomination {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-denomination th,
        .table-denomination td {
            border: 1px solid #aaa;
            padding: 5px;
            /* Increased padding */
            vertical-align: top;
            font-size: 0.85em;
        }

        .table-denomination th {
            background-color: #60d88e;
            /* Light header color */
            text-align: center;
            font-weight: 600;
        }

        .denomination-cell {
            display: flex;
            flex-direction: column;
            gap: 3px;
            padding: 2px 0;
        }

        .denomination-cell .input-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* border-bottom: 1px solid gray; */

        }

        .denomination-cell input {
            border: none;
            outline: none;
            border-bottom: 1px dashed #aaa;
            /* Dashed line for input */
            width: 40px;
            text-align: right;
            font-size: 0.8em;
            padding: 1px 2px;
            background: transparent;
        }

        .denomination-cell .denom-label {
            font-size: 0.8em;
            color: #555;
        }

        .total-amount-display {
            font-size: 1.1em;
            font-weight: bold;
            padding: 5px 10px;
            text-align: right;
            border-top: 2px solid #333;
            /* Stronger separation line */
            margin-top: 5px;
        }

        .amount-in-words {
            margin-top: 10px;
            padding-top: 5px;
            font-size: 0.9em;
            font-weight: 600;
        }

        .amount-in-words .input-line {
            display: inline-block;
            width: 70%;
        }

        /* Update the main signature area styles from the previous prompt (No changes here) */
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 100px;
            font-size: 0.8em;
            width: 100%;
        }

        .signature-area div {
            width: 30%;
            border-top: 1px solid #333;
            padding-top: 3px;
            text-align: left;
            margin: 0 5px;
        }

        /* Style for the new Depositor Details block (the 3rd div) */
        .depositor-details-block {
            text-align: left !important;
            padding-left: 5px;
        }


        /* ==========================================================
                                                                                                                                                                                                                                                                                                                       PRINT STYLES (Keep it simple and dark for printing)
                                                                                                                                                                                                                                                                                                                       ========================================================== */
        @page {
            size: legal landscape;
            margin: 0.4in;
        }

        @media print {

            /* Hide non-print elements */
            .layout-navbar,
            .layout-menu,
            .card-header,
            .content-footer,
            .h5-reset-margin,
            .breadcrumb {
                display: none !important;
            }


            .content-wrapper,
            .layout-page,
            .container-xxl,
            .row.challan-view-container {
                padding: 0 !important;
                margin: 0 !important;
            }

            .card-body {
                padding: 0 !important;
            }

            body {
                font-size: 0.7em !important;
                /* Make print font size smaller */
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background-color: white !important;
                color: black !important;
            }

            .deposit-slips-container {
                width: 100%;
                max-width: none;
                justify-content: space-between;
                gap: 0;
                padding: 0;
            }

            .deposit-slip {
                width: 49% !important;
                /* Ensure minimal width */
                border: 1px solid black !important;
                box-shadow: none;
                padding: 2px !important;
                /* Drastically reduce slip padding */
                border-radius: 0;
            }

            /* NEW: Fixes for overflow */
            .table-denomination th,
            .table-denomination td {
                padding: 2px 1px !important;
                /* Reduce table cell padding */
                font-size: 0.75em !important;
                /* Ensure table text is small */
            }

            .account-metadata,
            .checkbox-group {
                /* Ensure account metadata doesn't cause overflow */
                font-size: 0.75em !important;
            }

            /* Re-introduce the Dotted Separator for PRINTING ONLY */
            .deposit-slip:first-of-type::after {
                content: '';
                position: absolute;
                right: -5px;
                top: 0;
                bottom: 0;
                width: 1px;
                border-right: 1px dotted black;
            }

            /* Ensure lines print dark */
            .field-group input,
            .input-line,
            .details-box,
            .pk-nbp-a-grid,
            .table-denomination th,
            .table-denomination td {
                border-color: black !important;
            }

            .denomination-cell input {
                width: 30px !important;
                /* Ensure denomination inputs are small */
            }

            .signature-area div {
                border-top: 1px solid black !important;
            }

            .deposit-slip-title {
                color: black !important;
                /* Ensure title prints black */
            }

            .total-amount-display {
                border-top: 2px solid black !important;
            }

            /* Print Media Fix: Ensure lines are solid black when printing */
            @media print {
                .write-line {
                    border-bottom: 1px solid black !important;
                }
            }
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row challan-view-container">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <h5 class="h5-reset-margin">Deposit Slip</h5>
                <div>
                    <a href="javascript:void(0)" onclick="window.print()" class="btn btn-warning rounded-pill btn-sm" type="button"><i
                            class="bx bx-printer"></i> Print Slips</a>
                    <a href="{{ route('billings.treasury-challans.downloadDepositSlipPdf', $treasuryChallan->id) }}"
                        class="btn btn-info rounded-pill btn-sm" type="button"><i class="bx bx-download"></i>Download PDF</a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="deposit-slips-container">
                    @for ($i = 0; $i < 2; $i++)
                        {{-- Loop for two copies --}}
                        <div class="deposit-slip">
                            <div class="header-row">
                                <div class="bank-branding">
                                    {{-- Using a cleaner placeholder for the NBP logo --}}
                                    <img src="{{ asset('assets/img/nbp-logo/NBP.png') }}" alt="NBP Logo" class="nba-logo">
                                    {{-- <span>NBP<br><small>National Bank of Pakistan</small></span> --}}
                                </div>
                                <div class="deposit-slip-title">
                                    DEPOSIT SLIP
                                </div>
                                <div class="copy-info">
                                    @if ($i == 0)
                                        <span>BANK COPY (White)</span><br>
                                    @else
                                        <span>CUSTOMER COPY (Green)</span><br>
                                    @endif
                                    <span style="font-size: 0.9em;">(Not official unless validated)</span>
                                </div>
                            </div>

                            {{-- Sr. No. and Date Row --}}
                            <div class="field-group">
                                <strong>Sr.No:</strong> <input type="text" class="sr-no-input"
                                    value="{{ $treasuryChallan->memo_number }}" readonly>
                                <strong style="margin-left: auto;">Date:</strong>
                                <input type="text" class="date-input" placeholder="DD-MM-YYYY"
                                    value="{{ now()->format('d-m-Y') }}" readonly>
                            </div>

                            <div class="field-group">
                                <strong>PAY-IN-SLIP TYPE:</strong> <span class="input-line">CASH / TRANSFER</span>
                            </div>

                            {{-- Account Details Box --}}
                            <div class="details-box">
                                <div class="account-fields">
                                    <div class="field-group" style="margin-bottom: 5px; width:200%">
                                        <strong>Branch:</strong> <input type="text" class="branch-title-input"
                                            value="{{ $treasuryChallan->bank_name }}" readonly>
                                    </div>
                                    <div class="field-group" style="margin-bottom: 5px;">
                                        <strong>Title of Account:</strong> <input type="text" class="branch-title-input"
                                            value="Govt Receipt" readonly>
                                    </div>
                                    <div class="field-group" style="margin-bottom: 5px;">
                                        <strong>A/C No:</strong> <input type="text" class="branch-title-input"
                                            value="{{ $treasuryChallan->bank_account_number }}"
                                            style="font-weight: bold; text-align: center;"readonly>
                                    </div>
                                </div>
                                <div class="account-metadata">
                                    <div class="chechbox-group"></div>
                                    <div class="checkbox-group mt-4">
                                        <strong>A/C Type:</strong>
                                        <div class="checkbox-item">Current<span></span></div>
                                        <div class="checkbox-item">Saving<span></span></div>
                                        <div class="checkbox-item">Others<span></span></div>
                                    </div>

                                    <div class="checkbox-group">
                                        <strong>Currency:</strong>
                                        <div class="checkbox-item">PKR<span>&#x2713;</span></div> {{-- Checkmark example --}}
                                        <div class="checkbox-item">USD<span></span></div>
                                        <div class="checkbox-item">EURO<span></span></div>
                                        <div class="checkbox-item">GBP<span></span></div>
                                    </div>
                                </div>
                            </div>

                            {{-- PK NBP A and Type Section --}}
                            <div class="pk-nbp-a-grid">
                                <div>P K</div>
                                <div>N</div>
                                <div>B</div>
                                <div>P</div>
                                <div>A</div>
                            </div>
                            <div class="transfer-cash-section">
                                <span>Transfer</span>
                                <span>Cash</span>
                            </div>

                            {{-- Denomination Table --}}
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
                                        <td style="width: 25%; text-align: center; vertical-align: middle;">
                                            <div style="font-weight: bold; font-size: 1.1em; padding: 10px 0;">G-11217</div>
                                        </td>
                                        <td style="width: 25%; text-align: center; vertical-align: middle;">
                                            <div style="font-weight: bold;">{{ $treasuryChallan->cheque_number }}</div>
                                        </td>
                                        <td style="width: 25%;">
                                            <div class="denomination-cell">
                                                <div class="input-row"><span class="denom-label">5000 X</span> <input
                                                        type="text" readonly></div>
                                                <div class="input-row"><span class="denom-label">1000 X</span> <input
                                                        type="text" readonly></div>
                                                <div class="input-row"><span class="denom-label">500 X</span> <input
                                                        type="text" readonly></div>
                                                <div class="input-row"><span class="denom-label">100 X</span> <input
                                                        type="text" readonly></div>
                                                <div class="input-row"><span class="denom-label">50 X</span> <input
                                                        type="text" readonly></div>
                                                <div class="input-row"><span class="denom-label">20 X</span> <input
                                                        type="text" readonly></div>
                                                <div class="input-row"><span class="denom-label">10 X</span> <input
                                                        type="text" readonly></div>
                                                <div class="input-row"><span class="denom-label">5 X</span> <input
                                                        type="text" readonly></div>
                                                <div class="input-row"><span class="denom-label">Coins</span></div>
                                                <div class="input-row" style="margin-top: 5px;"><span class="denom-label"
                                                        style="font-weight: bold;">Total Cash</span></div>
                                            </div>
                                        </td>
                                        <td style="width: 25%; text-align: center; vertical-align: bottom;">
                                            <div style="padding-bottom: 5px; font-weight: bold;">
                                                {{ $finalAmount }}</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- Total Amount Display --}}
                            <div>
                                <div class="total-amount-display">
                                    <span>TOTAL RS:</span>
                                    <span>{{ $finalAmount }}/-</span>
                                </div>

                                {{-- Amount in Words --}}
                                <div class="amount-in-words">
                                    Total Amount in Words: <span class="input-line">{{ $rupeesWords }}</span>
                                </div>
                                <div style="font-size:10px; text-align:right; margin-right:15px;">
                                    <span>I have read, understood and accepted the terms and conditions printed
                                        overleaf.</span>
                                </div>
                            </div>

                            {{-- Signature Area --}}
                            <div class="signature-area">

                                {{-- 1. Cashier / Received By --}}
                                <div>
                                    <span class="text-bold text-uppercase">Received by:</span><br>
                                    Cashier's Signature with Stamp
                                </div>

                                {{-- 2. Authorised Officer --}}
                                <div>
                                    Authorised Officer Signature
                                </div>

                                {{-- 3. Depositor's Details --}}
                                <div style="text-align: center !imprtant" class="">
                                    <span class=" mb-2 text-center">Depositor's Signature</span>
                                    <br>

                                    <span>Name: <span style="text-decoration: underline"> Asad
                                            Khan_____</span></span><br>
                                    <span>CNIC: <span style="text-decoration: underline">_________________
                                        </span></span><br>
                                    <span>Cell No: <span style="text-decoration: underline">0346-9150145
                                        </span></span><br>


                                </div>


                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush

@push('scripts')
@endpush

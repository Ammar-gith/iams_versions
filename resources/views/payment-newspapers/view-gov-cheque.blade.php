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

        .gov-cheque {
            width: 900px;
            margin: 20px auto;
            border: 2px solid #d97706;
            padding: 20px 25px;
            background: #fff7ed;
            font-family: 'Times New Roman', serif;
            position: relative;
        }

        /* HEADER */
        .cheque-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #d97706;
            padding-bottom: 10px;
        }

        .logo-left img,
        .logo-right img {
            width: 70px;
            height: auto;
        }

        .header-center {
            text-align: center;
            flex: 1;
        }

        .header-center h4 {
            margin: 0;
            color: #b45309;
            font-weight: bold;
        }

        .header-center h5 {
            margin: 0;
            font-size: 14px;
        }

        .header-center .sub {
            font-size: 12px;
            color: #555;
        }

        /* DATE */
        .cheque-date {
            text-align: right;
            margin-top: 10px;
            font-weight: bold;
            font-size: 13px;
        }

        /* PAY LINE */
        .pay-line {
            margin-top: 20px;
            display: flex;
            align-items: center;
        }

        .pay-line .label {
            width: 80px;
            font-weight: bold;
        }

        .pay-line .value {
            flex: 1;
            border-bottom: 1px solid #000;
            padding-left: 10px;
            font-weight: bold;
        }

        /* AMOUNT WORDS */
        .amount-words {
            margin-top: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            font-style: bold;
        }

        /* MIDDLE */
        .middle-row {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .city {
            font-weight: bold;
        }

        .note {
            border: 1px solid #d97706;
            padding: 5px 12px;
            font-size: 12px;
            background: #fed7aa;
        }

        /* AMOUNT */
        .amount-box {
            margin-top: 25px;
            text-align: right;
            font-size: 22px;
            font-weight: bold;
            color: #b45309;
        }

        /* FOOTER */
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }

        .signature {
            font-size: 13px;
        }

        .stamp {
            border: 1px dashed #d97706;
            padding: 15px;
            font-size: 12px;
            color: #b45309;
        }

        .watermark {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.05);
            pointer-events: none;
        }

        /* css for back cheque */
        .cheque-pair {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }

        /* BACK SIDE */
        .back-cheque {
            margin-top: 10px;
            background: #ffffff;
            border: 2px solid #d97706;
            padding: 0;
            /* ✅ REMOVE SPACE */
        }

        .back-table {
            width: 100%;
            border-collapse: collapse;
            background: #f3f2f2;
            margin: 0;
            /* ✅ remove any default spacing */
            font-size: 14px;
        }

        .back-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }

        /* ❌ REMOVE OUTER BORDER ONLY */
        .back-table tr:first-child td {
            border-top: none;
        }

        .back-table tr:last-child td {
            border-bottom: none;
        }

        .back-table td:first-child {
            border-left: none;
        }

        .back-table td:last-child {
            border-right: none;
        }

        .back-table .title {
            font-weight: bold;
            background: #f3f2f2;
        }

        .back-table .provincial {
            color: red;
            font-weight: bold;
        }

        .back-table .label {
            width: 120px;
            font-weight: bold;
        }

        .back-table .amount {
            font-weight: bold;
        }

        .back-table .words {
            font-weight: 500;
        }

        /* Print only cheque (hide full layout) */
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            body {
                background: #fff !important;
            }

            /* hide everything by default */
            body * {
                visibility: hidden !important;
            }

            /* show only cheque area */
            .js-print-cheque-area,
            .js-print-cheque-area * {
                visibility: visible !important;
            }

            .js-print-cheque-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* avoid flex weirdness while printing */
            .deposit-slips-container {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: none !important;
                gap: 0 !important;
            }

            .cheque-pair {
                margin: 0 !important;
                page-break-inside: avoid;
            }

            .gov-cheque {
                margin: 0 auto 10px auto !important;
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row challan-view-container">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>

                    <h5 class="h5-reset-margin">Government Cheque</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="window.print()" class="btn btn-warning rounded-pill btn-sm">
                        <i class="bx bx-printer"></i>
                        Print Cheque
                    </button>
                    <a href="{{ route('payment.newspapers.viewGovCheque.pdf', request()->query()) }}"
                        class="btn btn-info rounded-pill btn-sm" type="button">
                        <i class="bx bx-download"></i>
                        Download PDF
                    </a>
                </div>
                    </div>
                    @php
                        $bankDetail = $taxPayee ?? null;
                        if (!$bankDetail) {
                            $bankDetail =
                                $payments->first()->mediaBankDetail ??
                                ($agencyPayments->first()->mediaBankDetail ?? null);
                        }

                    @endphp

                    <div class="card-body p-0 js-print-cheque-area">
                        <div class="deposit-slips-container">
                            {{-- @foreach ($payments as $payment) --}}
                            <div class="cheque-pair">
                                {{-- ================= FRONT CHEQUE ================= --}}
                                <div class="gov-cheque">
                                    <div class="watermark">DGIPR</div>

                                    {{-- HEADER --}}
                                    <div class="cheque-header">
                                        <div class="logo-left">
                                            <img src="{{ asset('assets/img/kpk-logo/kpk-logo.png') }}">
                                        </div>

                                        <div class="header-center">
                                            <h4>Government of Khyber Pakhtunkhwa</h4>
                                            <h5>Directorate General Information & Public Relations</h5>
                                            <span class="sub">DGIPR - Payment Cheque</span>
                                        </div>

                                        <div class="logo-right">
                                            <img src="{{ asset('assets/img/department-logo/kp-logo.png') }}">
                                        </div>
                                    </div>

                                    {{-- DATE --}}
                                    <div class="cheque-date">
                                        Date: {{ now()->format('d-M-Y') }}
                                    </div>

                                    {{-- PAY LINE --}}
                                    <div class="pay-line">
                                        <span class="label">Pay to:</span>
                                        <span class="value">
                                            Manager {{ $bankDetail->bank_name ?? '---' }}
                                        </span>
                                    </div>

                                    {{-- AMOUNT WORDS --}}
                                    <div class="amount-words">
                                        Rupees {{ numberToWords($totalAmount) }}
                                    </div>

                                    {{-- CITY + NOTE --}}
                                    <div class="middle-row">
                                        <div class="city">Peshawar</div>
                                        <div class="note">Payee’s A/C Only</div>
                                    </div>

                                    {{-- AMOUNT --}}
                                    <div class="amount-box">
                                        Rs. {{ number_format($totalAmount) }}/-
                                    </div>

                                    {{-- FOOTER --}}
                                    <div class="footer">
                                        <div class="signature">
                                            ___________________________<br>
                                            Authorized Signatory
                                        </div>

                                        <div class="stamp">
                                            Official Stamp
                                        </div>
                                    </div>
                                </div>
                                {{-- @endforeach --}}
                                {{-- ================= BACK CHEQUE ================= --}}
                                <div class="gov-cheque back-cheque">
                                    <div class="watermark">DGIPR</div>

                                    <table class="back-table">
                                        <tr>
                                            <td colspan="4" class="title">Payees A/C Only</td>
                                        </tr>

                                        <tr>
                                            <td colspan="4" class="provincial">Provincial</td>
                                        </tr>

                                        <tr>
                                            <td class="label">PO NO</td>
                                            <td></td>
                                            <td class="label">Date</td>
                                            <td>{{ now()->format('d-M-Y') }}</td>
                                        </tr>

                                        <tr>
                                            <td class="label">To</td>
                                            <td colspan="3">Chief Manager SBP BSC Peshawar</td>
                                        </tr>

                                        <tr>
                                            <td class="label">PAY Rs</td>
                                            <td colspan="3" class="amount">
                                                Rs. {{ number_format($totalAmount) }}/-

                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="label">Rupees.</td>
                                            <td colspan="3" class="words">
                                                {{ numberToWords($totalAmount) }} Rupees only
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="label">ATO</td>
                                            <td colspan="3">DCA/Treasury Officer Peshawar</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            {{-- ================= BACK CHEQUE ================= --}}
                        </div>
                    </div>
                </div>
@endpush

    @push('scripts')
@endpush

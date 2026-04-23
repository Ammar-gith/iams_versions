<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Government Cheque</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 13px;
            color: #111;
        }

        .gov-cheque {
            width: 900px;
            margin: 10px auto;
            border: 2px solid #d97706;
            padding: 18px 22px;
            background: #fff7ed;
            position: relative;
        }

        /* Dompdf: prefer table layout over flex */
        .cheque-header {
            width: 100%;
            border-bottom: 2px solid #d97706;
            padding-bottom: 10px;
        }

        .cheque-header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cheque-header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .cheque-header-table .cell-logo {
            width: 80px;
        }

        .cheque-header-table .cell-center {
            text-align: center;
        }

        .cheque-header-table img {
            width: 65px;
            height: auto;
            display: inline-block;
        }

        .header-center {
            text-align: center;
        }

        .header-center h4 {
            margin: 0;
            color: #b45309;
            font-weight: bold;
            font-size: 16px;
        }

        .header-center h5 {
            margin: 0;
            font-size: 13px;
        }

        .header-center .sub {
            font-size: 11px;
            color: #555;
        }

        .cheque-date {
            text-align: right;
            margin-top: 10px;
            font-weight: bold;
            font-size: 12px;
        }

        .pay-line {
            margin-top: 18px;
            display: flex;
            align-items: center;
        }

        .pay-line .label {
            width: 70px;
            font-weight: bold;
        }

        .pay-line .value {
            flex: 1;
            border-bottom: 1px solid #000;
            padding-left: 10px;
            font-weight: bold;
        }

        .amount-words {
            margin-top: 12px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        /* Dompdf-safe left/right row */
        .middle-row {
            width: 100%;
            margin-top: 16px;
        }

        .middle-row-table {
            width: 100%;
            border-collapse: collapse;
        }

        .middle-row-table td {
            padding: 0;
            vertical-align: middle;
        }

        .note {
            border: 1px solid #d97706;
            padding: 5px 10px;
            font-size: 11px;
            background: #fed7aa;
        }

        .amount-box {
            margin-top: 18px;
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #b45309;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 45px;
        }

        .signature {
            font-size: 12px;
        }

        .stamp {
            border: 1px dashed #d97706;
            padding: 12px;
            font-size: 11px;
            color: #b45309;
            text-align: right;
        }

        .watermark {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 70px;
            color: rgba(0, 0, 0, 0.05);
        }

        .cheque-pair {
            page-break-inside: avoid;
            margin-bottom: 18px;
        }

        .back-cheque {
            margin-top: 10px;
            background: #ffffff;
            border: 2px solid #d97706;
            padding: 0;
        }

        .back-table {
            width: 100%;
            border-collapse: collapse;
            background: #f3f2f2;
            margin: 0;
            font-size: 12px;
        }

        .back-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }

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
    </style>
</head>

<body>
    @php
        $bankDetail = $taxPayee ?? null;
        if (!$bankDetail) {
            $bankDetail = $payments->first()->mediaBankDetail ?? ($agencyPayments->first()->mediaBankDetail ?? null);
        }
        $kpkLogo = public_path('assets/img/kpk-logo/kpk-logo.png');
        $deptLogo = public_path('assets/img/department-logo/kp-logo.png');
    @endphp

    <div class="cheque-pair">
        <div class="gov-cheque">
            <div class="watermark">DGIPR</div>

            <div class="cheque-header">
                <table class="cheque-header-table">
                    <tr>
                        <td class="cell-logo" style="text-align:left;">
                            @if (is_file($kpkLogo))
                                <img src="{{ $kpkLogo }}">
                            @endif
                        </td>
                        <td class="cell-center">
                            <div class="header-center">
                                <h4>Government of Khyber Pakhtunkhwa</h4>
                                <h5>Directorate General Information & Public Relations</h5>
                                <span class="sub">DGIPR - Payment Cheque</span>
                            </div>
                            
                        </td>
                        <td class="cell-logo" style="text-align:right;">
                            @if (is_file($deptLogo))
                                {{-- keep inside page; align left inside its own box if needed --}}
                                <img src="{{ $deptLogo }}" style="text-align:left;">
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class="cheque-date">
                Date: {{ now()->format('d-M-Y') }}
            </div>

            <div class="pay-line">
                <span class="label">Pay to:</span>
                <span class="value">
                    Manager {{ $bankDetail->bank_name ?? '---' }}
                </span>
            </div>

            <div class="amount-words">
                Rupees {{ numberToWords($totalAmount) }}
            </div>

            <div class="middle-row">
                <table class="middle-row-table">
                    <tr>
                        <td style="text-align:left;">
                            <strong>Peshawar</strong>
                        </td>
                        <td style="text-align:right;">
                            <span class="note">Payee’s A/C Only</span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="amount-box">
                Rs. {{ number_format($totalAmount) }}/-
            </div>

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
                    <td colspan="3" style="font-weight:bold;">
                        Rs. {{ number_format($totalAmount) }}/-
                    </td>
                </tr>
                <tr>
                    <td class="label">Rupees.</td>
                    <td colspan="3">
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
</body>

</html>

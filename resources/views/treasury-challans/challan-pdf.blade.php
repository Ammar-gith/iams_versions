<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Challan Form</title>
    <style>
        @page {
            size: legal landscape;
            margin: 5mm 8mm;
            /* Slightly reduced to prevent overflow */
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Times New Roman", serif;
            font-size: 12px;
            color: #000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .challan-wrapper {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .challan-cell {
            width: 32%;
            border: 1px solid #000;
            vertical-align: top;
            padding: 5px;
        }

        .separator-cell {
            width: 2%;
            text-align: center;
            vertical-align: middle;
        }

        .dotted-line {
            border-left: 1px dashed #000;
            height: 100%;
            display: inline-block;
        }

        .challan-header {
            background-color: #77e28e;
            font-weight: bold;
            text-align: center;
            padding: 4px 0;
            border-bottom: 1px solid #000;
            font-size: 13px;
        }

        .challan-subheader {
            text-align: center;
            font-size: 12px;
            border-bottom: 1px solid #000;
            padding: 2px 0;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .details-table td {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: top;
        }

        .green-box {
            background-color: #60d88e;
            text-align: center;
            font-weight: bold;
        }

        .amount-words {
            margin-top: 6px;
            font-weight: bold;
        }

        .total-row {
            text-align: right;
            font-weight: bold;
            border-top: 1px solid #000;
            margin-top: 4px;
        }

        .signature-table {
            width: 100%;
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
        }

        .signature-table td {
            border-top: 1px solid #000;
            padding-top: 4px;
        }

        /* Account Details Section */
        .account-details {
            border: 1px solid #000;
            padding: 4px;
            margin-top: 5px;
            font-size: 11px;
        }

        .account-row {
            width: 100%;
            border-collapse: collapse;
        }

        .account-row td {
            padding: 2px;
        }

        .checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            text-align: center;
            line-height: 9px;
            font-size: 9px;
        }

        .underline {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 180px;
            text-align: center;
        }
    </style>
    {{-- <style>
        @page {
            size: legal landscape;
            margin: 10mm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
            color: #000;
        }

        /* Container for all challans */
        .challan-container {
            text-align: center;
            white-space: nowrap;
            width: 100%;
        }

        .challan {
            display: inline-block;
            width: 32.5%;
            vertical-align: top;
            border: 1px solid #000;
            box-sizing: border-box;
            margin: 0 0.4%;
            page-break-inside: avoid;
        }

        .challan-header {
            background-color: #77e28e;
            border-bottom: 1px solid #000;
            text-align: center;
            font-weight: bold;
            padding: 4px;
        }

        .challan-subheader {
            font-size: 12px;
            border-bottom: 1px solid #000;
            text-align: center;
            padding: 2px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 11px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
            padding: 3px;
        }

        .challan-no {
            background-color: #60d88e;
            font-weight: bold;
        }

        .green-box {
            background-color: #60d88e;
            font-weight: bold;
            text-align: center;
        }

        .amount-row {
            text-align: right;
            font-weight: bold;
            border-top: 1px solid #000;
            padding: 5px;
        }

        .amount-words {
            text-align: left;
            padding: 5px;
            font-weight: 600;
        }

        .signature-area {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }

        .signature-area div {
            border-top: 1px solid #000;
            width: 45%;
            text-align: center;
            padding-top: 2px;
        }
    </style> --}}

</head>

<body>
    <table class="challan-wrapper">
        <tr>
            @for ($i = 0; $i < 3; $i++)
                {{-- Single Challan --}}
                <td class="challan-cell">
                    <div class="challan-header">CHALLAN OF CHEQUE PAID INTO THE TREASURY/SUB-TREASURY</div>
                    <div class="challan-subheader">SBP / NBP, PESHAWAR</div>

                    <div class="account-details">
                        <table class="account-row">
                            <tr>
                                <td><strong>Branch:</strong></td>
                                <td colspan="3" class="underline">{{ $treasuryChallan->bank_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Title of Account:</strong></td>
                                <td class="underline">Govt Receipt</td>
                                <td><strong>A/C Type:</strong></td>
                                <td>
                                    <span class="checkbox">✓</span> Current
                                    <span class="checkbox"></span> Saving
                                    <span class="checkbox"></span> Others
                                </td>
                            </tr>
                            <tr>
                                <td><strong>A/C No:</strong></td>
                                <td class="underline">{{ $treasuryChallan->bank_account_number }}</td>
                                <td><strong>Currency:</strong></td>
                                <td>
                                    <span class="checkbox">✓</span> PKR
                                    <span class="checkbox"></span> USD
                                    <span class="checkbox"></span> EURO
                                    <span class="checkbox"></span> GBP
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table class="details-table">
                        <tr>
                            <td colspan="2" class="green-box">Challan No:</td>
                            <td colspan="3" class="green-box">{{ $treasuryChallan->memo_number }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align:center;">Be filled by remitter. To be filled by the
                                Departmental Officer of the Treasury</td>
                        </tr>
                        <tr>
                            <td>By Whom Tendered</td>
                            <td>Names & Designation</td>
                            <td>Full Particulars</td>
                            <td>Amount (Rs)</td>
                            <td>Order to the Bank Provincail</td>
                        </tr>
                        <tr>
                            <td rowspan="4">Cheque No: {{ $treasuryChallan->cheque_number }}</td>
                            <td rowspan="4">Director General Information<br>Govt of KP<br>(PR 4075)</td>
                            <td rowspan="4">{{ $treasuryChallan->office->ddo_name }}</td>
                            <td rowspan="4">{{ $finalAmount }}/-</td>
                            <td rowspan="4" class="green-box">G-11217</td>
                        </tr>
                        <tr></tr>
                        <tr></tr>
                        <tr></tr>
                        <tr>
                            <td>Cheque Date:</td>
                            <td>{{ $treasuryChallan->cheque_date->format('d-m-Y') }}</td>
                            <td colspan="3">
                                @if (!empty($infNumbers))
                                    @foreach ($infNumbers as $number)
                                        <span>{{ $number }}</span><br>
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    </table>

                    <div class="amount-words">Amount in Words: {{ $rupeesWords }}</div>
                    <div class="total-row">Total Amount: {{ $finalAmount }}/-</div>

                    <table class="signature-table">
                        <tr>
                            <td>Date / Accountant</td>
                            <td>Head of Account Verification by Treasury Officer</td>
                        </tr>
                    </table>
                </td>

                {{-- Add separator except after last challan --}}
                @if ($i < 2)
                    <td class="separator-cell">
                        <div class="dotted-line">&nbsp;</div>
                    </td>
                @endif
            @endfor
        </tr>
    </table>
</body>

</html>

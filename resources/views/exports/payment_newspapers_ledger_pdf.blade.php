<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Ledger</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
        }

        .text-end {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Directorate General Information & Public Relations</h2>
        <p>Government of Khyber Pakhtunkhwa</p>
        <h3><strong>Payment Ledgerization</strong></h3>
        <div style="margin-top:4px;">Generated on: {{ $generatedAt->format('d M Y H:i') }}</div>
    </div>


    <table>
        <thead>
            <tr>
                <th style="width:40px;">S. No.</th>
                <th>INF No.</th>
                <th>Office</th>
                <th>Cheque Number</th>
                <th class="text-end">Cheque Amount</th>
                <th>Treasury Verify Date</th>
                <th>Challan No.</th>
                <th>Bank Verify Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($challans as $i => $c)
                @php
                    $isParked = collect($c->payments ?? [])->first() !== null;
                    $uiStatus = $isParked ? 'Parked' : 'Unparked';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ collect($c->payments ?? [])->pluck('inf_number')->filter()->unique()->values()->implode(', ') }}
                    </td>
                    <td>{{ $c->office?->ddo_name ?? '' }}</td>
                    <td>{{ $c->cheque_number ?? '' }}</td>
                    <td class="text-end">{{ number_format((float) ($c->total_amount ?? 0), 0) }}</td>
                    <td>{{ $c->tr_challan_verification_date?->format('d M Y') ?? '' }}</td>
                    <td>{{ $c->challan_number ?? '' }}</td>
                    <td>{{ $c->sbp_verification_date?->format('d M Y') ?? '' }}</td>
                    <td>{{ $uiStatus }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

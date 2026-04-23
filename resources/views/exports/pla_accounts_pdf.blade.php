<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>PLA Accounts</title>
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
        <h3><strong>PLA Accounts</strong></h3>
        <div style="margin-top:4px;">Generated on: {{ $generatedAt->format('d M Y H:i') }}</div>
    </div>



    <table>
        <thead>
            <tr>
                <th style="width:40px;">S. No.</th>
                <th style="width:60px;">ID</th>
                <th>INF No.</th>
                <th>Cheque Number</th>
                <th>Cheque Date</th>
                <th>Office</th>
                <th>Challan Number</th>
                <th class="text-end">Amount Received</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($plaAcounts as $i => $pla)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $pla->id }}</td>
                    <td>
                        {{ $pla->plaAccountItems?->pluck('inf_number')->filter()->unique()->implode(', ') }}
                    </td>
                    <td>{{ $pla->cheque_no ?? '' }}</td>
                    <td>
                        {{ $pla->cheque_date ? \Carbon\Carbon::parse($pla->cheque_date)->toFormattedDateString() : '' }}
                    </td>
                    <td>{{ $pla->office?->ddo_name ?? '' }}</td>
                    <td>{{ $pla->challan_no ?? '' }}</td>
                    <td class="text-end">{{ number_format((float) ($pla->total_cheque_amount ?? 0), 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

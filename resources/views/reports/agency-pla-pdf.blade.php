<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Newspaper PLA Amount Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
        }

        .header p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f2f2f2;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <div class="header">
        <h2>Directorate General Information & Public Relations</h2>
        <p>Government of Khyber Pakhtunkhwa</p>
        <p><strong>Agency Wise PLA Amount Report</strong></p>
        <p>Generated on: {{ now()->format('d M Y H:i') }}</p>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th width="10%">S#</th>
                <th width="40%">Agnecy Name</th>
                </th>
                <th width="30%">Newspaper Name</th>
                <th width="15%">15% Agency Comission</th>
                <th width="15%">Date</th>
            </tr>
        </thead>

        <tbody>

            @php
                $total = 0;
            @endphp

            @foreach ($data as $item)
                @php
                    $total += $item->agency_commission_amount;
                @endphp

                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->agency->name ?? '-' }}</td>
                    <td>{{ $item->newspaper->title ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->agency_commission_amount) }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>
                </tr>
            @endforeach

        </tbody>

        {{-- Total Row --}}
        <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th class="text-right">{{ number_format($total) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>This is a system generated report.</p>
    </div>

</body>

</html>

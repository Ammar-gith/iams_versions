<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Category Wise Report</title>

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
    <div class="header">
        <h2>Directorate General Information & Public Relations</h2>
        <p>Government of Khyber Pakhtunkhwa</p>
        <p><strong>{{ $title ?? 'Category Wise Report' }}</strong></p>
        <p>Generated on: {{ now()->format('d M Y H:i') }}</p>
    </div>

    @if (!empty($from) && !empty($to))
        <p style="text-align: center; margin-bottom: 15px;">
            <strong>Date Range:</strong>
            {{ \Carbon\Carbon::parse($from)->format('d M Y') }} - {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        </p>
    @endif

    <table>
        <thead>
            <tr>
                <th width="10%">S#</th>
                <th width="70%">Category Name</th>
                <th width="20%" class="text-right">Total Advertisements</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach ($data as $item)
                @php $total += $item['count']; @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['label'] }}</td>
                    <td class="text-right">{{ number_format($item['count']) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total</th>
                <th class="text-right">{{ number_format($total) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This is a system generated report.</p>
    </div>
</body>

</html>

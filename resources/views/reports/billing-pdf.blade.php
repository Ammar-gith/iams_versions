<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Billing Reports</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            color: #333;
        }

        .filters {
            margin-bottom: 20px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
        }

        .bg-success {
            background-color: #28a745;
        }

        .bg-warning {
            background-color: #ffc107;
        }

        .bg-secondary {
            background-color: #6c757d;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>Directorate General Information & Public Relations</h2>
        <p>Government of Khyber Pakhtunkhwa</p>
        <h4><strong>Billing Reports</strong></h4>
        <p>Generated on: {{ now()->format('d M Y H:i:s') }}</p>
    </div>


    <table>
        <thead>
            <tr>
                <th>S. No.</th>
                <th>INF No.</th>
                <th>Invoice No.</th>
                <th>Invoice Date</th>
                <th>Publication Date</th>
                <th>Printed Total Bill</th>
                <th>Newspaper</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($billings as $key => $billing)
                <tr>
                    <td>{{ ++$key }}</td>
                    <td>{{ $billing->advertisement->inf_number ?? '' }}</td>
                    <td>{{ $billing->invoice_no ?? '' }}</td>
                    <td>{{ $billing->invoice_date ? $billing->invoice_date : '' }}</td>
                    <td>{{ $billing->publication_date ? $billing->publication_date : '' }}</td>
                    <td>{{ number_format($billing->printed_total_bill ?? 0, 2) }}</td>
                    <td>{{ implode(', ', $billing->newspaper_titles) }}</td>
                    <td>
                        @if ($billing->status == 'billed')
                            {{ $billing->status }}
                        @else
                            pending
                        @endif

                    </td>
                    <td>{{ $billing->created_at->format('d M Y') }}</td>
                </tr>

            @empty
                <tr>
                    <td colspan="9" class="text-center">
                        No billing records found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Records: {{ $billings->count() }}</p>
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>

</html>

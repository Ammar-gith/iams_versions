<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Paid Amount History</title>
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
        <h3><strong>Paid Amount History</strong></h3>
        <div style="margin-top:4px;">Generated on: {{ $generatedAt->format('d M Y H:i') }}</div>
    </div>




    <table>
        <thead>
            <tr>
                <th style="width:40px;">S. No.</th>
                <th>Batch No</th>
                <th>Payee Name</th>
                <th>Type</th>
                <th>Bank</th>
                <th>Cheque No</th>
                <th class="text-end">Amount Paid (Rs)</th>
                <th class="text-end">Total Amount (Rs)</th>
                <th>Status</th>
                <th>Cheque Date</th>
            </tr>
        </thead>
        <tbody>
            @php
                $typeLabels = [
                    'newspaper' => 'NP',
                    'newspaper_partner' => 'NP partner',
                    'agency' => 'Agency',
                    'kpra' => 'KPRA',
                    'fbr' => 'FBR',
                ];
            @endphp
            @foreach ($history as $i => $entry)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $entry->batch_no ?? '' }}</td>
                    <td>{{ $entry->payee_name ?? '' }}</td>
                    <td>{{ $typeLabels[$entry->payee_type] ?? ($entry->payee_type ?? '') }}</td>
                    <td>{{ $entry->mediaBankDetail?->bank_name ?? '—' }}</td>
                    <td>{{ $entry->cheque_no ?? '—' }}</td>
                    <td class="text-end">{{ number_format((float) ($entry->amount ?? 0), 0) }}</td>
                    <td class="text-end">{{ number_format((float) ($entry->amount ?? 0), 0) }}</td>
                    <td>{{ $entry->status ?? '' }}</td>
                    <td>{{ $entry->created_at ? $entry->created_at->format('d M Y') : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Status Wise Report</title>

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
        <p><strong>Status Wise Report</strong></p>
        <p>Generated on: {{ now()->format('d M Y H:i') }}</p>
    </div>

    @php
        $statusMap = [
            3 => 'New',
            4 => 'In progress',
            10 => 'Approved',
            8 => 'Published',
            7 => 'Rejected',
        ];
        $statusName = $statusMap[$statusId] ?? 'Unknown';
    @endphp

    <p style="text-align: center; margin-bottom: 15px;">
        <strong>Status:</strong> {{ $statusName }}<br>
        @if (request('from') && request('to'))
            <strong>Date Range:</strong>
            {{ \Carbon\Carbon::parse(request('from'))->format('d M Y') }} -
            {{ \Carbon\Carbon::parse(request('to'))->format('d M Y') }}
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th width="5%">S#</th>
                <th width="15%">INF No.</th>
                <th width="20%">Office/Department</th>
                <th width="10%">Status</th>
                <th width="10%">Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ads as $ad)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $ad->inf_number }}</td>
                    <td>
                        @if ($ad->office_id)
                            {{ $ad->office->ddo_name ?? '-' }}
                        @elseif($ad->department_id)
                            {{ $ad->department->name ?? '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $statusMap[$ad->status_id] ?? 'Unknown' }}</td>
                    <td>{{ $ad->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system generated report.</p>
    </div>
</body>

</html>

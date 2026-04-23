<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <h2 style="text-align: center;">{{ $title }}</h2>
    <p>Generated on: {{ now()->format('d M Y, h:i A') }}</p>

    <table border="1" width="100%" cellpadding="5">
        <thead>
            <tr>
                <th>Memo No</th>
                <th>INF No</th>
                <th>Department</th>
                <th>Office</th>
                <th>Cheque No</th>
                <th>Cheque Date</th>
                <th>Cheque Amount</th>
                <th>Bank Verify Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    <td>{{ $item->memo_number }}</td>
                    <td>{{ is_array($item->inf_number) ? implode(', ', $item->inf_number) : $item->inf_number }}</td>
                    <td>{{ $item->department->name ?? '' }}</td>
                    <td style="width:50%; white-space: normal; word-wrap: break-word;">
                        {{ \Illuminate\Support\Str::words($item->office->ddo_name ?? '', 10, '...') }}
                    </td>
                    <td>{{ $item->cheque_number }}</td>
                    <td>{{ $item->cheque_date }}</td>
                    <td>{{ $item->total_amount }}</td>
                    <td>{{ $item->sbp_verification_date }}</td>
                    <td>{{ $item->status->title ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

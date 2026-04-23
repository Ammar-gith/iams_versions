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
        <br>
        <h3><strong>{{ $title }}</strong></h3>
        <p>Generated on: {{ now()->format('d M Y H:i') }}</p>
    </div>


    <table>
        <thead>
            <tr>
                <th>S.No</th>
                @if (
                    $user->hasRole([
                        'Superintendent',
                        'Diary Dispatch',
                        'Super Admin',
                        'Deputy Director',
                        'Director General',
                        'Secretary',
                    ]))
                    <th>INF No.</th>
                @endif
                <th>Memo No.</th>
                <th>Memo Date</th>
                <th>Urdu Space</th>
                <th>Urdu Size</th>
                <th>English Space</th>
                <th>English Size</th>
                <th>Department / Office</th>
                <th>Submission Date</th>
                <th>Publication Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($advertisements as $ad)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @if (
                        $user->hasRole([
                            'Superintendent',
                            'Diary Dispatch',
                            'Super Admin',
                            'Deputy Director',
                            'Director General',
                            'Secretary',
                        ]))
                        <td>{{ $ad->inf_number }}</td>
                    @endif
                    <td>{{ $ad->memo_number ?? '-' }}</td>
                    <td>{{ !empty($ad->memo_date) ? \Carbon\Carbon::parse($ad->memo_date)->toFormattedDateString() : '-' }}</td>
                    <td>{{ $ad->urdu_space ?? '-' }}</td>
                    <td>{{ $ad->urdu_size ?? '-' }}</td>
                    <td>{{ $ad->english_space ?? '-' }}</td>
                    <td>{{ $ad->english_size ?? '-' }}</td>
                    <td>{{ $ad->office->ddo_name ?? ($ad->department->name ?? '-') }}</td>
                    <td>{{ optional($ad->created_at)->toFormattedDateString() }}</td>
                    <td>{{ optional($ad->publish_on_or_before)->toFormattedDateString() }}</td>
                    <td>{{ $ad->status->title ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        <p>This is a system generated report.</p>
    </div>
</body>

</html>

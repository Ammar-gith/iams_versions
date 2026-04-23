<!DOCTYPE html>
<html>

<head>
    <title>{{ $title }}</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #eee;
        }
    </style>
</head>

<body>
    <h3>{{ $title }}</h3>
    <table>
        <thead>
            <tr>
                <th>Label</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['count'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

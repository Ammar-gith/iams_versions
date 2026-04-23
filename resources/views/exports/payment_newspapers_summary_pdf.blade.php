<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Summary</title>
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
        <h3><strong>Newspaper Wise Total Amount</strong></h3>
        <div style="margin-top:4px;">Generated on: {{ $generatedAt->format('d M Y H:i') }}</div>
    </div>



    <table>
        <thead>
            <tr>
                <th style="width:40px;">S. No.</th>
                <th>Type</th>
                <th>Name</th>
                <th>Account No.</th>
                <th>Bank</th>
                <th class="text-end">KPRA (INF)</th>
                <th class="text-end">KPRA (Dept)</th>
                <th class="text-end">IT (INF)</th>
                <th class="text-end">IT (Dept)</th>
                <th class="text-end">Payable</th>
            </tr>
        </thead>
        <tbody>
            @php $sr = 1; @endphp
            @foreach ($payments as $newspaperId => $rows)
                <tr>
                    <td>{{ $sr++ }}</td>
                    <td>Newspaper</td>
                    <td>{{ $rows->first()?->newspaper?->title ?? '' }}</td>
                    <td>{{ $rows->first()?->mediaBankDetail?->account_number ?? '' }}</td>
                    <td>{{ $rows->first()?->mediaBankDetail?->bank_name ?? '' }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('kpra_inf'), 0) }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('kpra_department'), 0) }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('it_inf'), 0) }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('it_department'), 0) }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('net_dues'), 0) }}</td>
                </tr>
            @endforeach

            @foreach ($agencies as $agencyId => $rows)
                <tr>
                    <td>{{ $sr++ }}</td>
                    <td>Agency</td>
                    <td>{{ $rows->first()?->agency?->name ?? '' }}</td>
                    <td>{{ $rows->first()?->mediaBankDetail?->account_number ?? '' }}</td>
                    <td>{{ $rows->first()?->mediaBankDetail?->bank_name ?? '' }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('kpra_inf'), 0) }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('kpra_department'), 0) }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('it_inf'), 0) }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('it_department'), 0) }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('net_dues'), 0) }}</td>
                </tr>
            @endforeach

            @php
                $kpraInf = (float) ($kpraTotalInf ?? 0);
                $kpraDept = (float) ($kpraTotalDept ?? 0);
                $fbrInf = (float) ($fbrTotalInf ?? 0);
                $fbrDept = (float) ($fbrTotalDept ?? 0);
            @endphp

            @if ($kpraInf + $kpraDept > 0)
                <tr style="background:#fff3cd;font-weight:bold;">
                    <td>{{ $sr++ }}</td>
                    <td>KPRA</td>
                    <td>{{ $kpraPayee->description ?? 'KPRA' }}</td>
                    <td>{{ $kpraPayee->account_number ?? '' }}</td>
                    <td>{{ $kpraPayee->bank_name ?? '' }}</td>
                    <td class="text-end">{{ number_format($kpraInf, 0) }}</td>
                    <td class="text-end">{{ number_format($kpraDept, 0) }}</td>
                    <td class="text-end">0</td>
                    <td class="text-end">0</td>
                    <td class="text-end">{{ number_format($kpraInf + $kpraDept, 0) }}</td>
                </tr>
            @endif

            @if ($fbrInf + $fbrDept > 0)
                <tr style="background:#fff3cd;font-weight:bold;">
                    <td>{{ $sr++ }}</td>
                    <td>FBR</td>
                    <td>{{ $fbrPayee->description ?? 'FBR' }}</td>
                    <td>{{ $fbrPayee->account_number ?? '' }}</td>
                    <td>{{ $fbrPayee->bank_name ?? '' }}</td>
                    <td class="text-end">0</td>
                    <td class="text-end">0</td>
                    <td class="text-end">{{ number_format($fbrInf, 0) }}</td>
                    <td class="text-end">{{ number_format($fbrDept, 0) }}</td>
                    <td class="text-end">{{ number_format($fbrInf + $fbrDept, 0) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Pay Order List</title>
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
        <h3><strong>Pay Order List</strong></h3>
        <div style="margin-top:4px;">Generated on: {{ $generatedAt->format('d M Y H:i') }}</div>
    </div>



    <table>
        <thead>
            <tr>
                <th style="width:40px;">S. No.</th>
                <th>Payee Name</th>
                <th class="text-end">Payable</th>
            </tr>
        </thead>
        <tbody>
            @php $sr = 1; @endphp
            @php $overallPayable = 0.0; @endphp
            @foreach ($mergedBanks as $bankName => $npPayable)
                @php $ag = (float) ($agencyTotalsByBankName[$bankName] ?? 0); @endphp
                @php $overallPayable += ((float) $npPayable + $ag); @endphp
                <tr>
                    <td>{{ $sr++ }}</td>
                    <td>Manager {{ $bankName }}</td>
                    <td class="text-end">{{ number_format((float) $npPayable + $ag, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if (!empty($kpraTotal) && $kpraTotal > 0)
                <tr style="background:#f8f9fa;font-weight:bold;">
                    <td>{{ $sr++ }}</td>
                    <td class="fw-bold">
                        {{ $kpraPayee->description ?? 'KPRA' }}
                        @if (!empty($kpraPayee?->bank_name) || !empty($kpraPayee?->account_number))
                            <div style="font-size:10px;color:#6c757d;">
                                {{ $kpraPayee->bank_name ?? '' }}
                                @if (!empty($kpraPayee?->account_number))
                                    — {{ $kpraPayee->account_number }}
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format((float) $kpraTotal, 0) }}</td>
                </tr>
            @endif

            @if (!empty($fbrTotal) && $fbrTotal > 0)
                <tr style="background:#f8f9fa;font-weight:bold;">
                    <td>{{ $sr++ }}</td>
                    <td class="fw-bold">
                        {{ $fbrPayee->description ?? 'FBR' }}
                        @if (!empty($fbrPayee?->bank_name) || !empty($fbrPayee?->account_number))
                            <div style="font-size:10px;color:#6c757d;">
                                {{ $fbrPayee->bank_name ?? '' }}
                                @if (!empty($fbrPayee?->account_number))
                                    — {{ $fbrPayee->account_number }}
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format((float) $fbrTotal, 0) }}</td>
                </tr>
            @endif

            <tr style="background:#fff3cd;font-weight:bold;">
                <td colspan="2" class="text-end">GRAND TOTAL (All Banks):</td>
                <td class="text-end">
                    {{ number_format((float) ($grandTotal ?? $overallPayable + (float) ($kpraTotal ?? 0) + (float) ($fbrTotal ?? 0)), 0) }}
                </td>
            </tr>
        </tfoot>
    </table>
</body>

</html>

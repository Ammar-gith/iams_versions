<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bank Wise Payment Summary</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10.5px;
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
            padding: 5px;
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
        <h3><strong>Bank Wise Payment Summary</strong></h3>
        <div style="margin-top:4px;">Generated on: {{ $generatedAt->format('d M Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px;">S. No.</th>
                <th>Media Name</th>
                <th>Partner</th>
                <th>Share %</th>
                <th>Account No.</th>
                <th>Account Title</th>
                <th>Bank</th>
                <th class="text-end">Payable</th>
            </tr>
        </thead>
        <tbody>
            @php $sr = 1; @endphp
            @foreach ($bankNames as $bankName)
                @php
                    $bankPayable = 0.0;
                @endphp
                @foreach (collect($partnerBankBuckets[$bankName] ?? []) as $r)
                    @php $bd = $r['bank_detail'] ?? null; @endphp
                    @php $bankPayable += (float) ($r['totals']['payable'] ?? 0); @endphp
                    <tr>
                        <td>{{ $sr++ }}</td>
                        <td>{{ $r['newspaper']?->title ?? ($bd?->media_name ?? '') }}</td>
                        <td>{{ $r['partner_name'] ?? '—' }}</td>
                        <td>{{ $r['share_percentage'] !== null ? $r['share_percentage'] : '—' }}</td>
                        <td>{{ $bd?->account_number ?? '' }}</td>
                        <td>{{ $bd?->account_title ?? '' }}</td>
                        <td>{{ $bankName }}</td>
                        <td class="text-end">{{ number_format((float) ($r['totals']['payable'] ?? 0), 0) }}</td>
                    </tr>
                @endforeach
                {{-- <td>{{}}</td> --}}

                @foreach (($agenciesByBank[$bankName] ?? collect())->groupBy('agency_id') as $agencyId => $rows)
                    @php $bankPayable += (float) $rows->sum('net_dues'); @endphp
                    <tr>
                        <td>{{ $sr++ }}</td>
                        <td>{{ $rows->first()?->agency?->name ?? '' }}</td>
                        <td>—</td>
                        <td>—</td>
                        <td>{{ $rows->first()?->mediaBankDetail?->account_number ?? '' }}</td>
                        <td>{{ $rows->first()?->mediaBankDetail?->account_title ?? '' }}</td>
                        <td>{{ $bankName }}</td>
                        <td class="text-end">{{ number_format((float) $rows->sum('net_dues'), 0) }}</td>
                    </tr>
                @endforeach

                <tr style="background:#fff3cd;font-weight:bold;">
                    <td colspan="7" class="text-end">Total for {{ $bankName }}:</td>
                    <td class="text-end">{{ number_format($bankPayable, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if (!empty($kpraTotal) && $kpraTotal > 0)
                <tr style="background:#f8f9fa;font-weight:bold;">
                    <td>{{ $sr++ }}</td>
                    <td class="fw-bold">{{ $kpraPayee->description ?? 'KPRA' }}</td>
                    <td>—</td>
                    <td>—</td>
                    <td>{{ $kpraPayee->account_number ?? '' }}</td>
                    <td>{{ $kpraPayee->account_title ?? '' }}</td>
                    <td>{{ $kpraPayee->bank_name ?? '' }}</td>
                    <td class="text-end">{{ number_format((float) $kpraTotal, 0) }}</td>
                </tr>
            @endif

            @if (!empty($fbrTotal) && $fbrTotal > 0)
                <tr style="background:#f8f9fa;font-weight:bold;">
                    <td>{{ $sr++ }}</td>
                    <td class="fw-bold">{{ $fbrPayee->description ?? 'FBR' }}</td>
                    <td>—</td>
                    <td>—</td>
                    <td>{{ $fbrPayee->account_number ?? '' }}</td>
                    <td>{{ $fbrPayee->account_title ?? '' }}</td>
                    <td>{{ $fbrPayee->bank_name ?? '' }}</td>
                    <td class="text-end">{{ number_format((float) $fbrTotal, 0) }}</td>
                </tr>
            @endif

            <tr style="background:#fff3cd;font-weight:bold;">
                <td colspan="7" class="text-end">GRAND TOTAL (All Banks):</td>
                <td class="text-end">{{ number_format((float) ($grandTotal ?? 0), 0) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>

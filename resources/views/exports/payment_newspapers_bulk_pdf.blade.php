<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Bulk</title>
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
        <h3><strong>Newspaper Bulk View</strong></h3>
        <div style="margin-top:4px;">Generated on: {{ $generatedAt->format('d M Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px;">S. No.</th>
                <th>Newspaper</th>
                <th>INF</th>
                <th>RT</th>
                <th>Invoice #</th>
                <th>Invoice Date</th>
                <th>Through</th>
                <th class="text-end">Grand</th>
                <th class="text-end">Pay(%)</th>
                <th class="text-end">Gross</th>
                <th class="text-end">KPRA INF</th>
                <th class="text-end">KPRA Dept</th>
                <th class="text-end">IT INF</th>
                <th class="text-end">IT Dept</th>
                <th class="text-end">Payable</th>
            </tr>
        </thead>
        <tbody>
            @php $sr = 1; @endphp
            @foreach ($rows->groupBy('newspaper_id') as $newspaperId => $group)
                @php
                    $totalAmount = (float) $group->sum('total_amount');
                    $totalPayable = (float) $group->sum('net_dues');
                    $totalGrossAmount = (float) $group->sum('gross_amount_100_or_85_percent');
                    $totalKpraInf = (float) $group->sum('kpra_inf');
                    $totalKpraDept = (float) $group->sum('kpra_dept');
                    $totalItInf = (float) $group->sum('it_inf');
                    $totalItDept = (float) $group->sum('it_dept');
                @endphp

                @foreach ($group as $r)
                    <tr>
                        <td>{{ $sr++ }}</td>
                        <td>{{ $r->newspaper?->title ?? '' }}</td>
                        <td>{{ $r->inf_number ?? '' }}</td>
                        <td>{{ $r->rt_number ?? '' }}</td>
                        <td>{{ $r->bill?->invoice_no ?? '' }}</td>
                        <td>{{ $r->bill?->invoice_date ? \Carbon\Carbon::parse($r->bill->invoice_date)->toFormattedDateString() : '' }}
                        </td>
                        <td>{{ $r->payment_type === 'direct' ? 'Newspaper' : 'Agency' }}</td>
                        <td class="text-end">{{ number_format((float) ($r->total_amount ?? 0), 0) }}</td>
                        <td class="text-end">{{ $r->payment_type === 'direct' ? '100' : '85' }}</td>
                        <td class="text-end">{{ number_format((float) ($r->gross_amount_100_or_85_percent ?? 0), 0) }}
                        </td>
                        <td class="text-end">{{ number_format((float) ($r->kpra_inf ?? 0), 0) }}</td>
                        <td class="text-end">{{ number_format((float) ($r->kpra_dept ?? 0), 0) }}</td>
                        <td class="text-end">{{ number_format((float) ($r->it_inf ?? 0), 0) }}</td>
                        <td class="text-end">{{ number_format((float) ($r->it_dept ?? 0), 0) }}</td>
                        <td class="text-end">{{ number_format((float) ($r->net_dues ?? 0), 0) }}</td>
                    </tr>
                @endforeach

                <tr style="background:#f8f9fa;font-weight:bold;">
                    <td></td>
                    <td></td>
                    <td colspan="5">Total Amounts :</td>
                    <td class="text-end">{{ number_format($totalAmount, 0) }}</td>
                    <td></td>
                    <td class="text-end">{{ number_format($totalGrossAmount, 0) }}</td>
                    <td class="text-end">{{ number_format($totalKpraInf, 0) }}</td>
                    <td class="text-end">{{ number_format($totalKpraDept, 0) }}</td>
                    <td class="text-end">{{ number_format($totalItInf, 0) }}</td>
                    <td class="text-end">{{ number_format($totalItDept, 0) }}</td>
                    <td class="text-end">{{ number_format($totalPayable, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (!empty($agencyPayments) && count($agencyPayments) > 0)
        <div style="margin-top: 18px; font-weight: bold;">AgencyWise Total Amount</div>

        @foreach ($agencyPayments as $agencyId => $agencyPaymentRecords)
            @foreach ($agencyPaymentRecords as $agencyPayment)
                @php
                    $infNumbers = $agencyPayment->payments->pluck('inf_number')->filter()->unique()->implode(', ');
                    $rtNumbers = $agencyPayment->payments->pluck('rt_number')->filter()->unique()->implode(', ');
                    $firstPayment = $agencyPayment->payments->first();
                @endphp

                <div style="margin-top: 10px; font-weight: bold;">
                    {{ $agencyPayment->agency->name ?? 'Unknown Agency' }}
                </div>
                <table style="margin-top: 6px;">
                    <thead>
                        <tr>
                            <th>INF Number</th>
                            <th>RT Number</th>
                            <th>Invoice Number</th>
                            <th>Invoice Date</th>
                            <th>Through</th>
                            <th class="text-end">Grand</th>
                            <th class="text-end">Pay(%)</th>
                            <th class="text-end">Gross (15%)</th>
                            <th class="text-end">KPRA By Inf</th>
                            <th class="text-end">KPRA By Dept</th>
                            <th class="text-end">IT By Inf</th>
                            <th class="text-end">IT By Dept</th>
                            <th class="text-end">Payable</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $infNumbers ?: 'N/A' }}</td>
                            <td>{{ $rtNumbers ?: 'N/A' }}</td>
                            <td>{{ $firstPayment?->bill?->invoice_no ?? '' }}</td>
                            <td>{{ $firstPayment?->bill?->invoice_date ? \Carbon\Carbon::parse($firstPayment->bill->invoice_date)->toFormattedDateString() : '' }}
                            </td>
                            <td>Agency</td>
                            <td class="text-end">{{ number_format((float) ($agencyPayment->grand_amount ?? 0), 0) }}
                            </td>
                            <td class="text-end">15</td>
                            <td class="text-end">
                                {{ number_format((float) ($agencyPayment->gross_amount_15_percent ?? 0), 0) }}</td>
                            {{-- keep exact same mapping as blade --}}
                            <td class="text-end">{{ number_format((float) ($agencyPayment->it_inf ?? 0), 0) }}</td>
                            <td class="text-end">{{ number_format((float) ($agencyPayment->it_department ?? 0), 0) }}
                            </td>
                            <td class="text-end">{{ number_format((float) ($agencyPayment->kpra_inf ?? 0), 0) }}</td>
                            <td class="text-end">{{ number_format((float) ($agencyPayment->kpra_department ?? 0), 0) }}
                            </td>
                            <td class="text-end">{{ number_format((float) ($agencyPayment->net_dues ?? 0), 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        @endforeach
    @endif
</body>

</html>

@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between gap-2 align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5-reset-margin">NewspaperWise Total Amount</h5>
                </div>
                <div class="d-flex gap-2">
                    {{-- Global Search Form --}}
                    <label for="search" class="form-label mt-2">Search:</label>
                    <div class="input-group position-relative ">
                        <i class='bx bx-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted'
                            style="z-index: 5; pointer-events: none;"></i>
                        <input type="text" name="search"
                            class="form-control rounded-pill form-control-sm ps-4 js-local-search-input"
                            placeholder="Search..." value="{{ request('search') }}">

                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="{{ route('payment.newspapers.bulkview.export.excel', request()->query()) }}"
                        class="custom-excel-button me-2">Export Excel</a>
                    <a href="{{ route('payment.newspapers.bulkview.export.pdf', request()->query()) }}"
                        class="custom-pdf-button">Export PDF</a>
                </div>

            </div>

            <div class="table-responsive">
                @foreach ($payments as $newspaperId => $rows)
                    <div class="card mb-4">
                        <div class="card-header fw-bold fs-5">
                            {{ $rows->first()->newspaper->title }}
                        </div>
                        @if ($payments->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-bordered-stripped w-100 mb-0">
                                    <thead>
                                        <tr>
                                            <th>INF Number</th>
                                            <th>RT Number</th>
                                            <th>Invoice Number</th>
                                            <th>Invoice Date</th>
                                            <th>Through Media</th>
                                            <th class="text-end">Grand Amount</th>
                                            <th class="text-end">Pay(%)</th>
                                            <th class="text-end">100%, 85% or 15% <br> Gross Amount</th>
                                            <th class="text-end">KPRA Tax By Inf</th>
                                            <th class="text-end">KPRA Tax By Dept</th>
                                            <th class="text-end">I.T Tax By Inf</th>
                                            <th class="text-end">I.T Tax By Dept</th>
                                            <th class="text-end">Payable Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody class="js-local-filter-rows">
                                        @php
                                            $totalAmount = 0;
                                            $totalPayable = 0;
                                            $totalGrossAmount = 0;
                                            $totalKpraInf = 0;
                                            $totalKpraDept = 0;
                                            $totalItInf = 0;
                                            $totalItDept = 0;
                                        @endphp

                                        @foreach ($rows as $row)
                                            @php
                                                $totalAmount += $row->total_amount;
                                                $totalPayable += $row->net_dues;
                                                $totalGrossAmount += $row->gross_amount_100_or_85_percent;
                                                $totalKpraInf += $row->kpra_inf;
                                                $totalKpraDept += $row->kpra_dept;
                                                $totalItInf += $row->it_inf;
                                                $totalItDept += $row->it_dept;
                                            @endphp

                                            <tr>
                                                <td>{{ $row->inf_number }}</td>
                                                <td>{{ $row->rt_number }}</td>
                                                <td>{{ $row->bill->invoice_no }}</td>
                                                <td>{{ $row->bill->invoice_date ? \Carbon\Carbon::parse($row->bill->invoice_date)->toFormattedDateString() : 'N/A' }}
                                                </td>
                                                <td class="text-end">
                                                    @if ($row->payment_type == 'direct')
                                                        <span>Newspaper</span>
                                                    @else
                                                        <span>Agency</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ number_format($row->total_amount) }}</td>

                                                <td class="text-end">
                                                    @if ($row->payment_type == 'direct')
                                                        <span>100</span>
                                                    @else
                                                        <span>85</span>
                                                    @endif
                                                </td>

                                                <td class="text-end">
                                                    {{ number_format($row->gross_amount_100_or_85_percent) }}
                                                </td>
                                                <td class="text-end">{{ number_format($row->kpra_inf) }}</td>
                                                <td class="text-end">{{ number_format($row->kpra_dept) }}</td>
                                                <td class="text-end">{{ number_format($row->it_inf) }}</td>
                                                <td class="text-end">{{ number_format($row->it_dept) }}</td>
                                                <td class="text-end">{{ number_format($row->net_dues) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td colspan="3">Total Amounts :</td>
                                            <td class="text-end">{{ number_format($totalAmount) }}</td>
                                            <td></td>
                                            <td class="text-end">{{ number_format($totalGrossAmount) }}</td>
                                            <td class="text-end">{{ number_format($totalKpraInf) }}</td>
                                            <td class="text-end">{{ number_format($totalKpraDept) }}</td>
                                            <td class="text-end">{{ number_format($totalItInf) }}</td>
                                            <td class="text-end">{{ number_format($totalItDept) }}</td>
                                            <td class="text-end">{{ number_format($totalPayable) }}</td>
                                        </tr>
                                    </tfoot>

                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">No Book to show</div>
                        @endif
                    </div>
                @endforeach

            </div>
        </div>
        <!-- Agency Payments Section -->
        @if ($agencyPayments && count($agencyPayments) > 0)
            <div class="card mb-4" style="padding: 0; margin-top: 30px;">
                <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                    <h5 class="h5-reset-margin">AgencyWise Total Amount</h5>
                </div>
                @foreach ($agencyPayments as $agencyId => $agencyPaymentRecords)
                    @foreach ($agencyPaymentRecords as $agencyPayment)
                        <div class="table-responsive">
                            <table class="table table-bordered-stripped w-100 mb-0">
                                <thead>
                                    <br>
                                    <div class="card-header fw-bold fs-5">
                                        {{ $agencyPayment->agency->name ?? 'Unknown Agency' }}
                                    </div>
                                    <tr>
                                        <th>INF Nnumber</th>
                                        <th>RT Number</th>
                                        <th>Invoice Number</th>
                                        <th>Invoice Date</th>
                                        <th>Through Media</th>
                                        <th class="text-end">Grand Amount</th>
                                        <th>Pay(%)</th>
                                        <th class="text-end">Gross Amount (15%)</th>
                                        <th class="text-end">KPRA Tax By Inf </th>
                                        <th class="text-end">KPRA Tax By Dept </th>
                                        <th class="text-end">I.T Tax By Inf</th>
                                        <th class="text-end">I.T Tax By Dept</th>
                                        <th class="text-end">Payable Amount</th>

                                    </tr>
                                </thead>

                                <tbody>
                                    @php

                                        $infNumbers = $agencyPayment->payments
                                            ->pluck('inf_number')
                                            ->filter()
                                            ->unique()
                                            ->implode(', ');
                                        $rtNumbers = $agencyPayment->payments
                                            ->pluck('rt_number')
                                            ->filter()
                                            ->unique()
                                            ->implode(', ');
                                    @endphp

                                    <tr>
                                        {{-- <td>{{ $agencyPayment->agency->name ?? 'Unknown Agency' }}</td> --}}
                                        <td>
                                            {{ $infNumbers ?: 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $rtNumbers ?: 'N/A' }}
                                        </td>
                                        <td>{{ $agencyPayment->payments->first()->bill->invoice_no }}</td>
                                        <td>{{ $agencyPayment->payments->first()->bill->invoice_date ? \Carbon\Carbon::parse($agencyPayment->payments->first()->bill->invoice_date)->toFormattedDateString() : 'N/A' }}
                                        </td>
                                        <td>Agency</td>
                                        <td class="text-end">{{ number_format($agencyPayment->grand_amount) }}</td>
                                        <td>15</td>
                                        <td class="text-end">
                                            {{ number_format($agencyPayment->gross_amount_15_percent) }}
                                        </td>
                                        <td class="text-end">{{ number_format($agencyPayment->it_inf) }}</td>
                                        <td class="text-end">{{ number_format($agencyPayment->it_department) }}</td>
                                        <td class="text-end">{{ number_format($agencyPayment->kpra_inf) }}</td>
                                        <td class="text-end">{{ number_format($agencyPayment->kpra_department) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($agencyPayment->net_dues) }}</td>
                                    </tr>
                    @endforeach
                @endforeach
                </tbody>
                </table>
            </div>
    </div>
    @endif
    </div>
@endpush

@push('scripts')
    <script>
        (function() {
            function normalize(s) {
                return (s || '').toString().toLowerCase();
            }

            function applyLocalFilter(query) {
                const q = normalize(query).trim();
                const bodies = document.querySelectorAll('tbody.js-local-filter-rows');
                bodies.forEach((tbody) => {
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    rows.forEach((tr) => {
                        const hay = normalize(tr.innerText || tr.textContent || '');
                        tr.style.display = !q || hay.includes(q) ? '' : 'none';
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const input = document.querySelector('.js-local-search-input');
                const btn = document.querySelector('.js-local-search-btn');
                if (!input) return;

                input.addEventListener('input', function() {
                    applyLocalFilter(input.value);
                });
                if (btn) {
                    btn.addEventListener('click', function() {
                        applyLocalFilter(input.value);
                    });
                }
            });
        })();
    </script>
@endpush

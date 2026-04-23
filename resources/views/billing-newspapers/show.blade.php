@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">

            {{-- Title (Header) --}}
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5-reset-margin">Newspaper Bill Details</h5>
                </div>
                @if (!empty($inf_number))
                    <div class="inf-badge">
                        <span class="icon"><i class='bx bxs-purchase-tag'></i></span>
                        <span class="label">INF No.</span>
                        <span class="number">{{ $inf_number }}</span>
                    </div>
                @endif
            </div>

            {{-- Body --}}
            <div class="table-responsive text-nowrap"></div>
            <table class="table w-100">
                <tbody>
                    <tr>
                        <td class="fw-bold">Invoice No:</td>
                        <td class="text-start">{{ $billDetailShow->invoice_no ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Invoice Date:</td>
                        <td class="text-start">
                            {{ date('d M Y', strtotime($billDetailShow->invoice_date)) ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Publication Date:</td>
                        <td class="text-start">
                            {{ date('d M Y', strtotime($billDetailShow->publication_date)) ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Size:</td>
                        <td class="text-start">{{ str_replace('*', 'x', $billDetailShow->original_space) }} =
                            {{ $billDetailShow->printed_size ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Rate:</td>
                        <td class="text-start">
                            Rs. {{ $billDetailShow->printed_rate ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Number of insertion:</td>
                        <td class="text-start">{{ $billDetailShow->printed_no_of_insertion ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Bill Cost:</td>
                        <td class="text-start">Rs. {{ number_format(round($billDetailShow->printed_bill_cost)) ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">KPRA Tax:</td>
                        <td class="text-start">Rs. {{ number_format(round($billDetailShow->kpra_tax)) ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Total Bill:</td>
                        <td class="text-start">Rs. {{ number_format(round($billDetailShow->printed_total_bill)) ?? 'N/A' }}</td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
@endpush

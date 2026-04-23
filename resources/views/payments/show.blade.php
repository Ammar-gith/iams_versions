@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="container">

        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                Batch Ledger
            </div>

            <div class="card-body">
                <p><strong>Cheque Number:</strong> {{ $batch->cheque_number }}</p>
                <p><strong>Cheque Date:</strong> {{ $batch->cheque_date }}</p>
                <p><strong>Department:</strong> {{ $batch->received_from_department }}</p>
                <p><strong>Total Cheque Amount:</strong> {{ number_format($batch->total_cheque_amount, 2) }}</p>
                <p><strong>Total Distributed:</strong> {{ number_format($distributed, 2) }}</p>
                <p><strong>Remaining Balance:</strong> {{ number_format($remaining, 2) }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Distribution Details
            </div>

            <div class="card-body">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>INF</th>
                            <th>Newspaper</th>
                            <th>Paid Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($batch->distributions as $dist)
                            <tr>
                                <td>{{ $dist->bill->id }}</td>
                                <td>{{ $dist->bill->inf_number }}</td>
                                {{-- <td>
                                    @foreach ($dist->bill->newspapers as $newspaper)
                                        {{ $newspaper->title }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </td> --}}
                                <td>{{ $dist->bill->newspaper->title ?? '' }}</td>
                                <td>{{ number_format($dist->paid_amount, 2) }}</td>
                                <td>{{ $dist->created_at->format('d-m-Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    </div>
@endpush

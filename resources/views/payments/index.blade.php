@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

<div class="container">

    <div class="card">
        <div class="card-header bg-dark text-white">
            Payment Batches
        </div>

        <div class="card-body">

            <a href="{{ route('payment.batches.create') }}"
               class="btn btn-primary mb-3">
                Create New Batch
            </a>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Cheque No</th>
                        <th>Date</th>
                        <th>Department</th>
                        <th>Total Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($batches as $batch)
                    <tr>
                        <td>{{ $batch->cheque_number }}</td>
                        <td>{{ $batch->cheque_date }}</td>
                        <td>{{ $batch->received_from_department }}</td>
                        <td>{{ number_format($batch->total_cheque_amount,2) }}</td>
                        <td>
                            <a href="{{ route('payment.batches.show',$batch->id) }}"
                               class="btn btn-sm btn-info">
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

</div>
@endpush

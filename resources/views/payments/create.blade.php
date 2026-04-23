@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="container">

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Create Payment Batch
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('payment.batches.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Cheque Number</label>
                            <input type="text" name="cheque_number" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label>Cheque Date</label>
                            <input type="date" name="cheque_date" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label>Department</label>
                            <input type="text" name="department" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label>Total Cheque Amount</label>
                            <input type="number" step="0.01" name="total_amount" id="cheque_amount" class="form-control"
                                required>
                        </div>
                    </div>

                    <hr>

                    <h5>Pending INF Bills</h5>

                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>INF</th>
                                <th>Newspaper</th>
                                <th>Bill Amount</th>
                                <th>Total Paid</th>
                                <th>Remaining</th>
                                <th>Enter Paid Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingBills as $bill)
                                <tr>
                                    <td>{{ $bill->inf_number }}</td>
                                    <td>{{ $bill->newspaper->title ?? '' }}</td>
                                    <td>{{ number_format($bill->printed_bill_cost, 2) }}</td>
                                    <td>{{ number_format($bill->paid_amount, 2) }}</td>
                                    <td>
                                        {{ number_format($bill->printed_bill_cost - $bill->paid_amount, 2) }}
                                    </td>

                                    <td>
                                        <input type="hidden" name="bill_id[]" value="{{ $bill->id }}">
                                        <input type="number" step="0.01" name="paid_amount[]"
                                            class="form-control paid-amount" onkeyup="calculateTotal()">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Total Distributed:
                                <span id="distributed">0.00</span>
                            </h6>
                        </div>

                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-success">
                                Save Payment Batch
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>

    <script>
        function calculateTotal() {
            let total = 0;

            document.querySelectorAll('.paid-amount').forEach(function(input) {
                total += parseFloat(input.value) || 0;
            });

            document.getElementById('distributed').innerText = total.toFixed(2);
        }
    </script>

@endpush

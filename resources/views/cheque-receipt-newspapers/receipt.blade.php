@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <h5 class="h5-reset-margin">Receipts</h5>
                {{-- @if ($treasuryChallan->isEmpty())
                    <span class="text-muted">No Receipts to show</span>
                @endif --}}
                <div class="inf-badge">
                    <span class="icon"><i class='bx bxs-purchase-tag'></i></span>
                    <span class="label">Challan ID.</span>
                    {{-- @foreach ($chequeReceiptsNps as $key => $chequeReceiptNp) --}}
                    <td>{{ $treasuryChallan->id }}</td>
                    {{-- @endforeach --}}

                </div>
                <div class="inf-badge">
                    <span class="icon"><i class='bx bxs-purchase-tag'></i></span>
                    <span class="label">INF No.</span>
                    <td>{{ implode(', ', $inf_numbers) }}</td>
                </div>
            </div>
            {{-- Get the authenticated logged in user --}}
            {{-- @php
                $user = Auth::User();
            @endphp --}}

            {{-- Show ads if any --}}
            {{-- @if ($billClassifiedAds->isNotEmpty()) --}}
            <div class="table-responsive text-nowrap">
                <form action="" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="table-responsive text-nowrap">
                        <table class="table w-100">
                            <thead>
                                <tr>
                                    <th>Newspaper</th>
                                    <th>Total Dues</th>
                                    <th>I.T By INF(%)</th>
                                    <th>I.T By Deptt(%)</th>
                                    <th>KPRA(%)</th>
                                    <th>SBP</th>
                                    <th>Net Dues</th>
                                    <th>Received</th>
                                    <th>Balance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($receiptDetails as $receipt)
                                    <tr>
                                        <td>

                                            <input type="text" name="receipts[{{ $loop->index }}][newspaper]"
                                                class=" form-control-sm "
                                                style="border: 1px solid rgb(224, 223, 223); outline:none; color:gray;"
                                                value="{{ $receipt['newspaper'] }}" readonly>
                                            <input type="hidden" name="receipts[{{ $loop->index }}][id]"
                                                value="{{ $receipt['id'] }}">
                                        </td>

                                        <td>
                                            <input type="text" name="receipts[{{ $loop->index }}][printed_total_bill]"
                                                class="form-control form-control-sm"
                                                value="{{ $receipt['printed_total_bill'] }}" readonly>
                                        </td>

                                        <td>
                                            <input type="text" name="receipts[{{ $loop->index }}][income_tax_rate]"
                                                class="form-control form-control-sm"
                                                value="{{ $receipt['income_tax_rate'] }}">
                                        </td>

                                        <td>
                                            <input type="text" name="receipts[{{ $loop->index }}][it_department]"
                                                class="form-control form-control-sm" value="">
                                        </td>

                                        <td>
                                            <input type="text" name="receipts[{{ $loop->index }}][kpra]"
                                                class="form-control form-control-sm" value="">
                                        </td>

                                        <td>
                                            <input type="text" name="receipts[{{ $loop->index }}][sbp_charges]"
                                                class="form-control form-control-sm" value="">
                                        </td>
                                        {{-- Net Dues --}}
                                        <td>
                                            <input type="text" name="receipts[{{ $loop->index }}][total_after_tax]"
                                                class="form-control form-control-sm net-dues"
                                                value="{{ $receipt['total_after_tax'] }}" readonly>
                                        </td>


                                        <td>
                                            <input type="text" name="receipts[{{ $loop->index }}][total_after_tax]"
                                                class="form-control form-control-sm received-amount"
                                                value="{{ $receipt['total_after_tax'] }}">
                                        </td>

                                        <td>
                                            <input type="text" name="receipts[{{ $loop->index }}][balance]"
                                                class="form-control form-control-sm balance-amount" value="0">
                                        </td>
                                        <td>
                                            <a href="#" type="button" class="btn btn-danger btn-sm remove-row">
                                                <li class="fa fa-remove"></li>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn custom-primary-button">Save</button>
                    </div>
                </form>

                {{-- <div class="custom-pagination">
                        {{ $advertisements->links() }}
                    </div> --}}
            </div>
            {{-- @endif --}}
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Listen for input on all received fields
            document.querySelectorAll('.received-amount').forEach(function(input) {
                input.addEventListener('input', function() {
                    const row = this.closest('tr');
                    const netDues = parseFloat(row.querySelector('.net-dues').value) || 0;
                    const received = parseFloat(this.value) || 0;
                    const balance = netDues - received;

                    // Set balance value
                    row.querySelector('.balance-amount').value = balance.toFixed(2);
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.remove-row').forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    // remove row
                    this.closest('tr').remove();
                });
            });
        });
    </script>
@endpush

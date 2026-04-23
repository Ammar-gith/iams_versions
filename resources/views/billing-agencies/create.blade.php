@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="flex-grow-1">
        <div class="row">
            {{-- Media Edit Form --}}
            {{-- @if ($newspapers->isNotEmpty()) --}}
            <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
                <form method="POST" action="{{ route('billings.agencies.store', $advertisement->id) }}"
                    enctype="multipart/form-data" class="card-body" style="padding: 0;">
                    @csrf
                    @method('PUT')
                    {{-- Title (Header) --}}
                    {{-- <div class="form-header flex w-full">
                        <h4 class="h5-reset-margin text-light">Billing Receipt</span></h4>
                        <a href="{{ url()->previous() }}" class="custom-secondary-button">← Back</a>
                    </div> --}}

                    <div style="background-color:rgb(144, 187, 167)" class="card-header-table ">
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                            <h5 class="h5-reset-margin">Billing Receipt</h5>
                        </div>
                    </div>

                    {{-- Advertisement Form --}}
                    <div class="form-padding">
                        <div class="row">


                            {{-- INF Number --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label-x" for="inf-no">INF Number</label>
                                <input type="text" id="inf-no" name="inf_number" class="form-control"
                                    value="{{ $inf_number }}" readonly />
                            </div>

                            {{-- Invoice Number --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label-x" for="invoice-no">Invoice Number</label>
                                <input type="text" id="invoice-no" name="invoice_no" class="form-control"
                                    placeholder="Enter invoice number..." />
                            </div>

                            {{-- Bill date --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label-x" for="invoice-date">Bill Date</label>
                                <input type="text" id="invoice-date" name="invoice_date" class="form-control"
                                    value="{{ now() }}" readonly />
                            </div>
                            {{-- Original Size --}}
                            {{-- <div class="col-md-4 mb-4">
                                        <label class="form-label-x" for="newspaper">Newspapers</label>
                                        <input type="text" id="newspaper" class="form-control"
                                            value="{{ $newspapers->pluck('title')->implode(', ') }}" readonly />

                                        {{-- Hidden input to actually send IDs as array --}
                                        @foreach ($newspapers->pluck('id') as $id)
                                            <input type="hidden" name="newspaper_id[]" value="{{ $id }}">
                                        @endforeach
                                    </div> --}}
                            {{--
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label-x" for="newspaper">Newspapers</label>
                                        <input type="text" id="newspaper" class="form-control"
                                            value="{{ $newspapers->pluck('title')->implode(', ') }}" readonly />
                                    </div> --}}
                            <div class="col md-4 mb-4">
                                <table class="table table-stripped">
                                    <thead>
                                        <th>Newspapers</th>
                                        <th>Position</th>
                                        <th>Rate</th>
                                        <th>Spaces</th>
                                        <th>T.Space</th>
                                        <th>Ins.</th>
                                        <th>Est. Cost</th>
                                        <th>2% KPRA Tax on 85% Newpaper</th>
                                        <th>10% KPRA on 15% Agency</th>
                                        <th>Total Amount With Taxes</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($newspaperDetails as $newspaperDetail)
                                            <tr>
                                                <td>
                                                    <input type="text" id="newspaper" class="form-control"
                                                        value="{{ $newspaperDetail['title'] }}" readonly />
                                                    <input type="hidden" name="newspaper_id[]" class="form-control"
                                                        value="{{ $newspaperDetail['id'] }}" />
                                                </td>
                                                <td>
                                                    <input type="text" id="placement" class="form-control"
                                                        value="{{ $newspaperDetail['placement_position'] . ',' . ' ' . number_format(round($newspaperDetail['placement_rates'])) }}"
                                                        readonly />
                                                    <input type="hidden" name="placements[]" class="form-control"
                                                        value="{{ $newspaperDetail['placement_position'] . ',' . ' ' . $newspaperDetail['placement_rates'] }}" />
                                                </td>
                                                <td>
                                                    <input type="text" id="rate_with_placement"
                                                        name="rates_with_placement[]" class="form-control"
                                                        value="{{ $newspaperDetail['rate_with_placement'] }}" readonly />
                                                </td>
                                                <td>
                                                    <input type="text" id="spaces" name="spaces[]"
                                                        class="form-control"
                                                        value="{{ str_replace('*', 'x', $newspaperDetail['space_used']) }}"
                                                        readonly />
                                                </td>
                                                <td>
                                                    <input type="text" id="total_spaces" name="total_spaces[]"
                                                        class="form-control" value="{{ $newspaperDetail['size_used'] }}"
                                                        readonly />
                                                </td>
                                                <td>
                                                    <input type="text" id="numberOfInsertion" name="insertions[]"
                                                        class="form-control"
                                                        value="{{ $newspaperDetail['numberOfInsertions'] }}" readonly />
                                                </td>
                                                <td>
                                                    <input type="text" id="base_amount" class="form-control"
                                                        value="{{ number_format(round($newspaperDetail['base_amount'])) }}"
                                                        readonly />
                                                    <input type="hidden" name="total_cost_per_newspaper[]"
                                                        class="form-control"
                                                        value="{{ $newspaperDetail['base_amount'] }}" />
                                                </td>
                                                <td>
                                                    <input type="text" id="kpra_newspaper" class="form-control"
                                                        value="{{ number_format(round($newspaperDetail['newspaper_tax_amount'])) }}"
                                                        readonly />
                                                    <input type="hidden" name="newspaper_share_amounts[]"
                                                        class="form-control"
                                                        value="{{ $newspaperDetail['newspaper_share_amounts'] }}">
                                                    <input type="hidden" name="kpra_2_percent_on_85_percent_newspaper[]"
                                                        class="form-control"
                                                        value="{{ $newspaperDetail['newspaper_tax_amount'] }}" />
                                                </td>
                                                <td>
                                                    <input type="text" id="kpra_agency" class="form-control"
                                                        value="{{ number_format(round($newspaperDetail['agency_tax_amount'])) }}"
                                                        readonly />
                                                    <input type="hidden" name="agency_share_amounts[]"
                                                        class="form-control"
                                                        value="{{ $newspaperDetail['agency_share_amounts'] }}">
                                                    <input type="hidden" name="kpra_10_percent_on_15_percent_agency[]"
                                                        class="form-control"
                                                        value="{{ $newspaperDetail['agency_tax_amount'] }}" />
                                                </td>
                                                <td>
                                                    <input type="text" id="amount_with_taxes" class="form-control"
                                                        value="{{ number_format(round($newspaperDetail['tatolBaseAmountWithTax'])) }}"
                                                        readonly />
                                                    <input type="hidden" name="total_amount_with_taxes[]"
                                                        class="form-control"
                                                        value="{{ $newspaperDetail['tatolBaseAmountWithTax'] }}" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>


                            {{-- Original Insertions --}}
                            <div class="col-md-3 mb-4">
                                <label class="form-label-x" for="original-insertions">Total
                                    Insertions</label>
                                <input type="text" id="original-insertions" name="printed_no_of_insertion"
                                    class="form-control" value="{{ $totalInsertions }}" readonly />
                            </div>

                            {{-- Total Dues --}}
                            <div class="col-md-3 mb-4">
                                <label class="form-label-x" for="printed-bill-cost">Total Dues </label>
                                <input type="text" id="total-dues" class="form-control"
                                    value="{{ number_format(round($totalCurrentBill)) }}" />
                                <input type="hidden" id="total-dues" name="printed_bill_cost" class="form-control"
                                    value="{{ $totalCurrentBill }}" />
                            </div>

                            {{-- Total Newspaper KPRA Tax --}}
                            {{-- <div class="col-md-4 mb-4">
                                <label class="form-label-x" for="total-newspapers-kpra-tax">Total Newspaper KPRA Tax</label> --}}
                            <input type="hidden" id="total-newspapers-kpra-tax" name="total_newspapers_tax"
                                class="form-control" value="{{ $totalNewspaperTaxAmount }}" />
                            {{-- </div> --}}

                            {{-- Total Agency KPRA Tax --}}
                            {{-- <div class="col-md-4 mb-4">
                                <label class="form-label-x" for="total-agency-kpra-tax">Total Agnecy KPRA Tax</label> --}}
                            <input type="hidden" id="total-agency-kpra-tax" name="total_agency_tax"
                                class="form-control" value="{{ $totalAgencyTaxAmount }}" />
                            {{-- </div> --}}

                            {{-- KPRA Tax --}}
                            <div class="col-md-3 mb-4">
                                <label class="form-label-x" for="kpra-tax">KPRA Tax</label>
                                <input type="text" id="kpra-tax" name="kpra_tax" class="form-control"
                                    value="{{ number_format(round($allTotalTax)) }}" />
                                <input type="hidden" id="kpra-tax" name="kpra_tax" class="form-control"
                                    value="{{ $allTotalTax }}" />
                            </div>

                            {{-- Net Dues --}}
                            <div class="col-md-3 mb-4">
                                <label class="form-label-x" for="net-dues-id">Net Dues <span
                                        class="text-danger fs-6">(KPRA
                                        tax included)</span></label>
                                <input type="text" id="net-dues" name="printed_total_bill" class="form-control"
                                    value="{{ number_format(round($netDues)) }}" readonly />
                                <input type="hidden" id="net-dues" name="printed_total_bill" class="form-control"
                                    value="{{ $netDues }}" />
                            </div>

                            {{-- Press Cutting --}}
                            {{-- <input type="file" id="press-cutting" name="press_cutting" class="form-control"
                                multiple /> --}}
                            <div class="col-md-9 mb-4">
                                <label class="form-label-x" for="press-cutting">Press Cutting <span
                                        class="form-label-danger">&lpar;Please attach jpg
                                        file&rpar;</span></label>

                                <div id="app1">
                                    <file-uploader :unlimited="true" collection="press_cutting_agency"
                                        class="col-form-label"
                                        :tokens="{{ json_encode(old('press_cutting_agency', [])) }}"
                                        label="Upload Press Cutting" notes="Supported Doc type: PDF"
                                        accept="image/*,application/pdf">
                                    </file-uploader>
                                    <div class="text-danger small mt-1" id="press_cutting_error"></div>
                                </div>
                            </div>

                            {{-- Scanned Bill --}}
                            {{-- <input type="file" id="scanned-bill" name="scanned_bill" class="form-control" /> --}}
                            <div class="col-md-3">
                                <label class="form-label-x" for="scanned-bill">Scanned Bill <span
                                        class="form-label-danger">&lpar;Please attach PDF
                                        file&rpar;</span></label>

                                <div id="app2">
                                    <file-uploader :unlimited="false" max="1" collection="scanned_bill_agency"
                                        class="col-form-label" :tokens="{{ json_encode(old('scanned_bill_agency', [])) }}"
                                        label="Upload Scanned Bill" notes="Supported Doc type: PDF"
                                        accept="image/*,application/pdf">
                                    </file-uploader>
                                    <div class="text-danger small mt-1" id="scanned_bill_error"></div>
                                </div>
                            </div>




                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="buttons-div flex">
                        {{-- Publish Ad --}}
                        <button type="submit" name="action" value="publish"
                            class="custom-primary-button">Submit</button>
                    </div>
                </form>
            </div>
            {{-- @else
                <div class="alert alert-warning mt-4">
                    No authorized newspaper found for this advertisement.
                </div>
            @endif --}}
            {{-- ! / Ad Form --}}
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-file-uploader"></script>

    <script>
        new Vue({
            el: '#app1'
        })

        new Vue({
            el: '#app2'
        })
    </script>
    {{-- date formate --}}
    <script>
        flatpickr("#invoice-date", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"
        });
    </script>
    {{-- calculation for generating current bill by Media users --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get input values form user
            const totalDuesInput = document.getElementById('total-dues');
            const kpraTaxInput = document.getElementById('kpra-tax');
            const netDuesInput = document.getElementById('net-dues');

            function calculateAgencyBill() {
                let totalDues = parseFloat(totalDuesInput.value) || 0;
                let kpraTax = parseFloat(kpraTaxInput.value) || 0;

                // calulate bill
                let netDues = totalDues + kpraTax;
                netDuesInput.value = netDues;
            }

            // Add listiners events to calulate bill on real and time and update on inputs change
            [totalDuesInput, kpraTaxInput].forEach(input => {
                input.addEventListener('input', calculateAgencyBill);
            });

        });
    </script>
@endpush

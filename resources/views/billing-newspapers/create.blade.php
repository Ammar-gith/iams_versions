@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        {{-- Newspaper Billing --}}
        @if ($newspapers->isNotEmpty())
            <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">

                {{-- Title (Header) --}}
                <div class="card-header-table">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                        <h5 class="h5-reset-margin">Billing Receipt</h5>
                    </div>
                </div>

                {{-- Body --}}
                <form method="POST" action="{{ route('billings.newspapers.store', $advertisement->id) }}"
                    enctype="multipart/form-data" class="card-body" style="padding: 0;">
                    @csrf
                    @method('PUT')

                    {{-- Data --}}
                    <div class="form-padding">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="row g-1">

                                    {{-- Hidden Fields --}}
                                    <input type="hidden" name="inf_number" value="{{ $advertisement->inf_number }}" />
                                    <input type="hidden" name="newspaper_id" value="{{ $loggedNewspaperId }}" />
                                    <input type="hidden" id="rate_placement_id" value="{{ $placementRate }}" />
                                    <input type="hidden" id="register_with_kpra"
                                        value="{{ $loggedNewspaper->register_with_kapra }}" />

                                    {{-- Invoice Number --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="invoice-no">Invoice Number</label>
                                        <input type="text" id="invoice-no" class="form-control" name="invoice_no" />
                                    </div>

                                    {{-- Original Size --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="original-size">Original Size <span
                                                class="form-label-note">&lpar;cm&rpar;</span></label>
                                        <div class="input-group">
                                            <input type="text" id="original-size" name="size"
                                                class="form-control copy-source" data-target="printe_size_id"
                                                value="{{ $originalSize }}" readonly>
                                            <input type="hidden" id="original-space" name="original_space"
                                                class="form-control" value="{{ $originalSpace }}" />
                                            <button type="button" class="btn btn-outline-secondary copy-btn"
                                                data-bs-toggle="tooltip" data-bx-placement="top" title="Copy"
                                                data-bs-custom-class="customs-tooltip">
                                                <i class="bx bx-copy"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Rate --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="rate">Rate</label>
                                        <div class="input-group">
                                            <input type="text" id="rate" name="rate"
                                                class="form-control copy-source" data-target="printed_rate_id"
                                                value="{{ $rate }}" readonly>
                                            <button type="button" class="btn btn-outline-secondary copy-btn"
                                                data-bs-toggle="tooltip" data-bx-placement="top" title="Copy"
                                                data-bs-custom-class="customs-tooltip">
                                                <i class="bx bx-copy"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Original Insertions --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="original-insertions">Original
                                            Insertions</label>
                                        <div class="input-group">
                                            <input type="text" id="original-insertions" name="no_of_insertion"
                                                class="form-control copy-source" data-target="printed_insertion_id"
                                                value="{{ $insertion }}" readonly>
                                            <button type="button" class="btn btn-outline-secondary copy-btn"
                                                data-bs-toggle="tooltip" data-bx-placement="top" title="Copy"
                                                data-bs-custom-class="customs-tooltip">
                                                <i class="bx bx-copy"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Estimated Cost --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="estimated-cost">Estimated Cost <span
                                                class="form-label-danger">&lpar;KPRA taxes included if
                                                any&rpar;</span></label>
                                        <input type="text" id="estimated-cost" name="estimated_cost"
                                            class="form-control" value="{{ $uniqueFinalBill }}" readonly />
                                    </div>

                                    {{-- KPRA Tax --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="kpra-tax">KPRA Tax</label>
                                        <input type="text" id="kpra-tax" name="kpra_tax" class="form-control"
                                            value="{{ $kpraTax }}" readonly />
                                    </div>

                                    {{-- Press Cutting --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="press-cutting">Press
                                            Cutting <span class="form-label-danger">&lpar;Please attach jpg
                                                file&rpar;</span></label>
                                        <div id="app2">
                                            <file-uploader :unlimited="true" collection="press_cutting"
                                                class="col-form-label"
                                                :tokens="{{ json_encode(old('press_cutting', [])) }}"
                                                label="Upload Press Cutting" notes="Supported Doc type: PDF"
                                                accept="image/*,application/pdf">
                                            </file-uploader>
                                            <div class="text-danger small mt-1" id="press_cutting_error"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-1">
                                    {{-- Invoice Date --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="invoice-date">Invoice Date</label>
                                        <input type="text" id="invoice-date" name="invoice_date"
                                            value="{{ now() }}" class="form-control" readonly>
                                    </div>

                                    {{-- Printed Size (cm) --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="printe-size">Printed Size <span
                                                class="form-label-note">&lpar;cm&rpar;</span></label>
                                        <input type="text" id="printe_size_id" name="printed_size"
                                            class="form-control" value="" />
                                    </div>

                                    {{-- Printed Rate --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="printed-rate">Printed Rate</label>
                                        <input type="text" id="printed_rate_id" name="printed_rate"
                                            class="form-control" value="" />
                                    </div>

                                    {{-- Printed Insertions --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="printed-insertions">Printed
                                            Insertions <span class="form-label-samll">&lpar;1 by
                                                default&rpar;</span></label>
                                        <input type="text" id="printed_insertion_id" name="printed_no_of_insertion"
                                            class="form-control" value="" />
                                    </div>

                                    {{-- Bill Cost --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="bill-cost">Bill Cost <span
                                                class="form-label-danger">&lpar;KPRA taxes included if
                                                any&rpar;</span></label>
                                        <input type="text" id="bill_cost_id" name="printed_bill_cost"
                                            class="form-control" value="" />
                                    </div>

                                    {{-- Total Bill --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="total-bill">Total Bill</label>
                                        <input type="text" id="total_bill_id" name="printed_total_bill"
                                            class="form-control" value="" />
                                    </div>

                                    {{-- Scanned bill --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="total-bill">Scanned bill <span
                                                class="form-label-danger">&lpar;Please attach PDF
                                                file&rpar;</span></label>
                                        <div id="app">
                                            <file-uploader :unlimited="false" :max="1"
                                                collection="scanned_bill" class="col-form-label"
                                                :tokens="{{ json_encode(old('scanned_bill', [])) }}"
                                                label="Upload Scanned Bill" notes="Supported Doc type: PDF"
                                                accept="image/*,application/pdf">
                                                <!-- Custom Preview -->
                                            </file-uploader>
                                            <div class="text-danger small mt-1" id="scanned_bill_error"></div>
                                        </div>
                                    </div>

                                    {{-- Publication Date --}}
                                    <div class="col-md-12">
                                        <label class="form-label-x col-form-label" for="pub-date">Publication
                                            Date</label>
                                        <input type="text" id="pub-date" name="publication_date"
                                            class="form-control" placeholder="DD-MM-YYYY" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="buttons-div flex">
                        {{-- Submit Ad --}}
                        <button type="submit" name="action" value="publish"
                            class="custom-primary-button">Submit</button>
                    </div>
                </form>

            </div>
        @else
            <div class="alert alert-warning mt-4">
                No authorized Newspapers found for this advertisement.
            </div>
        @endif
        {{-- ! / Ad Form --}}
    </div>
    {{-- ! / Page Content --}}
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-file-uploader"></script>


    {{-- datePicker --}}
    <script>
        flatpickr("#invoice-date", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"

        });
    </script>
    <script>
        flatpickr("#pub-date", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"
        });
    </script>

    <script>
        new Vue({
            el: '#app'
        })

        new Vue({
            el: '#app2'
        })

        new Vue({
            el: '#app3'
        })
    </script>

    {{-- Auto Copy JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // init Bootstrap tooltips (if not already initialized)
            const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

            // handle copy buttons
            document.querySelectorAll('.copy-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // find the source input (previous sibling or .copy-source inside the same input-group)
                    const inputGroup = this.closest('.input-group');
                    const source = inputGroup ? inputGroup.querySelector('.copy-source') : this
                        .previousElementSibling;
                    if (!source) return;

                    const targetId = source.getAttribute('data-target');
                    if (!targetId) return;

                    const target = document.getElementById(targetId);
                    if (!target) return;

                    // set value programmatically
                    target.value = source.value;

                    // Dispatch input and change events so other listeners (calculator) run
                    // ^ use bubbles: true to mimic user-originated events
                    const inputEvent = new Event('input', {
                        bubbles: true
                    });
                    const changeEvent = new Event('change', {
                        bubbles: true
                    });

                    target.dispatchEvent(inputEvent);
                    target.dispatchEvent(changeEvent);

                    // Tooltip feedback (if using Bootstrap tooltip)
                    const tooltipInstance = bootstrap.Tooltip.getInstance(this);
                    if (tooltipInstance) {
                        tooltipInstance.setContent({
                            '.tooltip-inner': 'Pasted!'
                        });
                        tooltipInstance.show();
                        setTimeout(() => {
                            tooltipInstance.setContent({
                                '.tooltip-inner': 'Copy'
                            });
                        }, 1200);
                    }
                });
            });
        });
    </script>

    {{-- calculation for generating current bill by Media users --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get input values from user
            const printedSizeInput = document.getElementById('printe_size_id');
            const printedRateInput = document.getElementById('printed_rate_id');
            const printedInsertionInput = document.getElementById('printed_insertion_id');
            const billCostInput = document.getElementById('bill_cost_id');
            const totalBillInput = document.getElementById('total_bill_id');

            function calculatePrintedBill() {
                let printedSize = parseFloat(printedSizeInput.value) || 0;
                let printedRate = parseFloat(printedRateInput.value) || 0;
                let printedInsertion = parseFloat(printedInsertionInput.value) || 1;
                let placment = parseFloat(document.getElementById('rate_placement_id').value) || 0;
                const registerWithKpra = (document.getElementById('register_with_kpra').value);

                // calculate bill
                let rateWithPalacement = printedRate + (printedRate * placment / 100);
                let billCost = printedSize * rateWithPalacement;

                // Apply KPRA tax if registered
                if (registerWithKpra === 'Yes') {
                    let kpraTax = billCost * 0.02; // 2% KPRA tax
                    billCost += kpraTax;
                }

                let totalBill = billCost * printedInsertion;

                // update the bill cost and total bill inputs
                billCostInput.value = billCost.toFixed(2);
                totalBillInput.value = totalBill.toFixed(2);
            }

            // Add listeners (input + paste + change)
            [printedSizeInput, printedRateInput, printedInsertionInput].forEach(input => {
                ['input', 'paste', 'change'].forEach(eventType => {
                    input.addEventListener(eventType, () => {
                        // Small delay ensures pasted value is read correctly
                        setTimeout(calculatePrintedBill, 0);
                    });
                });
            });

        });
    </script>

    {{-- JS for hiding tooltip --}}
    <script>
        document.querySelectorAll('.copy-btn').forEach((btn) => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling; // source input
                const targetId = input.getAttribute('data-target');
                const targetField = document.getElementById(targetId);

                if (input && targetField) {
                    targetField.value = input.value; // Paste into right field

                    // Tooltip feedback
                    const tooltip = bootstrap.Tooltip.getInstance(this);
                    if (tooltip) {
                        tooltip.setContent({
                            '.tooltip-inner': 'Pasted!'
                        });
                        tooltip.show();

                        // After a short delay hide + remove tooltip
                        setTimeout(() => {
                            tooltip.hide();
                            tooltip.dispose(); // removes tooltip until page reload
                        }, 1200);
                    }
                }
            });
        });
    </script>
@endpush

@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row custom-paddings">
        {{-- Newspaper Billing --}}
        {{-- @if ($newspapers->isNotEmpty()) --}}
        <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">

            {{-- Title (Header) --}}
            <div class="card-header-table">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5-reset-margin">Treasury Challan</h5>
                </div>
            </div>

            {{-- Body --}}
            <form method="POST" action="{{ route('billings.treasury-challans.update') }}" enctype="multipart/form-data"
                class="card-body" style="padding: 0;">
                @csrf
                @method('PUT')
                {{-- Data --}}
                <div class="form-padding">
                    <div class="row g-4">
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-8">
                            <div class="row g-1">
                                {{-- Department Name --}}
                                <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="department">Department Name</label>
                                    <select name="department_id" id="departmentId" class="form-control select2">
                                        <option value="">Select Departments</option>
                                        @foreach ($getDepartments as $getDepartment)
                                            <option value="{{ $getDepartment->id }}"
                                                {{ old('department_id', $treasuryChallan->department_id) == $getDepartment->id ? 'selected' : '' }}>
                                                {{ $getDepartment->name ?? '-' }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                {{-- Office Name --}}
                                <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="officeId">Office Name</label>
                                    <select name="office_id" id="officeId" class="form-control select2">
                                        <option value="" disabled selected>Select Office</option>
                                        @foreach ($getOffices as $getOffice)
                                            <option value="{{ $getOffice->id }}"
                                                {{ old('office_id', $treasuryChallan->office_id) == $getOffice->id ? 'selected' : '' }}>
                                                {{ $getOffice->ddo_name ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- INF Number --}}
                                <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="inf-no">INF Number</label>
                                    <select name="inf_number[]" id="inf-no" class="form-control select2" multiple>
                                        @php
                                            // Always force array
                                            $savedInfNumbers = old('inf_number', $treasuryChallan->inf_number ?? []);
                                        @endphp

                                        @foreach ($allInfNumbers as $inf)
                                            <option value="{{ $inf }}"
                                                {{ in_array($inf, $savedInfNumbers) ? 'selected' : '' }}>
                                                {{ $inf }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                {{-- Memo Number --}}
                                <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="memo-number">Memo Number</label>
                                    <input type="text" id="memo-number" class="form-control" name="memo_number"
                                        value="{{ old('memo_number', $treasuryChallan->memo_number) }}" />
                                </div>

                                {{-- Memo Date --}}
                                <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="memo-date">Memo Date</label>
                                    <input type="date" id="memo-date" name="memo_date" class="form-control"
                                        value="{{ old('memo_date', optional($treasuryChallan->memo_date)->format('Y-m-d')) }}" />
                                </div>

                                {{-- Cheque Number --}}
                                <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="cheque-no">Cheque Number</label>
                                    <input type="text" id="cheque-no" name="cheque_number" class="form-control"
                                        value="{{ old('cheque_number', $treasuryChallan->cheque_number) }}" />
                                </div>

                                {{-- Cheque Date --}}
                                <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="cheque-date">Cheque Date</label>
                                    <input type="date" id="cheque-date" name="cheque_date" class="form-control"
                                        value="{{ old('cheque_date', optional($treasuryChallan->cheque_date)->format('Y-m-d')) }}" />
                                </div>
                                {{-- Cheque covering letter number --}}
                                <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="cheque-no">Cheque Covering Letter Number</label>
                                    <input type="text" id="cheque-cov-let-no" name="cheque_covering_letter_number" class="form-control"
                                        value="{{ old('cheque_covering_letter_number', $treasuryChallan->cheque_covering_letter_number) }}" />
                                </div>

                                {{-- Cheque covering letter Date --}}
                                <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="cheque-date">Cheque Covering Letter Date</label>
                                    <input type="date" id="cheque-cov-let-date" name="cheque_covering_letter_date" class="form-control"
                                        value="{{ old('cheque_covering_letter_date', optional($treasuryChallan->cheque_covering_letter_date)->format('Y-m-d')) }}" />
                                </div>
                                {{-- Newspapers Amount --}}
                                {{-- <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="newspaper-amount">Newspapers
                                        Amount</label>
                                    <input type="text" id="totalBill" name="newspapers_amount" class="form-control"
                                        value="{{ old('newspapers_amount', $treasuryChallan->newspapers_amount) }}" />
                                </div> --}}

                                {{-- Total Amount --}}
                                <div class="col-md-12">
                                    <label class="form-label-x col-form-label" for="total-amount">Cheque Amount</label>
                                    <input type="text" id="totalBillAmount" name="total_amount" class="form-control"
                                        value="{{ old('total_amount', $treasuryChallan->total_amount) }}" />
                                </div>


                            </div>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Save challans --}}
                    <button type="submit" class="custom-primary-button">Update</button>
                </div>
            </form>

        </div>
        {{-- @else --}}
        {{-- <div class="alert alert-warning mt-4">
            No authorized Agencies found for this advertisement.
        </div> --}}
        {{-- @endif --}}
        {{-- ! / Ad Form --}}
    </div>
    {{-- ! / Page Content --}}
@endpush

@push('scripts')
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

    <script>
        //select offices on the basis of department
        $(document).ready(function() {
            $("#departmentId").on("change", function() {
                let departmentId = $(this).val();

                if (departmentId) {
                    $.ajax({
                        url: "{{ route('get.offices.by.department') }}",
                        type: "GET",
                        data: {
                            departmentId: departmentId
                        },
                        success: function(data) {
                            let $officeSelect = $("#officeId");
                            $officeSelect.empty();
                            $officeSelect.append(
                                '<option value="">-- Select Office --</option>');

                            if (data.length > 0) {
                                $.each(data, function(index, office) {
                                    $officeSelect.append('<option value="' + office.id +
                                        '">' + office.ddo_name + '</option>');
                                });
                            } else {
                                $officeSelect.append(
                                    '<option value="">No Office Found</option>');
                            }
                        },
                        error: function(xhr) {
                            console.error("Error fetching offices:", xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>

    <script>
        // fetching inf numbers from advertisemet who's data are in the bill classified ads table
        $(document).ready(function() {
            function fetchInfNumbers() {
                let officeId = $("#officeId").val();
                let departmentId = $("#departmentId").val();

                $.ajax({
                    url: "{{ route('get.inf.numbers') }}",
                    type: "GET",
                    data: {
                        officeId: officeId,
                        departmentId: departmentId
                    },
                    success: function(data) {
                        let $infSelect = $("#inf-no");
                        $infSelect.empty();

                        if ($.isEmptyObject(data)) {
                            $infSelect.append('<option value="">No INF Numbers found</option>');
                        } else {
                            $.each(data, function(index, inf_number) {
                                $infSelect.append('<option value="' + inf_number + '">' +
                                    inf_number + '</option>');
                            });
                        }
                    }
                });
            }

            function fetchTotalBill() {
                let infNumbers = $("#inf-no").val();
                let officeId = $("#officeId").val();
                let departmentId = $("#departmentId").val();

                if (!infNumbers || infNumbers.length === 0) {
                    $("#totalBill").text("0");
                    return;
                }

                $.ajax({
                    url: "{{ route('get.total.bill') }}",
                    type: "GET",
                    data: {
                        infNumbers: infNumbers,
                        officeId: officeId,
                        departmentId: departmentId
                    },
                    success: function(data) {
                        $("#totalBill").val(data.total);
                        $("#totalBillAmount").val(data.total);

                    }
                });
            }

            // Trigger fetch INF numbers
            $("#officeId").on("change", fetchInfNumbers);
            $("#departmentId").on("change", function() {
                if (!$("#officeId").val()) fetchInfNumbers();
            });

            // Trigger total bill fetch on INF number change
            $("#inf-no").on("change", fetchTotalBill);

            // Load once on page ready
            fetchInfNumbers();
        });
    </script>

    <script>
        $(document).ready(function() {
            // Init select2 first
            $('#inf-no').select2();

            // Then set values AFTER init
            let savedInfNumbers = @json($savedInfNumbers);

            if (savedInfNumbers.length => 0) {
                $('#inf-no').val(savedInfNumbers).trigger('change');
            }
        });
    </script>
@endpush

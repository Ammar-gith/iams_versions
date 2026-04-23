@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5-reset-margin">Payment Distribution</h5>
                </div>
                <div class="inf-badge">
                    <span class="icon"><i class='bx bxs-purchase-tag'></i></span>
                    <span class="label">Challan ID:</span>
                    <span>{{ $treasuryChallan->id }}</span>
                </div>
            </div>
            {{ session('error') }}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="">
                <form action="{{ route('payment.newspapers.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="challan_id" value="{{ $treasuryChallan->id ?? '' }}">

                    @php
                        $rowCount = 0;
                    @endphp

                    @foreach ($groupedReceipts as $inf => $receiptDetails)
                        {{-- {{ dd($receiptDetails) }} --}}
                        <div class=" d-flex flex-wrap justify-content-between align-items-center">

                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm bulk-rt-input"
                                    data-inf="{{ $inf }}" placeholder="Enter RT Number in bulk" />

                            </div>
                            <div class="inf-badge mb-2">
                                <span class="icon"><i class="bx bxs-purchase-tag"></i></span>
                                <span class="label">INF No.</span>
                                <span>{{ $inf }}</span>
                            </div>
                            <div></div>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>RT No.</th>
                                        <th>Media Names</th>
                                        <th>KPRA Registered</th>
                                        <th>Total Dues</th>
                                        <th>100%, 85% & 15% <br> Gross Amount</th>
                                        <th>I.T By Inf.</th>
                                        <th>I.T By Dept.</th>
                                        <th>KPRA By Inf.</th>
                                        <th>KPRA By Dept.</th>
                                        <th>Bank</th>
                                        <th>Adj</th>
                                        <th>Net Amounts</th>
                                        <th>Received</th>
                                        {{-- <th>Balance</th> --}}
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $agencyInfo = [];
                                        $agencyTotalPrinted = 0;
                                        $agencyTotalGross = 0;
                                        $agencyTotalKpraTax = 0;
                                        $agencyName = '';
                                        $hasAgencyAds = false;
                                    @endphp

                                    @foreach ($receiptDetails as $receipt)
                                        {{-- {{ dd($receipt) }} --}}
                                        @php
                                            // dd($receipt);

                                            $existingPayment = $receipt['existing_payment'] ?? null;

                                            // For direct ads: printed_total_bill is the gross amount
                                            // For agency ads: gross_amount field is available
                                            $grossAmount =
                                                $receipt['gross_amount'] ?? ($receipt['printed_total_bill'] ?? 0);
                                            $adType = $receipt['ad_type'] ?? 'direct'; // 'direct' or 'agency'

                                            // Collect agency information for summary
                                            if ($adType === 'agency') {
                                                $hasAgencyAds = true;
                                                $agencyTotalPrinted += $receipt['printed_total_bill'] ?? 0;
                                                $agencyTotalGross += $receipt['gross_amount'] ?? 0;
                                                $agencyTotalKpraTax += $receipt['agency_kpra_tax_amount'] ?? 0;

                                                $agencyName = $receipt['agency_name'] ?? 'Agency';
                                                $agencyId = $receipt['agency_id'] ?? 'Agency';
                                            }

                                            if ($existingPayment) {
                                                // Use values from existing payment in database
                                                $rtNumber = $existingPayment->rt_number ?? '';
                                                $itInf = $existingPayment->it_inf ?? 0;
                                                $itDepartment = $existingPayment->it_department ?? 0;
                                                $kpraInf = $existingPayment->kpra_inf ?? 0;
                                                $kpraDepartment = $existingPayment->kpra_department ?? 0;
                                                $sbpCharges = $existingPayment->sbp_charges ?? '';
                                                $adjustment = $existingPayment->adjustment ?? '';
                                                $netDues = $existingPayment->net_dues ?? 0;
                                                $received = $existingPayment->received ?? 0;
                                                $balance = $existingPayment->balance ?? 0;
                                                $status = $existingPayment->status ?? 'UNPAID';
                                                $remarks = $existingPayment->remarks ?? '';

                                                // FORCE CORRECT STATUS: If balance is 0 and received > 0, set to PAID
                                                if (abs($balance) < 0.01 && $received > 0) {
                                                    $status = 'PAID';
                                                }
                                            } else {
                                                // Default values for new payments
                                                $rtNumber = '';
                                                $itInf = $receipt['income_tax_amount'] ?? 0;
                                                $itDepartment = 0;
                                                $kpraInf = $receipt['kpra_tax_amount'] ?? 0;
                                                $kpraDepartment = 0;
                                                $sbpCharges = 0;
                                                $adjustment = 0;
                                                $netDues = $receipt['total_after_income_tax'] ?? 0;
                                                $received = 0;
                                                $balance = $netDues;
                                                $status = 'UNPAID';
                                                $remarks = '';
                                            }

                                            // Get badge class
                                            $badgeClass = 'bg-danger';
                                            $statusText = 'Unpaid';

                                            switch ($status) {
                                                case 'PAID':
                                                    $badgeClass = 'bg-success';
                                                    $statusText = 'Paind';
                                                    break;
                                                case 'PARTIALLY_PAID':
                                                    $badgeClass = 'bg-warning';
                                                    $statusText = 'Partial Paid';
                                                    break;
                                                case 'OVER_PAID':
                                                    $badgeClass = 'bg-info';
                                                    $statusText = 'Over Paid';
                                                    break;
                                                default:
                                                    $badgeClass = 'bg-danger';
                                                    $statusText = 'Unpaid';
                                                    break;
                                            }
                                        @endphp

                                        <tr data-row-index="{{ $rowCount }}" data-inf="{{ $receipt['inf_number'] }}"
                                            data-ad-type="{{ $adType }}" data-initial-status="{{ $status }}"
                                            data-initial-balance="{{ $balance }}"
                                            data-initial-received="{{ $received }}"
                                            data-db-status="{{ $status }}" data-db-balance="{{ $balance }}"
                                            data-ad-type="{{ $adType }}">
                                            <td>
                                                <input type="hidden" name="receipts[{{ $rowCount }}][inf_number]"
                                                    value="{{ $receipt['inf_number'] }}">
                                                <input type="hidden" name="receipts[{{ $rowCount }}][newspaper_id]"
                                                    value="{{ $receipt['newspaper_id'] }}">
                                                <input type="text" name="receipts[{{ $rowCount }}][rt_number]"
                                                    class="form-control form-control-sm" value="{{ $rtNumber }}">
                                                <!-- ADD THIS HIDDEN FIELD FOR PAYMENT TYPE -->
                                                <input type="hidden" name="receipts[{{ $rowCount }}][payment_type]"
                                                    value="{{ $adType }}">

                                            <td>
                                                <input type="text" class="form-control-sm"
                                                    style="border: 1px solid rgb(224, 223, 223); outline:none; color:gray;"
                                                    value="{{ $receipt['newspaper'] }}" readonly>
                                                {{-- @if ($adType === 'agency')
                                                    <small class="text-muted d-block">Agency Ad</small>
                                                @endif --}}
                                            </td>
                                            <td>
                                                <select name="receipts[{{ $rowCount }}][kpra_registered]"
                                                    class="form-control form-control-sm">
                                                    <option value="1"
                                                        {{ $receipt['kpra_registered'] ? 'selected' : '' }}>
                                                        Register
                                                    </option>
                                                    <option value="0"
                                                        {{ !$receipt['kpra_registered'] ? 'selected' : '' }}>Not Register
                                                    </option>
                                                </select>
                                            </td>
                                            <td style="width: 100px;">
                                                <input type="text" name="receipts[{{ $rowCount }}][total_amount]"
                                                    class="form-control form-control-sm"
                                                    value="{{ $receipt['total_dues'] }}" readonly>
                                            </td>
                                            <td>
                                                {{-- Gross amount --}}
                                                @if ($adType === 'direct')
                                                    {{-- For direct ads, printed_total_bill is the gross amount --}}
                                                    <input type="text"
                                                        name="receipts[{{ $rowCount }}][gross_amount_100_or_85_percent]"
                                                        class="form-control form-control-sm total-share-amount"
                                                        value="{{ $receipt['total_dues'] }}" readonly>
                                                @else
                                                    {{-- For agency ads, use gross_amount field --}}
                                                    <input type="text"
                                                        name="receipts[{{ $rowCount }}][gross_amount_100_or_85_percent]"
                                                        class="form-control form-control-sm total-share-amount"
                                                        value="{{ $receipt['gross_amount'] ?? 0 }}" readonly>
                                                @endif
                                            </td>
                                            <td style="width: 100%;">
                                                <input type="text" name="receipts[{{ $rowCount }}][it_inf]"
                                                    class="form-control form-control-sm it-inf-input"
                                                    value="{{ floor($itInf) }}">
                                            </td>
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][it_department]"
                                                    class="form-control form-control-sm it-department-input"
                                                    value="{{ floor($itDepartment) }}">
                                            </td>
                                            <td style="width: 100%;">

                                                <input type="text" name="receipts[{{ $rowCount }}][kpra_inf]"
                                                    class="form-control form-control-sm kpra-inf-input"
                                                    value="{{ floor($kpraInf) }}">
                                            </td>
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][kpra_department]"
                                                    class="form-control form-control-sm kpra-department-input"
                                                    value="{{ floor($kpraDepartment) }}">
                                            </td>
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][sbp_charges]"
                                                    class="form-control form-control-sm sbp-input"
                                                    value="{{ floor($sbpCharges) }}">
                                            </td>
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][adjustment]"
                                                    class="form-control form-control-sm adjustment-input"
                                                    value="{{ floor($adjustment) }}">
                                            </td>
                                            <td>
                                                {{-- net amounts --}}
                                                <input type="text" name="receipts[{{ $rowCount }}][net_dues]"
                                                    class="form-control form-control-sm net-dues-input"
                                                    value="{{ floor($netDues) }}" readonly>
                                            </td>
                                            <td>
                                                {{-- received amount --}}
                                                <input type="text" name="receipts[{{ $rowCount }}][received]"
                                                    class="form-control form-control-sm received-input"
                                                    value="{{ floor($received) }}">

                                                {{-- balance amount --}}
                                                <input type="hidden" name="receipts[{{ $rowCount }}][balance]"
                                                    class="form-control form-control-sm balance-input"
                                                    value="{{ floor($balance) }}" readonly>

                                            </td>

                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][remarks]"
                                                    class="form-control form-control-sm remarks-input" placeholder="text"
                                                    value="{{ $remarks }}">
                                            </td>
                                        </tr>
                                        @php
                                            $rowCount++;
                                        @endphp
                                    @endforeach

                                    {{-- Agency Summary Row --}}
                                    @if ($hasAgencyAds)
                                        @php
                                            // Calculate agency commission (15% of newspaper share amount)
                                            $agencyCommission = $agencyTotalPrinted - $agencyTotalGross;
                                            $incomeTaxOnCommission = ($agencyCommission * 1.5) / 100;
                                            $agencyKpraTaxOnCommission = ($agencyCommission * 10) / 100;
                                            $agencyTotalKpraTaxs = $agencyTotalKpraTax;
                                            $netAgencyCommission =
                                                $agencyCommission -
                                                ($incomeTaxOnCommission + $agencyKpraTaxOnCommission);

                                            // ADD THIS: Check if we actually have agency receipts in this batch
                                            $hasAgencyReceiptsInThisInf = false;
                                            foreach ($receiptDetails as $receipt) {
                                                if (($receipt['ad_type'] ?? 'direct') === 'agency') {
                                                    $hasAgencyReceiptsInThisInf = true;
                                                    break;
                                                }
                                            }
                                            // ✅ ADD THIS LINE
                                            $agencyKpraRegistered = $receipt['agency_kpra_registered'] ?? 0;
                                        @endphp
                                        @if ($hasAgencyReceiptsInThisInf)
                                            <!-- ADD THIS CONDITION -->
                                            <tr style="background-color: #f0f8ff; font-weight: bold; text-align: center;">

                                                <td colspan="5"></td>
                                                <td class="text-danger" colspan="4">
                                                    <em>Agency Commission Summary</em>
                                                </td>
                                                <td colspan="6"></td>
                                            </tr>
                                            <tr style="background-color: #f0f8ff; font-weight: bold;">
                                                <td colspan="2" class="text-danger">
                                                    <strong> {{ $agencyName }}</strong>
                                                    {{-- Hidden inputs for agency data --}}
                                                    <input type="hidden" name="agency_data[agency_id]"
                                                        value="{{ $agencyId }}">
                                                    <input type="hidden" name="agency_data[grand_amount]"
                                                        value="{{ $agencyTotalPrinted }}">
                                                    <input type="hidden" name="agency_data[gross_amount_15_percent]"
                                                        value="{{ $agencyCommission }}">
                                                    <input type="hidden" name="agency_data[it_inf]"
                                                        value="{{ $incomeTaxOnCommission }}">
                                                    <input type="hidden" name="agency_data[it_department]"
                                                        value="0">
                                                    <input type="hidden" name="agency_data[kpra_inf]"
                                                        value="{{ $agencyKpraTaxOnCommission }}">
                                                    <input type="hidden" name="agency_data[kpra_department]"
                                                        value="0">
                                                    <input type="hidden" name="agency_data[sbp_charges]" value="0">
                                                    <input type="hidden" name="agency_data[adjustment]" value="0">
                                                    <input type="hidden" name="agency_data[net_dues]"
                                                        value="{{ $netAgencyCommission }}">
                                                    <input type="hidden" name="agency_data[received]"
                                                        value="{{ $netAgencyCommission }}">
                                                    <input type="hidden" name="agency_data[balance]" value="0">
                                                    <input type="hidden" name="agency_data[remarks]" placeholder="text">
                                                </td>
                                                {{-- ✅ NEW KPRA REGISTER DROPDOWN --}}
                                                <td>
                                                    <select name="agency_data[kpra_registered]"
                                                        class="form-control form-control-sm agency-kpra-select">
                                                        <option value="1"
                                                            {{ ($agencyKpraRegistered ?? 0) == 1 ? 'selected' : '' }}>
                                                            Register</option>
                                                        <option value="0"
                                                            {{ ($agencyKpraRegistered ?? 0) == 0 ? 'selected' : '' }}>Not
                                                            Register</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" readonly
                                                        value="{{ floor($agencyTotalPrinted) }}">

                                                </td>
                                                <td class="text-danger">
                                                    <input type="text"
                                                        class="form-control form-control-sm agency-total" readonly
                                                        value="{{ floor($agencyCommission) }}">
                                                </td>
                                                <td class="text-danger">
                                                    <input type="text"
                                                        class="form-control form-control-sm agency-it-inf"
                                                        value="{{ floor($incomeTaxOnCommission) }}">

                                                </td>
                                                <td class="text-danger">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="0">

                                                </td>
                                                <td class="text-danger">
                                                    <input type="text"
                                                        class="form-control form-control-sm agency-kpra-inf"
                                                        value="{{ floor($agencyKpraTaxOnCommission) }}">

                                                </td>
                                                <td class="text-danger">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="0">

                                                </td>
                                                <td class="text-danger">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="0">

                                                </td>
                                                <td class="text-danger">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="0">

                                                </td>
                                                {{-- net amounts --}}
                                                <td class="text-danger">
                                                    <input type="text" class="form-control form-control-sm agency-net"
                                                        value="{{ floor($netAgencyCommission) }}">

                                                </td>
                                                {{-- recieved amount --}}
                                                <td class="text-danger">
                                                    <input type="text"
                                                        class="form-control form-control-sm agency-received"
                                                        value="{{ floor($netAgencyCommission) }}">
                                                    {{-- balance amount --}}

                                                    <input type="hidden"
                                                        class="form-control form-control-sm agency-balance"
                                                        value="0">

                                                </td>
                                                <td class="text-danger">
                                                    <input type="text" class="form-control form-control-sm"
                                                        placeholder="text" value="text">

                                                </td>

                                            </tr>
                                        @endif
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <hr>
                    @endforeach

                    {{-- @if ($paymentNewspapers->isEmpty()) --}}
                    <div class="text-end mt-3">
                        <button type="submit" class="btn custom-primary-button">Save</button>
                    </div>
                    {{-- @else
                        <div class="text-end mt-3">
                            <button type="submit" class="btn custom-primary-button muted">Save</button>
                    @endif --}}

                </form>
            </div>
        </div>
    </div>
@endpush

@push('styles')
    <style>
        tr[style*="background-color: #f0f8ff"] td {
            border-top: 2px solid #4a90e2;
            border-bottom: 2px solid #4a90e2;
        }

        tr[style*="background-color: #f0f8ff"] td strong {
            color: #2c5282;
        }

        .form-control {
            min-width: 80px;
            max-width: 120px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .custom-paddings {
            padding: 10px;
        }
    </style>
@endpush

@push('scripts')
    {{-- old code  --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            function num(val) {
                return parseFloat(val) || 0;
            }

            // Mark received inputs that are manually edited
            document.querySelectorAll('.received-input').forEach(input => {
                input.addEventListener('input', function() {
                    this.dataset.manual = '1';
                });
            });

            function calculateRow(row) {
                const total = num(row.querySelector('input[name$="[gross_amount_100_or_85_percent]"]')?.value);
                const itInf = num(row.querySelector('.it-inf-input')?.value);
                const itDept = num(row.querySelector('.it-department-input')?.value);
                const kpraInf = num(row.querySelector('.kpra-inf-input')?.value);
                const kpraDept = num(row.querySelector('.kpra-department-input')?.value);
                const sbp = num(row.querySelector('.sbp-input')?.value);
                const adj = num(row.querySelector('.adjustment-input')?.value);

                // ✅ KPRA status
                // ✅ FIXED KPRA CHECK
                const kpraValue = row.querySelector('select[name$="[kpra_registered]"]')?.value;
                const kpraRegistered = Number(kpraValue) === 1;


                let deductions = 0;

                if (kpraRegistered) {
                    // KPRA Registered → exclude KPRA
                    deductions = itInf + itDept + sbp + adj;

                    // // Optional: reset KPRA fields
                    // row.querySelector('.kpra-inf-input').value = 0;
                    // row.querySelector('.kpra-department-input').value = 0;

                } else {
                    // Not Registered → include KPRA
                    deductions = itInf + itDept + kpraInf + kpraDept + sbp + adj;
                }


                // TOTAL DEDUCTIONS (AMOUNT BASED) old code
                // const deductions = itInf + itDept + kpraInf + kpraDept + sbp + adj;

                // NET DUES
                let netDues = total - deductions;
                if (netDues < 0) netDues = 0;

                row.querySelector('.net-dues-input').value = netDues.toFixed(0);

                // RECEIVED
                const receivedInput = row.querySelector('.received-input');

                // Auto-fill ONLY if user has NOT typed
                if (!receivedInput.dataset.manual) {
                    receivedInput.value = netDues.toFixed(0);
                }

                let received = num(receivedInput.value);

                // BALANCE old code
                // let balance = netDues - received - adj;
                // new code
                let balance = netDues - received;

                if (Math.abs(balance) < 0.01) balance = 0;

                row.querySelector('.balance-input').value = balance.toFixed(0);
            }

            // Initialize all rows (excluding agency summary rows)
            document.querySelectorAll("tbody tr:not([style*='background-color: #f0f8ff'])").forEach(function(row) {
                // Initial calculation
                calculateRow(row);

                // Inputs that affect calculation
                const inputs = row.querySelectorAll(
                    '.it-inf-input, .it-department-input, .kpra-inf-input, .kpra-department-input, .sbp-input, .adjustment-input, .received-input'
                );

                inputs.forEach(function(input) {
                    input.addEventListener('input', function() {
                        calculateRow(row);
                    });
                });
            });

            // ✅ KPRA dropdown change event (YOU ASKED THIS)
            document.querySelectorAll('select[name$="[kpra_registered]"]').forEach(select => {
                select.addEventListener('change', function() {
                    const row = this.closest('tr');
                    calculateRow(row);
                });
            });


            // Bulk RT Number functionality
            document.querySelectorAll('.bulk-rt-input').forEach(bulkInput => {
                bulkInput.addEventListener('input', function() {
                    const inf = this.dataset.inf;
                    const value = this.value;

                    document.querySelectorAll(
                        `tr[data-inf="${inf}"] input[name$="[rt_number]"]`
                    ).forEach(input => {
                        // Do not overwrite manual edits
                        if (!input.dataset.manual) {
                            input.value = value;
                        }
                    });
                });
            });

            // Detect manual override for RT numbers
            document.querySelectorAll('input[name$="[rt_number]"]').forEach(input => {
                input.addEventListener('input', function() {
                    this.dataset.manual = '1';
                });
            });
        });
    </script>

    {{-- calculation for agency row  --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            function num(val) {
                return parseFloat((val || '').toString().replace(/,/g, '')) || 0;
            }

            function calculateAgencyRow() {

                const row = document.querySelector('.agency-kpra-select')?.closest('tr');
                if (!row) return;

                const total = num(row.querySelector('.agency-total')?.value);

                const itInf = num(row.querySelector('.agency-it-inf')?.value);
                const itDept = num(row.querySelector('.agency-it-dept')?.value);
                const kpraInf = num(row.querySelector('.agency-kpra-inf')?.value);
                const kpraDept = num(row.querySelector('.agency-kpra-dept')?.value);
                const sbp = num(row.querySelector('.agency-sbp')?.value);
                const adj = num(row.querySelector('.agency-adj')?.value);

                const kpraRegistered = Number(row.querySelector('.agency-kpra-select')?.value) === 1;

                let deductions = 0;

                if (kpraRegistered) {
                    // ✅ KPRA NOT deducted
                    deductions = itInf + itDept + sbp + adj;
                } else {
                    // ✅ KPRA deducted
                    deductions = itInf + itDept + kpraInf + kpraDept + sbp + adj;
                }

                let net = total - deductions;
                if (net < 0) net = 0;

                const netInput = row.querySelector('.agency-net');
                if (netInput) netInput.value = net.toFixed(0);

                const receivedInput = row.querySelector('.agency-received');

                if (receivedInput && !receivedInput.dataset.manual) {
                    receivedInput.value = net.toFixed(0);
                }
            }

            // 🔁 Recalculate on input change
            document.querySelectorAll(
                '.agency-it-inf, .agency-it-dept, .agency-kpra-inf, .agency-kpra-dept, .agency-sbp, .agency-adj'
            ).forEach(input => {
                input.addEventListener('input', calculateAgencyRow);
            });

            // 🔁 Dropdown change
            document.querySelectorAll('.agency-kpra-select').forEach(select => {
                select.addEventListener('change', function() {
                    calculateAgencyRow();
                });
            });

            // ✍️ Manual received override
            document.querySelectorAll('.agency-received').forEach(input => {
                input.addEventListener('input', function() {
                    this.dataset.manual = '1';
                });
            });

            // ✅ Initial run
            calculateAgencyRow();

        });
    </script>
@endpush

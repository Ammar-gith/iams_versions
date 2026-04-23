{{-- @extends('layouts.masterVertical')

@push('content')
    <div class="row custom-paddings">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <h5 class="h5-reset-margin">Payment Distribution</h5>
                <div class="inf-badge">
                    <span class="icon"><i class='bx bxs-purchase-tag'></i></span>
                    <span class="label">Challan ID:</span>
                    <span>{{ $treasuryChallan->id }}</span>
                </div>
            </div>

            <div class="table-responsive text-nowrap">
                <form action="{{ route('payment.newspapers.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="challan_id" value="{{ $treasuryChallan->id ?? '' }}">



                    @php
                        $rowCount = 0;
                    @endphp

                    @foreach ($groupedReceipts as $inf => $receiptDetails)
                        <div class=" d-flex flex-wrap justify-content-between align-items-center">

                            <div class="col-md-3">
                                {{-- <label class="form-label fw-bold">RT Number</label> --}

                                <input type="text" class="form-control form-control-sm bulk-rt-input"
                                    data-inf="{{ $inf }}" placeholder="Enter RT Number to apply to all" />

                            </div>
                            <div class="inf-badge mb-2">
                                <span class="icon"><i class="bx bxs-purchase-tag"></i></span>
                                <span class="label">INF No.</span>
                                <span>{{ $inf }}</span>
                            </div>
                            <div></div>
                        </div>

                        <div class="table-responsive text-nowrap">
                            <table class="table w-100">
                                <thead>
                                    <tr>
                                        <th>RT No.</th>
                                        <th>Media Names</th>
                                        <th>Total Dues</th>
                                        <th>100%, 85% & 15% <br> Gross Amount</th>
                                        <th>I.T By Inf.</th>
                                        <th>I.T By Dept.</th>
                                        <th>KPRA By Inf.</th>
                                        <th>KPRA By Dept.</th>
                                        <th>SBP</th>
                                        <th>Adj</th>
                                        <th>Net Dues</th>
                                        <th>Received</th>
                                        <th>Balance</th>
                                        {{-- <th>Status</th> --}
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($receiptDetails as $receipt)
                                        @php
                                            $existingPayment = $receipt['existing_payment'] ?? null;

                                            if ($existingPayment) {
                                                // Use values from existing payment in database
                                                $rtNumber = $existingPayment->rt_number ?? '';
                                                $itInf = $existingPayment->it_inf ?? 1.5;
                                                $itDepartment = $existingPayment->it_department ?? 0;
                                                $kpraInf = $existingPayment->kpra_inf ?? 0;
                                                $kpraDepartment = $existingPayment->kpra_department ?? 0;
                                                $sbpCharges = $existingPayment->sbp_charges ?? '';
                                                $adjustment = $existingPayment->adjustment ?? '';
                                                $netDues =
                                                    $existingPayment->net_dues ?? $receipt['total_after_income_tax'];
                                                $received =
                                                    $existingPayment->received ?? $receipt['total_after_incometax'];
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
                                                $itInf = '1.5';
                                                $itDepartment = '0';
                                                $kpraInf = '0';
                                                $kpraDepartment = '0';
                                                $sbpCharges = '0';
                                                $adjustment = '0';
                                                $netDues = $receipt['total_after_income_tax'];
                                                $received = $receipt['total_after_income_tax'];
                                                $balance = '0';
                                                $status = 'UNPAID';
                                                $remarks = '';
                                            }

                                            // Get badge class - FORCE based on corrected status
                                            $badgeClass = 'bg-danger';
                                            $statusText = 'Unpaid';

                                            switch ($status) {
                                                case 'PAID':
                                                    $badgeClass = 'bg-success';
                                                    $statusText = 'Paid';
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
                                            data-initial-status="{{ $status }}"
                                            data-initial-balance="{{ $balance }}"
                                            data-initial-received="{{ $received }}"
                                            data-db-status="{{ $status }}" {{-- <!-- Store actual DB status --> --}
                                            data-db-balance="{{ $balance }}">
                                            <td>
                                                <input type="hidden" name="receipts[{{ $rowCount }}][inf_number]"
                                                    value="{{ $receipt['inf_number'] }}">
                                                <input type="text" name="receipts[{{ $rowCount }}][rt_number]"
                                                    class="form-control form-control-sm" value="{{ $rtNumber }}">
                                            </td>
                                            <td>
                                                <input type="hidden" name="receipts[{{ $rowCount }}][newspaper_id]"
                                                    value="{{ $receipt['newspaper_id'] }}">
                                                <input type="text" class="form-control-sm"
                                                    style="border: 1px solid rgb(224, 223, 223); outline:none; color:gray;"
                                                    value="{{ $receipt['newspaper'] }}" readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][total_amount]"
                                                    class="form-control form-control-sm total-amount"
                                                    value="{{ $receipt['printed_total_bill'] }}" readonly>
                                            </td>
                                            <td>
                                                {{-- //gross amount --}
                                                <input type="text" class="form-control form-control-sm "
                                                    value="{{ $receipt['printed_total_bill'] }}" readonly>
                                            </td>
                                            <td>
                                                {{-- <select name="receipts[{{ $rowCount }}][it_inf]"
                                                    class="form-control form-control-sm it-inf-select">
                                                    <option value="0" {{ (float) $itInf == 0 ? 'selected' : '' }}>0
                                                    </option>
                                                    <option value="1.5" {{ (float) $itInf == 1.5 ? 'selected' : '' }}>
                                                        1.5
                                                    </option>
                                                </select> --}
                                                <input type="text" name="receipts[{{ $rowCount }}][it_inf]"
                                                    class="form-control form-control-sm it-inf-input"
                                                    value="{{ $receipt['income_tax_amount'] }}">
                                            </td>
                                            <td>
                                                {{-- <select name="receipts[{{ $rowCount }}][it_department]"
                                                    class="form-control form-control-sm it-department-select">
                                                    <option value="0"
                                                        {{ (float) $itDepartment == 0 ? 'selected' : '' }}>0</option>
                                                    <option value="1.5"
                                                        {{ (float) $itDepartment == 1.5 ? 'selected' : '' }}>1.5</option>
                                                </select> --}
                                                <input type="text" name="receipts[{{ $rowCount }}][it_department]"
                                                    class="form-control form-control-sm it-department-input" value="0">
                                            </td>
                                            <td>
                                                {{-- <select name="receipts[{{ $rowCount }}][kpra_inf]"
                                                    class="form-control form-control-sm kpra-inf-select">
                                                    <option value="0" {{ (float) $kpraInf == 0 ? 'selected' : '' }}>0
                                                    </option>
                                                    <option value="2" {{ (float) $kpraInf == 2 ? 'selected' : '' }}>2
                                                    </option>
                                                </select> --}
                                                <input type="text" name="receipts[{{ $rowCount }}][kpra_inf]"
                                                    class="form-control form-control-sm kpra-inf-input"
                                                    value="{{ $receipt['kpra_tax_amount'] }}">


                                            </td>
                                            <td>
                                                {{-- <select name="receipts[{{ $rowCount }}][Kpra_department]"
                                                    class="form-control form-control-sm kpra-select">
                                                    <option value="0"
                                                        {{ (float) $kpraDepartment == 0 ? 'selected' : '' }}>0
                                                    </option>
                                                    <option value="2"
                                                        {{ (float) $kpraDepartment == 2 ? 'selected' : '' }}>2
                                                    </option>
                                                </select> --}
                                                <input type="text" name="receipts[{{ $rowCount }}][kpra_department]"
                                                    class="form-control form-control-sm kpra-department-input"
                                                    value="0">
                                            </td>
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][sbp_charges]"
                                                    class="form-control form-control-sm sbp-input"
                                                    value="{{ $sbpCharges }}">
                                            </td>
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][adjustment]"
                                                    class="form-control form-control-sm adjustment-input"
                                                    value="{{ $adjustment }}">
                                            </td>
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][net_dues]"
                                                    class="form-control form-control-sm net-dues-input"
                                                    value="{{ $netDues }}" readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][received]"
                                                    class="form-control form-control-sm received-input"
                                                    value="{{ $received }}">
                                            </td>
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][balance]"
                                                    class="form-control form-control-sm balance-input"
                                                    value="{{ $balance }}" readonly>
                                            </td>
                                            {{-- <td>
                                                <span class="badge {{ $badgeClass }} rounded-pill status-badge"
                                                    data-initial-class="{{ $badgeClass }}"
                                                    data-initial-text="{{ $statusText }}"
                                                    data-db-status="{{ $status }}">
                                                    {{ $statusText }}
                                                </span>
                                            </td> --}
                                            <td>
                                                <input type="text" name="receipts[{{ $rowCount }}][remarks]"
                                                    class="form-control form-control-sm remarks-input"
                                                    value="{{ $remarks }}">
                                            </td>
                                            </td>
                                        </tr>
                                        @php
                                            $rowCount++;
                                        @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <hr>
                    @endforeach

                    <div class="text-end mt-3">
                        <button type="submit" class="btn custom-primary-button">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

{{-- @push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Function to calculate net dues and update balance
            function calculateRow(row) {
                let total = parseFloat(row.querySelector('.total-amount').value) || 0;
                // let itInf = parseFloat(row.querySelector('.it-inf-select').value) || 0;
                let itInf = parseFloat(row.querySelector('.it-inf-input').value) || 0;
                let itDept = parseFloat(row.querySelector('.it-department-input').value) || 0;
                let kpraInf = parseFloat(row.querySelector('.kpra-inf-input').value) || 0;
                let kpraDept = parseFloat(row.querySelector('.kpra-department-input').value) || 0;
                let sbp = parseFloat(row.querySelector('.sbp-input').value) || 0;

                // Calculate deductions
                let taxTotal = (total * itInf / 100) + (total * itDept / 100) + (total * kpraInf / 100) + (total *
                    kpraDept / 100) + sbp;


                // NET DUES
                let net = total - taxTotal;
                let netInput = row.querySelector('.net-dues-input');
                netInput.value = net.toFixed(2);

                // RECEIVED
                let received = parseFloat(row.querySelector('.received-input').value) || 0;

                // BALANCE - fix floating point issues
                let balance = net - received;
                balance = Math.round(balance * 100) / 100; // Round to 2 decimal places

                // Fix -0.00 issue
                if (Math.abs(balance) < 0.005) {
                    balance = 0.00;
                }

                row.querySelector('.balance-input').value = balance.toFixed(2);

                // Update status badge based on current values
                updateStatusBadge(row, balance, net, received);
            }

            // Function to update status badge
            function updateStatusBadge(row, balance, net, received) {
                let statusBadge = row.querySelector('.status-badge');

                // Get database status from data attribute
                let dbStatus = row.getAttribute('data-db-status');
                let dbBalance = parseFloat(row.getAttribute('data-db-balance')) || 0;

                // Get current values
                let currentReceived = parseFloat(row.querySelector('.received-input').value) || 0;
                let currentBalance = balance;

                // Check if user has changed anything
                let hasChanged = false;

                // Check if received amount changed (more than 0.01 difference)
                if (Math.abs(currentReceived - parseFloat(row.getAttribute('data-initial-received') || 0)) >
                    0.005) {
                    hasChanged = true;
                }

                // Check if balance changed (more than 0.01 difference)
                if (Math.abs(currentBalance - parseFloat(row.getAttribute('data-initial-balance') || 0)) > 0.005) {
                    hasChanged = true;
                }

                // If no changes, ALWAYS show database status
                if (!hasChanged) {
                    // Use database status regardless of calculations
                    let dbStatusText = '';
                    let dbBadgeClass = '';

                    // Fix: If database balance is 0 and received > 0, it should be PAID
                    let dbReceived = parseFloat(row.getAttribute('data-initial-received') || 0);
                    if (Math.abs(dbBalance) < 0.005 && dbReceived > 0) {
                        dbStatus = 'PAID';
                    }

                    switch (dbStatus) {
                        case 'PAID':
                            dbStatusText = 'Paid';
                            dbBadgeClass = 'bg-success';
                            break;
                        case 'PARTIALLY_PAID':
                            dbStatusText = 'Partial Paid';
                            dbBadgeClass = 'bg-warning';
                            break;
                        case 'OVER_PAID':
                            dbStatusText = 'Over Paid';
                            dbBadgeClass = 'bg-info';
                            break;
                        default:
                            dbStatusText = 'Unpaid';
                            dbBadgeClass = 'bg-danger';
                            break;
                    }

                    statusBadge.textContent = dbStatusText;
                    statusBadge.className = `badge ${dbBadgeClass} rounded-pill status-badge`;
                    statusBadge.setAttribute('data-initial-class', dbBadgeClass);
                    statusBadge.setAttribute('data-initial-text', dbStatusText);
                    return;
                }

                // User has changed values, calculate new status
                let statusText = '';
                let badgeClass = '';

                // Fix for -0.00 issue - use a small tolerance
                if (Math.abs(currentBalance) < 0.005 && currentReceived > 0) {
                    statusText = 'Paid';
                    badgeClass = 'bg-success';
                } else if (currentBalance < -0.005) {
                    statusText = 'Over Paid';
                    badgeClass = 'bg-info';
                } else if (currentReceived > 0 && currentBalance > 0.005) {
                    statusText = 'Partial Paid';
                    badgeClass = 'bg-warning';
                } else {
                    statusText = 'Unpaid';
                    badgeClass = 'bg-danger';
                }

                statusBadge.textContent = statusText;
                statusBadge.className = `badge ${badgeClass} rounded-pill status-badge`;

                // Update the data attributes for future comparisons
                row.setAttribute('data-initial-status',
                    statusText === 'Paid' ? 'PAID' :
                    statusText === 'Partial Paid' ? 'PARTIALLY_PAID' :
                    statusText === 'Over Paid' ? 'OVER_PAID' : 'UNPAID');
                row.setAttribute('data-initial-balance', currentBalance);
                row.setAttribute('data-initial-received', currentReceived);
                statusBadge.setAttribute('data-initial-class', badgeClass);
                statusBadge.setAttribute('data-initial-text', statusText);
            }

            // Initialize calculation for all rows
            document.querySelectorAll("tbody tr").forEach(function(row) {
                // Store initial values from form inputs
                let initialItInf = row.querySelector('.it-inf-input').value;
                let initialItDep = row.querySelector('.it-department-input').value;
                let initialKpraInf = row.querySelector('.kpra-inf-input').value;
                let initialKpraDept = row.querySelector('.kpra-department-input').value;
                let initialSbp = row.querySelector('.sbp-input').value;
                let initialReceived = row.querySelector('.received-input').value;

                row.setAttribute('data-initial-itinf', initialItInf);
                row.setAttribute('data-initial-itdep', initialItDep);
                row.setAttribute('data-initial-kpra', initialKpra);
                row.setAttribute('data-initial-sbp', initialSbp);
                row.setAttribute('data-initial-received', initialReceived);

                // Calculate initial values
                calculateRow(row);

                // Attach event listeners
                let inputs = [
                    row.querySelector('.it-inf-input'),
                    row.querySelector('.it-department-input'),
                    row.querySelector('.kpra-inf-input'),
                    row.querySelector('.kpra-department-input'),
                    row.querySelector('.sbp-input'),
                    row.querySelector('.received-input')
                ];

                inputs.forEach(function(input) {
                    if (input) {
                        input.addEventListener("input", function() {
                            calculateRow(row);
                        });
                        input.addEventListener("change", function() {
                            calculateRow(row);
                        });
                    }
                });
            });
        });
    </script>
@endpush --}}

{{-- new script --}
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            function num(val) {
                return parseFloat(val) || 0;
            }

            document.querySelectorAll('.received-input').forEach(input => {
                input.addEventListener('input', function() {
                    this.dataset.manual = '1';
                });
            });

            function calculateRow(row) {

                const total = num(row.querySelector('.total-amount')?.value);

                const itInf = num(row.querySelector('.it-inf-input')?.value);
                const itDept = num(row.querySelector('.it-department-input')?.value);
                const kpraInf = num(row.querySelector('.kpra-inf-input')?.value);
                const kpraDept = num(row.querySelector('.kpra-department-input')?.value);
                const sbp = num(row.querySelector('.sbp-input')?.value);
                const adj = num(row.querySelector('.adjustment-input')?.value);

                // TOTAL DEDUCTIONS (AMOUNT BASED)
                const deductions = itInf + itDept + kpraInf + kpraDept + sbp;

                // NET DUES
                let netDues = total - deductions;
                if (netDues < 0) netDues = 0;

                row.querySelector('.net-dues-input').value = netDues.toFixed(0);


                // RECEIVED
                const receivedInput = row.querySelector('.received-input');

                // auto-fill ONLY if user has NOT typed
                if (!receivedInput.dataset.manual) {
                    receivedInput.value = netDues.toFixed(0);
                }

                let received = num(receivedInput.value);

                // BALANCE
                let balance = netDues - received - adj;

                if (Math.abs(balance) < 0.01) balance = 0;

                row.querySelector('.balance-input').value = balance.toFixed(0);
            }

            // Initialize all rows
            document.querySelectorAll("tbody tr").forEach(function(row) {

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

            // for bulk RT Number
            // Per-INF bulk RT handling
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

            // Detect manual override
            document.querySelectorAll('input[name$="[rt_number]"]').forEach(input => {
                input.addEventListener('input', function() {
                    this.dataset.manual = '1';
                });
            });



        });
    </script>
@endpush --}}

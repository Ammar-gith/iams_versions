@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        {{-- Ad Form --}}
        <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            {{-- Ad Form --}}
            <form id="AdForm" method="POST" action="{{ route('advertisements.store') }}" enctype="multipart/form-data"
                class="card-body" style="padding: 0;">
                @csrf
                @php
                    $user = Auth::user();
                @endphp

                {{-- Title (Header) --}}
                <div class="form-header flex w-full">

                    <h5 class="h5-reset-margin">New Advertisement Form @if ($user->hasRole('Diary Dispatch'))
                            <span>&lpar;Manual Receipt&rpar;</span>
                        @endif
                    </h5>
                    @can('view inf number')
                        <div class="inf-badge">
                            <span class="icon"><i class='bx bxs-purchase-tag'></i></span>
                            <span class="label">INF No.</span>
                            <span class="number">{{ $preview_inf_number }}</span>
                        </div>
                    @endcan
                </div>

                {{-- Hidden fields --}}
                <input type="hidden" name="new_status" value="{{ $new_status }}">
                <input type="hidden" name="draft_status" value="{{ $draft_status }}">
                <input type="hidden" name="pending_department_status" value="{{ $pending_department_status }}">
                <input type="hidden" name="user_id" value="{{ $user_id }}">

                {{-- Advertisement Form & Department --}}
                <div class="form-body flex">
                    {{-- Memo No., Memo Date --}}
                    <div class="row g-3">
                        {{-- Memo No. --}}
                        <div class="col-md-4">
                            <label class="form-label-x col-form-label" for="memo_number">Memo No.</label>
                            <input type="text" name="memo_number" id="memo_number" class="form-control"
                                placeholder="e.g., ABC123" />
                            <div class="text-danger small mt-1" id="memo_number_error"></div>
                        </div>
                        {{-- Memo Date --}}
                        <div class="col-md-4">
                            <label class="form-label-x col-form-label" for="memo-date">Memo Date</label>
                            <input type="text" name="memo_date" id="memo-date" class="form-control"
                                placeholder="DD-MM-YYYY" />
                            <div class="text-danger small mt-1" id="memo_date_error"></div>
                        </div>
                        @php
                            $isClientOffice = $user->hasRole('Client Office');
                        @endphp

                        {{-- Department --}}
                        <div class="col-md-4">
                            <label class="form-label-x col-form-label" for="department">Department</label>
                            <select id="department" name="department_id" class="select2 form-select" data-allow-clear="true"
                                {{ $isClientOffice ? 'disabled' : '' }}>
                                <option value="">Select Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ auth()->user()->department_id == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger small mt-1" id="department_error"></div>
                            @if ($isClientOffice)
                                <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}">
                            @endif
                        </div>
                    </div>

                    {{-- Office --}}
                    <div class="row g-3">
                        {{-- Office --}}
                        <div class="col-md-4">
                            <label class="form-label-x col-form-label" for="office">Office</label>
                            <select id="office" name="office_id" class="select2 form-select" data-allow-clear="true"
                                {{ $isClientOffice ? 'disabled' : '' }}>
                                <option value="">Select Office</option>
                            </select>
                            <div class="text-danger small mt-1" id="office_error"></div>
                            @if ($isClientOffice)
                                <input type="hidden" name="office_id" value="{{ auth()->user()->office_id }}">
                            @endif
                        </div>

                        {{-- Estimated Cost --}}
                        <div class="col-md-4">
                            <label class="form-label-x col-form-label" for="estimated_cost">Estimated Cost</label>
                            <select id="estimated_cost" name="ad_worth_id" class="select2 form-select"
                                data-allow-clear="true">
                                <option value="">Select Estimated Cost</option>
                                @foreach ($ad_worth_parameters as $ad_worth_parameter)
                                    <option value="{{ $ad_worth_parameter->id }}">{{ $ad_worth_parameter->range }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-danger small mt-1" id="estimated_cost_error"></div>
                        </div>

                        {{-- Ad Category --}}
                        <div class="col-md-4">
                            <label class="form-label-x col-form-label" for="classified_ad_type_id">Classified Ad
                                Type</label>
                            <select id="classified_ad_type_id" name="classified_ad_type_id" class="select2 form-select"
                                data-allow-clear="true">
                                <option value="">Select Ad Type</option>
                                @foreach ($classifiedAdTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->type }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger small mt-1" id="classified_ad_type_error"></div>
                        </div>
                    </div>

                    {{-- Number of lines (Urdu & English) --}}
                    <div class="row g-3 mb-3">
                        @can('view lines')
                            <h6 class="my-h6 fw-normal mt-4">Number of Lines &lpar;Urdu &amp; English&rpar;</h6>
                            <div class="col-md-4 mt-0">
                                <label class="form-label-x col-form-label" for="urdu-lines">Urdu lines</label>
                                <input type="text" id="urdu_lines" name="urdu_lines" class="form-control"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Enter urdu Lines" />
                                <div class="text-danger small mt-1" id="urdu_lines_error"></div>

                            </div>
                            <div class="col-md-4 mt-0">
                                <label class="form-label-x col-form-label" for="eng-lines">English lines</label>
                                <input type="text" id="english_lines" name="english_lines" class="form-control"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    placeholder="Enter english Lines" />
                                <div class="text-danger small mt-1" id="english_lines_error"></div>

                            </div>
                        @endcan

                        {{-- Publication Date --}}
                        <div class="col-md-4 mt-0">
                            <label class="form-label-x col-form-label" for="pub-date">Publish on or Before</label>
                            <input type="text" id="pub-date" name="publish_on_or_before" class="form-control"
                                placeholder="DD-MM-YYYY" />
                            <div class="text-danger small mt-1" id="pub_date_error"></div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        {{-- Source of Fund --}}
                        <div class="col-md-4">
                            <label class="form-label-x col-form-label" for="sourceoffund">Source of Fund</label>
                            <select id="SouceOfFundId" name="source_of_fund" class="select2 form-select"
                                data-allow-clear="true" required>
                                <option value="">Select Source of Fund</option>
                                <option value="ADP">ADP</option>
                                <option value="None-ADP">None-ADP</option>
                            </select>
                            <div class="text-danger small mt-1" id="office_error"></div>
                        </div>

                        {{-- ADP Code --}}
                        <div class="col-md-4" id="adpCodeField" style="display: none;">
                            <label class="form-label-x col-form-label" for="adp_code">ADP Code</label>
                            <input type="text" name="adp_code" id="adp_code" class="form-control"
                                placeholder="e.g., ABC123" />
                            <div class="text-danger small mt-1" id="adp_code_error"></div>
                        </div>

                        {{-- Project Name --}}
                        <div class="col-md-4" id="projectNameField" style="display: none;">
                            <label class="form-label-x col-form-label" for="project_name">Project Name</label>
                            <input type="text" name="project_name" id="project_name" class="form-control"
                                placeholder="Enter project name" />
                            <div class="text-danger small mt-1" id="project_name_error"></div>
                        </div>
                    </div>

                    {{-- Ad Size (Urdu & English) --}}
                    @can('view sizes')
                        <div class="row g-3 mb-3">
                            <h6 class="my-h6 fw-normal mt-4">Advertisement Size &lpar;Urdu &amp; English &rpar;</h6>
                            <div class="col-md-6 mt-0">
                                <label class="form-label-x col-form-label" for="urdu-size">Urdu size</label>
                                <input type="text" id="urdu_size_input" class="form-control" placeholder="e.g. 2*3">
                                <div class="text-danger small mt-1" id="urdu_size_input_error"></div>
                                <input type="hidden" id="urdu_size_result_input" name="urdu_size">
                                <div class="text-danger small mt-1" id="urdu_size_error"></div>
                                <span id="urdu_size_result"
                                    style="position: absolute; top: 55.5%; left: 110px; transform: translateY(-50%);font-weight: bold; color: green; pointer-events: none;">
                                </span>
                            </div>
                            <div class="col-md-6 mt-0">
                                <label class="form-label-x col-form-label" for="english_size">English size</label>
                                <input type="text" id="eng_size_input" class="form-control" placeholder="e.g. 2*3">
                                <div class="text-danger small mt-1" id="eng_size_input_error"></div>
                                <input type="hidden" id="eng_size_result_input" name="english_size">
                                <div class="text-danger small mt-1" id="english_size_error"></div>
                                <span id="eng_size_result"
                                    style="position: absolute; top: 55.5%; left: 560px; transform: translateY(-50%); font-weight: bold; color: green; pointer-events: none;">
                                </span>
                            </div>
                        </div>
                    @endcan

                    {{-- PDF Attachements --}}
                    <div class="row g-3">
                        <h6 class="my-h6 h6-design fw-normal"><i class="bx bx-bell custom-icon"></i>
                            <span>Note:</span> Please upload Covering Letter and actual Advertisement.
                            {{-- in separate PDF files. --}}
                        </h6>

                        {{-- File upload covering letter --}}
                        <div id = "app" class="row g-3 justify-content-between">
                            <div class="col-sm-4">
                                <file-uploader :unlimited="false" :max="1" collection="covering_letters"
                                    class="col-form-label" :tokens="{{ json_encode(old('covering_letters', [])) }}"
                                    label="Upload Covering Letter" notes="Supported Doc type: PDF/Images"
                                    accept="image/jpeg,image/png,image/jpg,application/pdf">
                                    <!-- Custom Preview -->
                                </file-uploader>
                                <div class="text-danger small mt-1" id="covering_letter_error"></div>
                            </div>

                            <div class="col-sm-4">
                                <file-uploader :unlimited="false" :max="1" collection="urdu_ads"
                                    class="col-form-label" :tokens="{{ json_encode(old('urdu_ads', [])) }}"
                                    label="Upload Urdu Ads" notes="Supported Doc type: PDF/Images"
                                    accept="image/jpeg,image/png,image/jpg,application/pdf">
                                </file-uploader>
                                <div class="text-danger small mt-1" id="urdu_ad_error"></div>
                            </div>

                            <div class="col-sm-4">
                                <file-uploader :unlimited="false" :max="1" collection="english_ads"
                                    class="col-form-label" :tokens="{{ json_encode(old('english_ads', [])) }}"
                                    label="Upload English Ads" notes="Supported Doc type: PDF/Images"
                                    accept="image/jpeg,image/png,image/jpg,application/pdf">
                                </file-uploader>
                                <div class="text-danger small mt-1" id="english_ad_error"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--  billings and dues --}}
                @can('view billings and dues')
                    <div class="financials-div">
                        <div class="row g-3">
                            <div class="col-md-6 custom-alignment">
                                <div class="some flex">
                                    <label class="form-label-x col-form-label l-green" for="current-bill">Current Bill</label>
                                    <input type="text" id="current-bill" value="" readonly />
                                </div>
                            </div>
                            <div class="col-md-6 custom-alignment">
                                <div class="some flex">
                                    <label class="form-label-x col-form-label l-red" for="previous-dues">Previous Dues</label>
                                    <input type="text" id="previous-dues" value="" readonly />
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Submit Ad --}}
                    <button type="submit" name="action" value="submit-ad" class="custom-primary-button">Submit
                        Ad</button>

                    {{-- Draft Ad --}}
                    <button type="submit" name="action" value="save-draft" class="custom-secondary-button">Save as
                        Draft</button>
                </div>
            </form>
        </div>
        {{-- ! / Ad Form --}}
    </div>
    {{-- ! / Page Content --}}
@endpush

{{-- auto populate the offices data on the basis of selected department --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-file-uploader"></script>
    {{-- datepicker --}}

    <script>
        flatpickr("#memo-date", {
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d"
        });

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
    </script>

    {{-- Ad Form Validation JS --}}
    <script>
        document.getElementById("AdForm").addEventListener("submit", function(event) {
            let valid = true;
            let firstInvalidField = null;

            // Clear previous errors
            document.querySelectorAll(".text-danger").forEach(el => el.textContent = "");

            // Memo Number: letters + digits only
            const memoNumber = document.getElementById("memo_number");
            if (!/^[A-Za-z0-9]+$/.test(memoNumber.value.trim())) {
                document.getElementById("memo_number_error").textContent =
                    "Memo Number must be letters and digits only (e.g., ABC123)";
                valid = false;
                if (!firstInvalidField) firstInvalidField = memoNumber;
            }

            // Memo Date required
            const memoDate = document.getElementById("memo_date");
            if (memoDate.value === "") {
                document.getElementById("memo_date_error").textContent = "Please select Memo Date";
                valid = false;
                if (!firstInvalidField) firstInvalidField = memoDate;
            }

            // Department required
            const department = document.getElementById("department");
            if (!department.value) {
                document.getElementById("department_error").textContent = "Please select Department";
                valid = false;
                if (!firstInvalidField) firstInvalidField = department;
            }

            // Office required
            const office = document.getElementById("office");
            if (!office.value) {
                document.getElementById("office_error").textContent = "Please select Office";
                valid = false;
                if (!firstInvalidField) firstInvalidField = office;
            }

            // Estimated Cost required
            const estimatedCost = document.getElementById("estimated_cost");
            if (!estimatedCost.value) {
                document.getElementById("estimated_cost_error").textContent = "Please select Estimated Cost";
                valid = false;
                if (!firstInvalidField) firstInvalidField = estimatedCost;
            }

            // Ad Category required
            const adCategory = document.getElementById("classified_ad_type_id");
            if (!adCategory.value) {
                document.getElementById("classified_ad_type_error").textContent = "Please select Ad Type";
                valid = false;
                if (!firstInvalidField) firstInvalidField = adCategory;
            }

            // Urdu lines digits only
            const urduLines = document.getElementById("urdu_lines");
            if (!/^[0-9]+$/.test(urduLines.value.trim())) {
                document.getElementById("urdu_lines_error").textContent = "Urdu lines must be digits only";
                valid = false;
                if (!firstInvalidField) firstInvalidField = urduLines;
            }

            // English lines digits only
            const englishLines = document.getElementById("english_lines");
            if (!/^[0-9]+$/.test(englishLines.value.trim())) {
                document.getElementById("english_lines_error").textContent = "English lines must be digits only";
                valid = false;
                if (!firstInvalidField) firstInvalidField = englishLines;
            }

            // Publication date required
            const pubDate = document.getElementById("pub_date");
            if (pubDate.value === "") {
                document.getElementById("pub_date_error").textContent = "Please select Publication Date";
                valid = false;
                if (!firstInvalidField) firstInvalidField = pubDate;
            }

            // Urdu size format: 2 x 5
            const urduSize = document.getElementById("urdu_size_input");
            if (!/^\d+\s*[xX*]\s*\d+$/.test(urduSize.value.trim())) {
                document.getElementById("urdu_size_input_error").textContent = "Urdu size must be like: 2 x 5";
                valid = false;
                if (!firstInvalidField) firstInvalidField = urduSize;
            }

            // English size format: 2 x 5
            const engSize = document.getElementById("eng_size_input");
            if (!/^\d+\s*[xX*]\s*\d+$/.test(engSize.value.trim())) {
                document.getElementById("eng_size_input_error").textContent = "English size must be like: 2 x 5";
                valid = false;
                if (!firstInvalidField) firstInvalidField = engSize;
            }

            // Covering Letter: must be PDF
            const coveringLetter = document.querySelector("input[name='covering_letters[]']");
            if (!coveringLetter || !coveringLetter.files.length || coveringLetter.files[0].type !==
                "application/pdf") {
                document.getElementById("covering_letter_error").textContent =
                    "Please upload Covering Letter (PDF only)";
                valid = false;
                if (!firstInvalidField) firstInvalidField = coveringLetter;
            }

            // Urdu Ad: must be PDF
            const urduAd = document.querySelector("input[name='urdu_ads[]']");
            if (!urduAd || !urduAd.files.length || urduAd.files[0].type !== "application/pdf") {
                document.getElementById("urdu_ad_error").textContent = "Please upload Urdu Ad (PDF only)";
                valid = false;
                if (!firstInvalidField) firstInvalidField = urduAd;
            }

            // English Ad: must be PDF
            const engAd = document.querySelector("input[name='english_ads[]']");
            if (!engAd || !engAd.files.length || engAd.files[0].type !== "application/pdf") {
                document.getElementById("english_ad_error").textContent = "Please upload English Ad (PDF only)";
                valid = false;
                if (!firstInvalidField) firstInvalidField = engAd;
            }

            // Stop submission if invalid
            if (!valid) {
                event.preventDefault();
                if (firstInvalidField) firstInvalidField.focus();
            }
        });
    </script>



    {{-- Office Selection based on Selected Department --}}
    <script>
        const userOfficeId = @json(auth()->user()->office_id);
        $(document).ready(function() {
            $('#department').change(function() {
                var departmentId = $(this).val();
                // console.log('The department ID is :', departmentId);
                if (departmentId) {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('advertisements.getOffices') }}",
                        data: {
                            department_id: departmentId
                        },
                        success: function(response) {
                            console.log('The response is :', response);
                            $('#office').empty();
                            $('#office').append('<option></option>');
                            $.each(response, function(key, value) {
                                var isSelected = (value.id == userOfficeId) ?
                                    'selected' : '';
                                $('#office').append('<option value="' + value.id +
                                    '" ' + isSelected + '>' + value.ddo_name +
                                    '</option>');

                                // $('#office').append('<option value="' + value.id +
                                //     '">' + value.name + '</option>');
                            });
                        }
                    });
                }
            });
            // If department is already selected (like from logged-in user), trigger change to load offices
            var selectedDepartment = $('#department').val();
            if (selectedDepartment) {
                $('#department').trigger('change');
            }
        });
    </script>

    {{-- JavaScript for INF Number --}}
    <script>
        document.getElementById('assignInfButton').addEventListener('click', async () => {
            try {
                const response = await fetch('{{ route('advertisements.generateINF') }}');
                if (response.ok) {
                    const data = await response.json();
                    document.getElementById('inf_number').value = data.inf_number;
                } else {
                    console.error('Failed to fetch INF number');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>

    {{-- for urdu size input --}}
    <script>
        const urduInput = document.getElementById('urdu_size_input');

        // Space ko "*" me convert karo
        urduInput.addEventListener('keydown', function(e) {
            if (e.key === " ") {
                e.preventDefault(); // space block
                insertAtCursor(this, "*"); // space ki jagah "*" daal do
            }
        });

        urduInput.addEventListener('input', function() {
            let input = this.value;

            // Remove invalid characters (only digits, . and *)
            input = input.replace(/[^0-9.*]/g, '');
            this.value = input;

            let resultBox = document.getElementById('urdu_size_result');
            let hiddenInput = document.getElementById('urdu_size_result_input');

            if (input.includes('*')) {
                let parts = input.split('*');

                if (parts.length === 2 && parts[0] && parts[1]) {
                    let a = parseFloat(parts[0]);
                    let b = parseFloat(parts[1]);

                    if (!isNaN(a) && !isNaN(b)) {
                        let result = a * b;
                        resultBox.innerText = `= ${result}`;
                        hiddenInput.value = result;
                        return;
                    }
                }
            }

            // Reset if not valid
            resultBox.innerText = '';
            hiddenInput.value = '';
        });

        // Cursor par "*" insert karne ka helper
        function insertAtCursor(field, value) {
            let start = field.selectionStart;
            let end = field.selectionEnd;
            field.value = field.value.substring(0, start) + value + field.value.substring(end);
            field.setSelectionRange(start + value.length, start + value.length);
        }
    </script>

    {{-- for english size input --}}
    <script>
        const engInput = document.getElementById('eng_size_input');

        // Space ko "*" me convert karo
        engInput.addEventListener('keydown', function(e) {
            if (e.key === " ") {
                e.preventDefault(); // space block
                insertAtCursor(this, "*"); // space ki jagah "*" daal do
            }
        });

        engInput.addEventListener('input', function() {
            let input = this.value;

            // Remove invalid characters (only digits, . and *)
            input = input.replace(/[^0-9.*]/g, '');
            this.value = input;

            let resultBox = document.getElementById('eng_size_result');
            let hiddenInput = document.getElementById('eng_size_result_input');

            if (input.includes('*')) {
                let parts = input.split('*');

                if (parts.length === 2 && parts[0] && parts[1]) {
                    let a = parseFloat(parts[0]);
                    let b = parseFloat(parts[1]);

                    if (!isNaN(a) && !isNaN(b)) {
                        let result = a * b;
                        resultBox.innerText = `= ${result}`;
                        hiddenInput.value = result;
                        return;
                    }
                }
            }

            // Reset if not valid
            resultBox.innerText = '';
            hiddenInput.value = '';
        });

        // Cursor par "*" insert karne ka helper
        function insertAtCursor(field, value) {
            let start = field.selectionStart;
            let end = field.selectionEnd;
            field.value = field.value.substring(0, start) + value + field.value.substring(end);
            field.setSelectionRange(start + value.length, start + value.length);
        }
    </script>

    {{-- Show/Hide ADP Code and Project Name fields based on Source of Fund selection --}}
    <script>
        $(document).ready(function() {
            // Function to toggle visibility of ADP fields
            function toggleAdpFields() {
                const sourceOfFund = $('#SouceOfFundId').val();

                if (sourceOfFund === 'ADP') {
                    // Show ADP fields
                    $('#adpCodeField').show();
                    $('#projectNameField').show();

                    // Make fields required when shown
                    $('#adp_code').prop('required', true);
                    $('#project_name').prop('required', true);
                } else {
                    // Hide ADP fields
                    $('#adpCodeField').hide();
                    $('#projectNameField').hide();

                    // Remove required attribute when hidden
                    $('#adp_code').prop('required', false);
                    $('#project_name').prop('required', false);

                    // Clear values when hidden (optional)
                    $('#adp_code').val('');
                    $('#project_name').val('');
                }
            }

            // Initial check on page load
            toggleAdpFields();

            // Bind change event
            $('#SouceOfFundId').on('change', function() {
                toggleAdpFields();
            });
        });
    </script>
@endpush

@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        {{-- Ad Form --}}
        <div class="col-xxl">
            <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
                {{-- Title (Header) --}}
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                        <h5 class="h5-reset-margin">Edit Draft Advertisement</h5>
                    </div>
                </div>
                {{-- Body --}}
                <form method="POST" action="{{ route('advertisements.draft.update', $advertisement->id) }}"
                    enctype="multipart/form-data" class="card-body" style="padding: 0;">
                    @csrf

                    {{-- Hidden fields --}}
                    <input type="hidden" name="new_status" value="{{ $new_status }}">

                    {{-- Data --}}
                    <div class="form-padding">
                        {{-- Memo No., Memo Date & Publication Date --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label-x" for="memo-no">Memo No.</label>
                                <input type="text" name="memo_number" id="memo-no" class="form-control"
                                    value="{{ $advertisement->memo_number }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-x" for="memo-date">Memo Date</label>
                                <input type="date" name="memo_date" id="memo-date" class="form-control"
                                    value="{{ \Carbon\Carbon::parse($advertisement->memo_date)->toFormattedDateString() }}" />
                            </div>
                        </div>

                        {{-- Department & Office --}}
                        <div class="row g-3 mb-3">
                            {{-- Department --}}
                            <div class="col-md-6">
                                <label class="form-label-x" for="department">Departments</label>
                                <input type="text" id="department" class="form-control"
                                    value="{{ old('department_id', $advertisement->department->name) }}" disabled />
                            </div>

                            {{-- Office --}}
                            <div class="col-md-6">
                                <label class="form-label-x" for="office">Office</label>
                                <input type="text" id="office" class="form-control"
                                    value="{{ old('office_id', optional($advertisement->office)->name) ?? '' }}" disabled />
                            </div>
                        </div>

                        {{-- Estimated Cost & Ad Category --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label-x" for="estimated-cost">Estimated Cost</label>
                                <select id="estimated-cost" name="ad_worth_id" class="select2 form-select"
                                    data-allow-clear="true" @if (!$user->hasRole('Superintendent'))  @endif>
                                    <option value=""
                                        {{ old('ad_worth_id', $advertisement->ad_worth_id) ? '' : 'selected' }}>Select
                                        Estimated Cost</option>
                                    @foreach ($adWorths as $param)
                                        <option value="{{ $param->id }}"
                                            {{ old('ad_worth_id', $advertisement->ad_worth_id) == $param->id ? 'selected' : '' }}>
                                            {{ $param->range }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Ad Category --}}
                            <div class="col-md-6">
                                <label class="form-label-x" for="classified_ad_type_id">Classified Ad Type</label>
                                <select id="classified_ad_type_id" name="classified_ad_type_id" class="select2 form-select"
                                    data-allow-clear="true">
                                    <option>Select Type</option>
                                    @foreach ($classifiedAdTypes as $type)
                                        <option value="{{ $type->id }}" {{-- {{ old('classified_ad_type_id') ?? $advertisement->classified_ad_type_id == $type->id ? 'selected' : '' }}> --}}
                                            {{ old('classified_ad_type_id', $advertisement->classified_ad_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Number of lines (Urdu & English) --}}
                        @can('view lines')
                            <div class="row g-3 mb-3">
                                <h6 class="my-h6 fw-normal mt-4">Number of Lines &lpar;Urdu &amp; English&rpar;</h6>
                                <div class="col-md-4 mt-0">
                                    <label class="form-label-x" for="urdu-lines">Urdu lines</label>
                                    <input type="text" id="urdu-lines" name="urdu_lines" class="form-control"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        value="{{ $advertisement->urdu_lines }}" />
                                </div>
                                <div class="col-md-4 mt-0">
                                    <label class="form-label-x" for="eng-lines">English lines</label>
                                    <input type="text" id="english-lines" name="english_lines" class="form-control"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        value="{{ $advertisement->english_lines }}" />
                                </div>
                                <div class="col-md-4 mt-0">
                                    <label class="form-label-x" for="pub-date">Publish on or Before</label>
                                    <input type="text" name="publish_on_or_before" id="pub-date" class="form-control"
                                        value="{{ \Carbon\Carbon::parse($advertisement->publish_on_or_before)->toFormattedDateString() }}" />
                                </div>
                            </div>
                        @endcan
                    </div>

                    {{-- Buttons --}}
                    <div class="buttons-div flex">
                        {{-- Submit Ad --}}
                        <button type="submit" name="action" value="submit-ad"
                            class="custom-primary-button">Submit</button>

                        {{-- Update Ad --}}
                        <button type="submit" name="action" value="update-draft"
                            class="custom-secondary-button">Update</button>
                    </div>
                </form>
            </div>
        </div>
        {{-- ! / Ad Form --}}
    </div>
    {{-- ! / Page Content --}}
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-file-uploader"></script>
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

    {{-- dateformat --}}
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
    {{-- JavaScript for radio button to change the submit functionality --}}
    <script>
        // Get the radio buttons and submits buttons
        const approvedRadio = document.getElementById('approved');
        const inprogressRadio = document.getElementById('inprogress');
        const approveBtn = document.getElementById('approve-btn');
        const forwardBtn = document.getElementById('forward-btn');

        // Functions to toggle submit buttons
        function toggleSubmitButtons() {
            if (approvedRadio.checked) {
                approveBtn.style.display = 'block';
                forwardBtn.style.display = 'none';
            } else {
                approveBtn.style.display = 'none';
                forwardBtn.style.display = 'block';

            }
        }
        // Add event listener to the radio button
        approvedRadio.addEventListener('change', toggleSubmitButtons);
        inprogressRadio.addEventListener('change', toggleSubmitButtons);
        // submit buttons initially
        toggleSubmitButtons();
    </script>

    {{-- Newspapers & Agencies JavaScript --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const radioNewspapers = document.getElementById("radioNewspapers");
            const radioAgencies = document.getElementById("radioAgencies");
            const newspapersDropdown =
                document.getElementById("newspapersDropdown");
            const agenciesDropdown =
                document.getElementById("agenciesDropdown");

            function toggleDropdowns() {
                if (radioNewspapers.checked) {
                    newspapersDropdown.classList.remove("d-none");
                    agenciesDropdown.classList.add("d-none");
                } else {
                    newspapersDropdown.classList.add("d-none");
                    agenciesDropdown.classList.remove("d-none");
                }
            }

            // Listen for changes
            radioNewspapers.addEventListener("change", toggleDropdowns);
            radioAgencies.addEventListener("change", toggleDropdowns);
        });
    </script>

    {{-- for urdu size input --}}
    <script>
        document.getElementById('urdu_size_input').addEventListener('input', function() {
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
    </script>

    {{-- for english size input --}}
    <script>
        document.getElementById('eng_size_input').addEventListener('input', function() {
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
    </script>

    {{-- for validation of newspaper selections and on runtime change newspaper limits when estimated cost change --}}
    <script>
        $(document).ready(function() {
            let maxNewspapers = {{ $max_newspapers }};
            let isUnlimited = {{ $is_unlimited ? 'true' : 'false' }};

            const newspapersSelect = $('#newspapers');
            newspapersSelect.select2();

            function updateLabel() {
                const label = document.getElementById("newspapersLabel");
                if (label) {
                    label.innerHTML = isUnlimited ?
                        "Select Newspapers (No Limit)" :
                        `Select Newspapers (Max: ${maxNewspapers})`;
                }
            }

            function trimSelectionIfOverLimit() {
                const selected = newspapersSelect.val() || [];
                if (!isUnlimited && selected.length > maxNewspapers) {
                    const trimmed = selected.slice(0, maxNewspapers);
                    newspapersSelect.val(trimmed).trigger('change');
                    alert(`You can only select up to ${maxNewspapers} newspaper(s).`);
                }
            }

            // Listen to dropdown change
            $('#estimated-cost').on('change', function() {
                const selectedId = $(this).val();
                if (selectedId) {
                    $.ajax({
                        url: '/ad-worth-limit/' + selectedId,
                        type: 'GET',
                        success: function(response) {
                            maxNewspapers = response.limit;
                            isUnlimited = response.is_unlimited;
                            updateLabel();
                            trimSelectionIfOverLimit();
                        },
                        error: function() {
                            console.error('Failed to fetch ad worth limit');
                        }
                    });
                }
            });

            // When user manually selects newspapers
            newspapersSelect.on('change', function() {
                trimSelectionIfOverLimit();
            });

            // Initial
            updateLabel();
        });
    </script>
@endpush

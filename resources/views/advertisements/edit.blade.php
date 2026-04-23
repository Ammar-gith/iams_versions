@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        {{-- Ad Form --}}

        <div class="card mb-4" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">

            {{-- Title (Header) --}}
            <div class="card-header-table">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5-reset-margin">Edit Advertisement</h5>
                </div>
                @can('view inf number')
                    @if (!empty($advertisement->inf_number))
                        <div class="inf-badge">
                            <span class="icon"><i class='bx bxs-purchase-tag'></i></span>
                            <span class="label">INF No.</span>
                            <span class="number">{{ $advertisement->inf_number }}</span>
                        </div>
                    @endif
                @endcan
            </div>

            {{-- Body --}}
            <form id="adForm" method="POST" action="{{ route('advertisements.update', $advertisement->id) }}"
                enctype="multipart/form-data" class="card-body" style="padding: 0;">
                @csrf

                {{-- Data --}}
                <div class="form-padding">
                    {{-- Memo No., Memo Date & Publication Date --}}
                    <div class="row g-3 mb-3">
                        {{-- @if ($showMemoFields) --}}
                        <div class="col-md-6">
                            <label class="form-label-x" for="memo-no">Memo No.</label>
                            <input type="text" id="memo-no" class="form-control"
                                value="{{ $advertisement->memo_number }}"
                                @if (!$user->hasRole(['Client Office', 'Superintendent'])) disabled @endif />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-x" for="memo-date">Memo Date</label>
                            <input type="text" id="memo-date" class="form-control"
                                value="{{ $advertisement->memo_date }}" @if (!$user->hasRole(['Client Office', 'Superintendent'])) disabled @endif />
                        </div>
                        {{-- @endif --}}
                    </div>

                    {{-- Department & Office --}}
                    <div class="row g-3 mb-3">
                        {{-- Department --}}
                        <div class="col-md-6">
                            <label class="form-label-x" for="department">Departments</label>
                            <input type="text" id="department" class="form-control"
                                value="{{ old('department_id', $advertisement->department->name) }}"
                                @if (!$user->hasRole(['Client Office', 'Superintendent'])) disabled @endif>
                        </div>

                        {{-- Office --}}
                        <div class="col-md-6">
                            <label class="form-label-x" for="office">Office</label>
                            <input type="text" id="office" class="form-control"
                                value="{{ old('office_id', optional($advertisement->office)->ddo_name) ?? '' }}"
                                @if (!$user->hasRole(['Client Office', 'Superintendent'])) disabled @endif>
                        </div>
                    </div>

                    {{-- Estimated Cost & Ad Category --}}
                    <div class="row g-3 mb-3">

                        {{-- Estimated Cost --}}
                        <div class="col-md-6">
                            <label class="form-label-x" for="estimated-cost">Estimated Cost</label>
                            <select id="estimated-cost" name="ad_worth_id" class="select2 form-select"
                                data-allow-clear="true" @if (!$user->hasRole(['Superintendent', 'Client Office'])) disabled @endif>
                                <option value="" disabled
                                    {{ old('ad_worth_id', $advertisement->ad_worth_id) ? '' : 'selected' }}>Select
                                    Estimated Cost</option>
                                @foreach ($ad_worth_parameters as $param)
                                    <option value="{{ $param->id }}"
                                        {{ old('ad_worth_id', $advertisement->ad_worth_id) == $param->id ? 'selected' : '' }}>
                                        {{ $param->range }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="ad_worth_id" value="{{ $advertisement->ad_worth_id }}">
                        </div>

                        {{-- Ad Category --}}
                        <div class="col-md-6">
                            <label class="form-label-x" for="classified_ad_type_id">Classified Ad Type</label>
                            <select id="classified_ad_type_id" name="classified_ad_type_id" class="select2 form-select"
                                data-allow-clear="true" @if (!$user->hasRole(['Client Office', 'Superintendent'])) disabled @endif>
                                <option>Select Type</option>
                                @foreach ($classifiedAdTypes as $type)
                                    <option value="{{ $type->id }}"
                                        {{ old('classified_ad_type_id') ?? $advertisement->classified_ad_type_id == $type->id ? 'selected' : '' }}>
                                        {{ $type->type }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="classified_ad_type_id"
                                value="{{ $advertisement->classified_ad_type_id }}">
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
                                    value="{{ $advertisement->urdu_lines }}"
                                    @if (!$user->hasRole(['Superintendent', 'Client Office'])) readonly @endif />
                                {{-- <input type="hidden" id="urdu-lines" name="urdu_lines" class="form-control"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    value="{{ $advertisement->urdu_lines }}" /> --}}
                            </div>
                            <div class="col-md-4 mt-0">
                                <label class="form-label-x" for="eng-lines">English lines</label>
                                <input type="text" id="eng-lines" name="english_lines" class="form-control"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    value="{{ $advertisement->english_lines }}"
                                    @if (!$user->hasRole(['Superintendent', 'Client Office'])) readonly @endif />
                                {{-- <input type="hidden" id="eng-lines" name="english_lines" class="form-control"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    value="{{ $advertisement->english_lines }}" /> --}}
                            </div>
                            <div class="col-md-4 mt-0">
                                <label class="form-label-x" for="pub-date">Publish on or Before</label>
                                <input type="date" id="pub-date" class="form-control"
                                    value="{{ $advertisement->publish_on_or_before }}"
                                    @if (!auth()->user()->hasRole(['Client Office', 'Superintendent'])) disabled @endif />
                            </div>
                        </div>
                    @endcan

                    {{-- Ad Size (Urdu & English) --}}
                    @can('view sizes')
                        <div class="row g-3 mb-3">
                            <h6 class="my-h6 fw-normal mt-4">Advertisement Size &lpar;Urdu &amp; English &rpar;</h6>
                            <div class="col-md-4 mt-0 position-relative">
                                <label class="form-label-x" for="urdu_size_input">Urdu size</label>
                                <input type="text" id="urdu_size_input" name="urdu_space" class="form-control"
                                    placeholder="e.g. 2*3"
                                    value="{{ str_replace('*', '×', $advertisement->urdu_space) }}{{ !empty($advertisement->urdu_size) ? ' = ' . $advertisement->urdu_size : '' }}"
                                    required @if (!$user->hasRole('Superintendent')) disabled @endif>
                                @if ($user->hasRole(['Deputy Director', 'Director General', 'Secretary']))
                                    <input type="hidden" id="urdu_space_input" name="urdu_space"
                                        value="{{ $advertisement->urdu_space }}">
                                @endif

                                <input type="hidden" id="urdu_size_result_input" name="urdu_size"
                                    value="{{ $advertisement->urdu_size }}">

                                <span id="urdu_size_result" class="position-absolute fw-bold"
                                    style="top: 70%; right: 230px; transform: translateY(-50%); pointer-events: none; color:#0e1714cb;">
                                </span>
                            </div>

                            <div class="col-md-4 mt-0 position-relative">
                                <label class="form-label-x" for="eng_size_input">English size</label>
                                <input type="text" id="eng_size_input" name="english_space" class="form-control"
                                    placeholder="e.g. 2*3"
                                    value="{{ str_replace('*', '×', $advertisement->english_space) }}{{ !empty($advertisement->english_size) ? ' = ' . $advertisement->english_size : '' }}"
                                    required @if (!$user->hasRole(['Superintendent', 'Client Office'])) disabled @endif>
                                @if ($user->hasRole(['Deputy Director', 'Director General', 'Secretary']))
                                    <input type="hidden" id="english_space_input" name="english_space"
                                        value="{{ $advertisement->english_space }}">
                                @endif
                                <input type="hidden" id="eng_size_result_input" name="english_size"
                                    value="{{ $advertisement->english_size }}">

                                <span id="eng_size_result" class="position-absolute fw-bold"
                                    style="top: 70%; right: 230px; transform: translateY(-50%); pointer-events: none; color:#0e1714cb;">
                                </span>
                            </div>

                            {{-- Position/Placement --}}
                            <div class="col-md-4 mt-0">
                                <label class="form-label-x" for="newsposrate_id">Position/Placement</label>
                                <select id="newsposrate_id" name="news_pos_rate_id" class="select2 form-select"
                                    data-allow-clear="true" @if (!$user->hasRole(['Client Office', 'Superintendent'])) disabled @endif>
                                    <option value="">Select Position</option>
                                    @foreach ($news_pos_rates as $news_pos_rate)
                                        <option value="{{ $news_pos_rate->id }}"
                                            data-placement="{{ $news_pos_rate->rates }}"
                                            @if (old('news_pos_rate_id', $advertisement->news_pos_rate_id ?? 3) == $news_pos_rate->id) selected @endif>
                                            {{ $news_pos_rate->position }} ({{ (int) $news_pos_rate->rates }}%)
                                        </option>
                                    @endforeach
                                </select>
                                {{-- Only needed if select is disabled --}}
                                @if (!$user->hasRole(['Client Office', 'Superintendent']))
                                    <input type="hidden" name="news_pos_rate_id"
                                        value="{{ $advertisement->news_pos_rate_id }}">
                                @endif
                            </div>

                        </div>
                    @endcan

                    {{-- Newspaper/Adv. Agency & Placement --}}
                    @can('add media')
                        <div class="row g-3 mb-3">
                            <h6 class="my-h6 fw-normal mt-4">Select Media &mdash; Newspaper&lpar;s&rpar; or Advertising
                                Agency</h6>
                            {{-- Newspapers Dropdown (Shown by Default)  --}}
                            <div id="newspapersDropdown" class="col-md-6">
                                <label id="newspapersLabel" for="newspapers" class="form-label-x">
                                    Select Newspapers
                                    @if (!$is_unlimited)
                                        (Max: {{ $max_newspapers }})
                                    @else
                                        (No Limit)
                                    @endif
                                </label>

                                <select id="newspapers" name="newspaper_id[]" class="select2 form-select"
                                    data-allow-clear="true" multiple>
                                    <option value="" disabled>Select Value</option>

                                    @foreach ($newspapers as $newspaper)
                                        <option value="{{ $newspaper->id }}" data-rate="{{ $newspaper->rate }}"
                                            data-language="{{ $newspaper->language_id }}"
                                            data-kpra="{{ $newspaper->register_with_kapra }}"
                                            {{ in_array($newspaper->id, old('newspaper_id', $selected_newspapers ?? [])) ? 'selected' : '' }}>
                                            {{ $newspaper->title }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- Error Placeholder --}}
                                <div id="newspaperError" class="text-danger mt-2"></div>
                                @error('newspaper_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    @if (!$is_unlimited)
                                        You must select newspapers according to estimated cost.
                                    @else
                                        You can select as many newspapers as needed.
                                    @endif
                                </small>
                            </div>

                            {{-- Advertising Agencies --}}
                            <div class="col-md-6">
                                <label class="form-label-x" for="adv_agencyId">Select Adv. Agency</label>
                                <select id="adv_agencyId" name="adv_agency_id" class="select2 form-select"
                                    data-allow-clear="true">
                                    <option value="">Select Value</option>
                                    @foreach ($adv_agencies as $adv_agency)
                                        <option value="{{ $adv_agency->id }}"
                                            {{ old('adv_agency_id') ?? $advertisement->adv_agency_id == $adv_agency->id ? 'selected' : '' }}>
                                            {{ $adv_agency->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endcan

                    {{-- Remarks --}}
                    {{-- <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label-x" for="remarks">Remarks (if any)</label>
                                <textarea id="remarks" name="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
                            </div>
                        </div> --}}

                    {{--  status --}}
                    @php
                        $user = auth()->user();
                    @endphp
                    @if ($user->hasRole('Deputy Director'))
                        <div class="row mb-3   ">
                            <label class="form-label-x" for="name">Status</label>
                            <div class="col-sm-3">
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="approved" name="status_id" value="approved"
                                        class="form-check-input ">
                                    <label class="form-check-label " for="approved">
                                        Approved
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="inprogress" name="status_id" value="inprogress"
                                        class="form-check-input " checked>
                                    <label class="form-check-label " for="inprogress">
                                        Inprogress / DG Approval
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($user->hasRole('Director General'))
                        <div class="row mb-3   ">
                            <label class="form-label-x" for="name">Status</label>
                            <div class="col-sm-3">
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="approved" name="status_id" value="approved"
                                        class="form-check-input ">
                                    <label class="form-check-label " for="approved">
                                        Approved
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="inprogress" name="status_id" value="inprogress"
                                        class="form-check-input " checked>
                                    <label class="form-check-label " for="inprogress">
                                        Inprogress / Sec Approval
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{--  billings and dues --}}
                @can('view billings and dues')
                    <div class="financials-div">
                        <div class="row g-3">
                            <div class="col-md-6 custom-alignment">
                                <div class="some flex">
                                    <label class="form-label-x l-green" for="current-bill">Current Bill</label>
                                    <div id="current_bill_display" data-value="{{ $advertisement->current_bill }}"
                                        class="fw-bold text-center"
                                        style="font-size: 16px; border: 1px solid white; background-color:rgba(255, 255, 255, 0.916); color:#246a44; padding: 5px 10px; border-radius: 6px;">
                                        {{ $advertisement->current_bill ?? 0 }}
                                    </div>

                                    <!-- Hidden input that will be submitted -->
                                    <input type="hidden" id="current_bill" name="current_bill"
                                        value="{{ $advertisement->current_bill ?? 0 }}">
                                </div>
                            </div>
                            <div class="col-md-6 custom-alignment">
                                <div class="some flex">
                                    <label class="form-label-x l-red" for="previous-dues">Previous Dues</label>
                                    <input type="text" readonly />
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan


                <div id="bill_result"></div>

                {{-- Buttons --}}
                <div class="buttons-div flex">
                    {{-- Draft Ad --}}
                    @can('view draft button')
                        <button type="submit" name="action" value="save-draft" class="custom-secondary-button">Save as
                            Draft</button>
                    @endcan

                    {{-- Forward Ad --}}
                    @can('view forward button')
                        <button type="submit" name="action" id="forward-btn" value="forward"
                            class="custom-primary-button">Forward Ad</button>
                    @endcan

                    {{-- approve Ad --}}
                    @can('view approve button')
                        <button type="submit" name="action" value="approve" id="approve-btn"
                            class="custom-primary-button">Approve Ad</button>
                    @endcan

                    {{-- Reject Ad --}}
                    @can('view reject button')
                        <button type="button" name="action" value="reject" class="custom-secondary-button"
                            data-bs-toggle="modal" data-bs-target="#editUser">Reject</button>
                    @endcan

                    {{-- Update Ad (Client and Diary) --}}
                    @hasanyrole('Diary Dispatch')
                        <button type="submit" class="custom-primary-button">Update</button>
                    @endhasanyrole

                    {{-- =================================================== --}}

                    {{-- Department User Actions --}}
                    @if ($is_department_user && $advertisement->status->title == 'Pending Department Approval')
                        <button type="submit" name="action" value="department_approve"
                            class="custom-primary-button me-2 rounded-pill">
                            <i class="bx bx-check"></i> Approve & Forward to IPR
                        </button>

                        <button type="button" class="btn btn-warning me-2 rounded-pill" data-bs-toggle="modal"
                            data-bs-target="#sendBackModal">
                            <i class="bx bx-undo"></i> Send Back to Office
                        </button>
                    @endif


                    {{-- Office User Resubmission --}}
                    @if ($is_office_user && $advertisement->status->title == 'Sent Back to Office')
                        <button type="submit" name="action" value="resubmit_to_department"
                            class="custom-primary-button rounded-pill">
                            <i class="bx bx-send"></i> Resubmit to Department
                        </button>
                    @endif


                    {{-- IPR Users Send Back Options
                    @if (in_array(auth()->user()->roles->first()->name, ['Superintendent', 'Deputy Director', 'Director General']) && in_array($advertisement->status->title, ['New', 'Forwarded', 'Approved']))
                        <div class="card mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">Send Back Options</h5>
                            </div>
                            <div class="card-body">
                                <button type="button" class="btn btn-warning rounded-pill" data-bs-toggle="modal"
                                    data-bs-target="#iprSendBackModal">
                                    <i class="bx bx-undo"></i> Send Back for Corrections
                                </button>
                            </div>
                        </div>
                    @endif --}}


                </div>
            </form>
        </div>

        {{-- =================================================== --}}


        {{-- Modals for Department Actions --}}
        @if ($is_department_user)
            <!-- Send Back Modal -->
            <div class="modal fade" id="sendBackModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('advertisements.update', $advertisement->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Send Back to Office</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="send_back_reason" class="form-label">Reason for Sending Back</label>
                                    <textarea class="form-control" id="send_back_reason" name="remarks" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="action" value="department_send_back"
                                    class="btn btn-warning">Send Back</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- IPR Send Back Modal --}}
        {{-- <div class="modal fade" id="iprSendBackModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('advertisements.update', $advertisement->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Send Back for Corrections</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="send_back_reason" class="form-label">Reason for Sending Back</label>
                                <textarea class="form-control" id="send_back_reason" name="send_back_reason" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="send_back_to_type" class="form-label">Send Back To</label>
                                <select class="form-control" id="send_back_to_type" name="send_back_to_type" required>
                                    <option value="">Select</option>
                                    <option value="department">Department User</option>
                                    <option value="office">Office User</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="action" value="send_back_to_department"
                                class="btn btn-warning">Send Back</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

        {{-- =================================================== --}}


    </div>
    {{-- ! / Ad Form --}}
    </div>
    {{-- ! / Page Content --}}

    <!-- Edit ads rejection reason Modal -->
    <div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content p-3 p-md-5">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3>Ads Rejection Reason</h3>
                        <p>Select Ads rejection reason form below.</p>
                    </div>
                    <form action="{{ route('advertisements.rejectionReason', $advertisement->id) }}" class="row g-3"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="col-md-12">
                            @foreach ($ad_rejection_reasons as $ad_rejection_reason)
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="ad_rejection_reasons_id[]" class="form-check-input"
                                        value="{{ $ad_rejection_reason->id }}"
                                        {{ in_array($ad_rejection_reason->id, old('ad_rejection_reasons_id', $selected_reasons ?? [])) ? 'checked' : '' }}>
                                    {{ $ad_rejection_reason->description }}
                                </div>
                            @endforeach
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="">Give remarks if any</label>
                            <textarea type="text" id="" name="remarks" class="form-control" placeholder="Write Remarks"></textarea>
                        </div>


                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                aria-label="Close">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Edit rejection reason Modal -->
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
    {{-- dateformat using flatpickr --}}
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
    <script>
        document.getElementById("adForm").addEventListener("submit", function(e) {
            let selected = $("#newspapers").val() || []; // Select2 selected values
            let maxLimit = @json($max_newspapers); // dynamic limit from backend
            let isUnlimited = @json($is_unlimited); // if unlimited, no validation

            // Clear old error
            document.getElementById("newspaperError").style.display = "none";
            document.getElementById("newspaperError").innerText = "";

            if (!isUnlimited && selected.length !== maxLimit) {
                e.preventDefault(); // Stop form submission
                document.getElementById("newspaperError").innerText =
                    `You must select exact limit of  newspaper(s) for the selected estimated cost range.`;
                document.getElementById("newspaperError").style.display = "block";
            }
        });
    </script>

    {{-- calculation for generating current bill  --}}
    <script>
        window.addEventListener("load", function() {
            function calculateBill() {
                let urduSize = parseFloat(document.getElementById('urdu_size_result_input').value) || 0;
                let engSize = parseFloat(document.getElementById('eng_size_result_input').value) || 0;

                let newspaperSelect = document.getElementById('newspapers');
                let selectedOptions = Array.from(newspaperSelect.selectedOptions);

                let placementSelect = document.getElementById('newsposrate_id')
                let placementOptions = placementSelect.options[placementSelect.selectedIndex];

                let advAgencySelect = document.getElementById('adv_agencyId'); // your Adv Agency dropdown
                let advAgencySelected = advAgencySelect && advAgencySelect.value !== ""; // true if agency is chosen

                let totalBill = 0;
                // let breakdown = "";

                selectedOptions.forEach(option => {
                    let rate = parseFloat(option.getAttribute("data-rate")) || 0;
                    let languageId = option.getAttribute("data-language");
                    let kpra = option.getAttribute("data-kpra");
                    let kpra_tax = 0.02 // 2% kpra tax charges
                    let placementRates = placementOptions ? parseFloat(placementOptions.getAttribute(
                        'data-placement')) || 0 : 0;

                    // map language_id to real language
                    let language = (languageId == 1) ? "urdu" : "english";

                    let size = (language === "urdu") ? urduSize : engSize;

                    // Placement of the newspapers
                    let billWithPlacement = rate + (rate * placementRates / 100);

                    let rateWtihPlacement = billWithPlacement * size;

                    let currentBill = rateWtihPlacement;

                    // if (kpra === 'Yes') {
                    //     CurrentBill = rateWtihPlacement + (rateWtihPlacement *
                    //         kpra_tax); // Add 2% kpra tax if register
                    // } else {
                    //     CurrentBill = rateWtihPlacement;
                    // }

                    // Apply KPRA tax only if NO agency is selected
                    if (!advAgencySelected && kpra === 'Yes') {
                        CurrentBill = rateWtihPlacement + (rateWtihPlacement * kpra_tax);
                    } else {
                        CurrentBill = rateWtihPlacement;
                    }

                    totalBill += CurrentBill;

                    //         breakdown += `
                //     <p>
                //         Newspaper: ${option.text} <br>
                //         Language: ${language} <br>
                //         Placement: ${placementRates} <br>
                //         bill with Placement: ${billWithPlacement} <br>
                //         Rate: ${rate} × Size: ${size} = <b>${rateWtihPlacement}</b>
                //         Rate with Placement: ${rateWtihPlacement} <br>
                //         KPRA : ${kpra} / ${kpra_tax} <br>
                //         currentBill : ${CurrentBill} <br>

                //     </p>
                //     <hr>
                // `;
                });

                // Update display
                document.getElementById("current_bill_display").innerText = totalBill.toFixed(2);

                // Update hidden field (for DB save)
                document.getElementById("current_bill").value = totalBill.toFixed(2);

                //     document.getElementById('bill_result').innerHTML = `
            //  <h4>Bill Preview</h4>
            //  ${breakdown}

            // `;
            }

            // Urdu size
            document.getElementById('urdu_size_input').addEventListener('input', function() {
                let result = 0;
                if (this.value.includes('*')) {
                    let parts = this.value.split('*');
                    if (parts.length === 2) {
                        result = (parseFloat(parts[0]) || 0) * (parseFloat(parts[1]) || 0);
                    }
                } else {
                    result = parseFloat(this.value) || 0;
                }
                document.getElementById('urdu_size_result_input').value = result;
                calculateBill();
            });

            // English size
            document.getElementById('eng_size_input').addEventListener('input', function() {
                let result = 0;
                if (this.value.includes('*')) {
                    let parts = this.value.split('*');
                    if (parts.length === 2) {
                        result = (parseFloat(parts[0]) || 0) * (parseFloat(parts[1]) || 0);
                    }
                } else {
                    result = parseFloat(this.value) || 0;
                }
                document.getElementById('eng_size_result_input').value = result;
                calculateBill();
            });

            // 🔹 For normal select
            document.getElementById('newspapers').addEventListener('change', calculateBill);

            // 🔹 For Select2 specifically
            $('#newspapers').on('select2:select select2:unselect', function() {
                calculateBill();
            });

            // Always recalc when any input changes
            ["change", "input"].forEach(evt => {
                document.getElementById('newspapers').addEventListener(evt, calculateBill);
                document.getElementById('urdu_size_result_input').addEventListener(evt, calculateBill);
                document.getElementById('eng_size_result_input').addEventListener(evt, calculateBill);
            });


            // Placement dropdown (Select2 needs jQuery event binding)
            $(document).ready(function() {
                $('#newsposrate_id').on('change.select2', function() {
                    calculateBill();
                });

                $('#adv_agencyId').on('change.select2', function() {
                    calculateBill();
                });
            });

            // Run once initially
            document.addEventListener("DOMContentLoaded", calculateBill);
        });
    </script>
@endpush

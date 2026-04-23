@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">

            {{-- Title (Header) --}}
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="h5-reset-margin">Advertisement Details</h5>
                </div>
                <div>
                    <span
                        class="badge rounded-pill
                        @if ($status == 'New') bg-success
                        @elseif($status == 'In progress') bg-warning
                        @elseif($status == 'Approved') bg-success
                        @elseif($status == 'Published') bg-published
                        @elseif($status == 'Rejected') bg-danger
                        @elseif($status == 'Draft') bg-warning @endif">
                        {{ $status ?? 'N/A' }}
                    </span>
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
            @php
                $user = Auth::user();
            @endphp

            {{-- Body --}}
            <div class="table-responsive text-nowrap"></div>
            <table class="table w-100">
                <tbody>
                    <tr>
                        <td class="fw-bold">Memo Number:</td>
                        <td class="text-start">{{ $advertisement->memo_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Memo Date:</td>
                        <td class="text-start">
                            {{ date('d M Y', strtotime($advertisement->memo_date)) ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Publish On Or Before:</td>
                        <td class="text-start">
                            {{ date('d M Y', strtotime($advertisement->publish_on_or_before)) ?? 'N/A' }}</td>
                    </tr>
                    @unless ($user->hasRole('Client Office'))
                        <tr>
                            <td class="fw-bold">Urdu Size:</td>
                            <td class="text-start">{{ str_replace('*', '×', $advertisement->urdu_space) }} =
                                {{ $advertisement->urdu_size ?? 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">English Size:</td>
                            <td class="text-start">{{ str_replace('*', '×', $advertisement->english_space) }} =
                                {{ $advertisement->english_size ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Urdu Lines:</td>
                            <td class="text-start">{{ $advertisement->urdu_lines ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">English Lines:</td>
                            <td class="text-start">{{ $advertisement->english_lines ?? 'N/A' }}</td>
                        </tr>
                    @endunless
                    <tr>
                        <td class="fw-bold">Department:</td>
                        <td class="text-start">{{ $departmentName ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Office:</td>
                        <td class="text-start">{{ $officeName ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">AD Category:</td>
                        <td class="text-start">{{ $classifiedAdType ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Ad Worth Parameters:</td>
                        <td class="text-start">{{ $adWorthparameters ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Newspaper Positions & Rates:</td>
                        <td class="text-start">{{ $newsposrate->position ?? 'N/A' }} /
                            {{ $newsposrate->rates ?? 'N/A' }}</td>
                    </tr>
                    {{-- <tr>
                            <td class="fw-bold">Newspapers:</td>
                            <td class="text-start">{{ implode(', ', $newspaperNames) ?? 'N/A' }}</td>
                        </tr> --}}

                    @if (!$user->hasRole(['Media', 'Client Office']))
                        <tr>
                            <td class="fw-bold">Suptd Newspapers Log:</td>
                            <td class="text-start">{{ implode(', ', $suptdNewspaperNames) ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">DD Newspapers Log:</td>
                            <td class="text-start">{{ implode(', ', $ddNewspaperNames) ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">DG Newspaper Log:</td>
                            <td class="text-start">{{ implode(', ', $dgNewspaperNames) ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Sec Newspaper Log:</td>
                            <td class="text-start">{{ implode(', ', $secNewspaperNames) ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Adv Agency:</td>
                            <td class="text-start">{{ $advAgency ?? 'N/A' }}</td>
                        </tr>
                        {{-- <tr>
                            <td class="fw-bold">Ad Type:</td>
                            <td class="text-start">{{ $advertisement->ad_type ?? 'N/A' }}</td>
                        </tr> --}}
                        <tr>
                            <td class="fw-bold">Forwarded By:</td>
                            <td class="text-start">{{ $forwardedBy ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Forwarded To:</td>
                            <td class="text-start">{{ $forwardedTo ?? 'N/A' }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="fw-bold">Ad Created By:</td>
                        <td class="text-start">{{ $userName ?? 'N/A' }}</td>
                    </tr>
                    {{-- Ad rejection reasons --}}
                    <tr>
                        <td class="fw-bold">Ad Rejection Reasons:</td>

                        <td class="text-start">{{ $advertisement->ad_rejection_reasons_id[1] ?? 'N/A' }}</td>
                    </tr>
                    {{-- remarks --}}
                    <tr>
                        <td class="fw-bold">Remarks:</td>
                        <td class="text-start text-danger">{{ $advertisement->remarks ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Covering Letter File:</td>
                        <td class="text-start">
                            @if ($covering_letter_files->isNotEmpty())
                                @foreach ($covering_letter_files as $covering_letter_file)
                                    @php
                                        // Default icon agar PDF hai
                                        if ($covering_letter_file->mime_type === 'application/pdf') {
                                            $thumbUrl = asset('assets/img/pdf/pdficon.jpg');
                                        } else {
                                            // Images ka thumbnail generate hoga
                                            $thumbUrl = $covering_letter_file->getUrl('thumb');
                                        }
                                    @endphp

                                    <img src="{{ $thumbUrl }}" alt="Covering Letter" class="img-fluid w-10 h-10 "
                                        style="max-width:30px;cursor:pointer;"
                                        onclick="openFileModal({{ $advertisement->id }}, {{ $covering_letter_file->id }})">
                                @endforeach
                            @else
                                <p>No covering letter uploaded.</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Urdu Ad File:</td>
                        <td class="text-start">
                            @if ($urdu_ad_files->isNotEmpty())
                                @foreach ($urdu_ad_files as $urdu_ad_file)
                                    @php
                                        // Default icon agar PDF hai
                                        if ($urdu_ad_file->mime_type === 'application/pdf') {
                                            $thumbUrl = asset('assets/img/pdf/pdficon.jpg');
                                        } else {
                                            // Images ka thumbnail generate hoga
                                            $thumbUrl = $urdu_ad_file->getUrl('thumb');
                                        }
                                    @endphp
                                    <img src="{{ $thumbUrl }}" alt="Urdu Ad" class="img-fluid"
                                        style="max-width:30px;cursor:pointer;"
                                        onclick="openFileModal({{ $advertisement->id }}, {{ $urdu_ad_file->id }})">
                                @endforeach
                            @else
                                <p>No urdu ad uploaded.</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">English Ad File:</td>
                        <td class="text-start">
                            @if ($english_ad_files->isNotEmpty())
                                @foreach ($english_ad_files as $english_ad_file)
                                    @php
                                        // Default icon agar PDF hai
                                        if ($english_ad_file->mime_type === 'application/pdf') {
                                            $thumbUrl = asset('assets/img/pdf/pdficon.jpg');
                                        } else {
                                            // Images ka thumbnail generate hoga
                                            $thumbUrl = $english_ad_file->getUrl('thumb');
                                        }
                                    @endphp
                                    <img src="{{ $thumbUrl }}" alt="English Ad" class="img-fluid"
                                        style="max-width:30px;cursor:pointer;"
                                        onclick="openFileModal({{ $advertisement->id }}, {{ $english_ad_file->id }})">
                                @endforeach
                            @else
                                <p>No english ad uploaded.</p>
                            @endif
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
    </div>
    {{-- ! / Page Content --}}

    {{-- Modal for show file --}}
    <div class="modal fade" id="fileModal" tabindex="-5" aria-labelledby="fileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileModalLabel">File Details</h5>
                    <button type="button" class="button-x" data-bs-dismiss="modal"><i
                            class="bx bx-x bx-modal-icons"></i></button>
                </div>
                <div class="modal-body justify-content-center" id="fileDetails">
                    <p class="text-center">Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endpush

    @push('scripts')
        <script>
            function openFileModal(advertisementId, imageId) {
                $.ajax({
                    url: `/advertisements/${advertisementId}/file-show/${imageId}`,
                    type: "GET",
                    success: function(response) {

                        let mime = response.mime_type;
                        let originalFile = response.url; // <- correct original file
                        let thumbFile = response.Url; // <- thumb/icon


                        let html = "";

                        // IMAGE
                        if (mime.startsWith("image")) {
                            html = `
                    <div class="text-center">
                        <img src="${originalFile}" class="img-fluid" style="max-height:600px;">
                    </div>
                `;
                        }

                        // PDF
                        else if (mime === "application/pdf") {
                            html = `
                    <div class="text-center">
                        <iframe src="${originalFile}" width="100%" height="600px"></iframe>
                    </div>
                `;
                        }

                        // Other files
                        else {
                            html = `<p class="text-center text-danger">Cannot display this file type.</p>`;
                        }

                        $("#fileModal .modal-body").html(html);

                        let myModal = new bootstrap.Modal(document.getElementById('fileModal'));
                        myModal.show();
                    },
                    error: function() {
                        alert("Something went wrong!");
                    }
                });
            }
        </script>
    @endpush

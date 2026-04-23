@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row custom-paddings">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between align-items-center">
                <h5 class="h5-reset-margin">Cheque Submissions</h5>
                {{-- @if ($billClassifiedAds->isEmpty())
                    <span class="text-muted">No Bills to show</span>
                @endif --}}
                <a href="{{ route('billings.treasury-challans.createOnlineCheque') }}" class="btn custom-primary-button"
                    type="submit">Create</a>

            </div>

            {{-- Get the authenticated logged in user  --}}
            @php
                $user = Auth::User();
            @endphp

            {{-- Show ads if any --}}
            {{-- @if ($billClassifiedAds->isNotEmpty()) --}}
            <div class="table-responsive text-nowrap">
                <table class="table w-100">
                    <thead>
                        <tr>
                            {{-- <th>ID</th> --}}
                            <th>Memo No.</th>
                            {{-- <th>INF No.</th> --}}
                            <th>Office</th>
                            <th>Cheque Number</th>
                            <th>Cheque Date</th>
                            {{-- <th>Newspapers Amount</th> --}}
                            <th>Cheque Amount</th>
                            <th>Status</th>

                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($treasuryChallans as $key => $treasuryChallan)
                            <tr>
                                {{-- <td>{{ $treasuryChallan->id }}</td> <!-- Serial Number --> --}}
                                <td>{{ $treasuryChallan->memo_number }}</td>
                                {{-- <td>{{ implode(', ', $treasuryChallan->inf_number ?? '') }}</td> --}}
                                {{-- <td>
                                    @foreach ($treasuryChallan->inf_number ?? [] as $inf)
                                        {{ $inf }} <br>
                                    @endforeach
                                </td> --}}
                                <td style="width:50%; white-space: normal; word-wrap: break-word;">
                                    {{ \Illuminate\Support\Str::words($treasuryChallan->office->ddo_name ?? '', 500, '...') }}
                                </td>
                                <td>{{ $treasuryChallan->cheque_number ?? '' }}</td>
                                <td>{{ \Carbon\Carbon::parse($treasuryChallan->cheque_date)->toFormattedDateString() }}
                                    {{-- <td>{{ $treasuryChallan->newspapers_amount ?? '-' }}</td> --}}
                                <td>{{ number_format(round($treasuryChallan->total_amount)) ?? '-' }}</td>

                                </td>
                                {{-- <td>{{ $treasuryChallan->status->title }}</td> --}}
                                <td>
                                    @if ($treasuryChallan->status_id == 19)
                                        <span class="badge bg-label-warning rounded-pill">Reciept Pending</span>
                                    @elseif($treasuryChallan->status_id == 18)
                                        <span class="badge bg-label-info rounded-pill">Recevied</span>
                                        @elseif($treasuryChallan->status_id == 10)
                                        <span class="badge bg-label-success rounded-pill">Approved</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- <div class="custom-pagination">
                        {{ $advertisements->links() }}
                    </div> --}}
            </div>
            {{-- @endif --}}
        </div>
    </div>

    {{-- ! / Page Content --}}
@endpush

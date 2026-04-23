@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header col-md-12 d-flex justify-content-between gap-2 align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                    <h5 class="me-3">Newspapers Bills List</h5>
                    @if ($billdetails->isEmpty())
                        <span class="text-muted">No ads to show</span>
                    @endif
                </div>
                @can('view inf number')
                    @if (!empty($inf_number))
                        <div class="inf-badge">
                            <span class="icon"><i class='bx bxs-purchase-tag'></i></span>
                            <span class="label">INF No.</span>
                            <span class="number">{{ $inf_number }}</span>
                        </div>
                    @endif
                @endcan
            </div>

            {{-- Get the authenticated logged in user --}}
            @php
                $user = Auth::User();
            @endphp

            {{-- Show ads if any --}}
            @if ($billdetails->isNotEmpty())
                <div class="table-responsive text-nowrap">
                    <table class="table w-100">
                        <thead>
                            <tr>
                                <th style="padding-right: 0 !important;">S. No.</th>
                                <th>Bill Date</th>
                                <th>Newspaper</th>
                                <th>Insertion</th>
                                <th>Size &lpar;<span class="text-lowercase">cm</span>&rpar;</th>
                                <th>Rate</th>
                                <th>Amount Due</th>
                                <th>Status</th>
                                <th>Publication Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($billdetails as $key => $billdetail)
                                <tr>
                                    <td>{{ ++$key }}</td> <!-- Serial Number -->
                                    <td>{{ \Carbon\Carbon::parse($billdetail->invoice_date)->toFormattedDateString() }}</td>
                                    <td>{{ $billdetail->user->newspaper->title ?? '-' }}</td>
                                    <td>{{ $billdetail->printed_no_of_insertion ?? '-' }}</td>
                                    <td>{{ $billdetail->printed_size ?? '-' }}</td>
                                    <td>{{ $billdetail->printed_rate ?? '-' }}</td>
                                    <td>{{ number_format(round($billdetail->printed_total_bill)) ?? '-' }}</td>
                                    <td>{{ $billdetail->status ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($billdetail->publication_date)->toFormattedDateString() ?? '-' }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center">

                                            {{-- View --}}
                                            <div class="action-item custom-tooltip">
                                                <a href="{{ route('billings.newspapers.show', $billdetail->id) }}">
                                                    <i class="bx bx-show-alt fs-4 bx-icon"></i>
                                                </a>
                                                <span class="tooltip-text">View Bill</span>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- <div class="custom-pagination">
                        {{ $advertisements->links() }}
                    </div> --}}
                </div>
            @endif
        </div>
    </div>
    {{-- ! / Page Content --}}
@endpush

@push('scripts')
@endpush

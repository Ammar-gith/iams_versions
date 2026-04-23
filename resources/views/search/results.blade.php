@extends('layouts.masterVertical')

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="card mb-4" style="padding: 0;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="{{ url()->previous() }}" class="back-button me-2"><i class='bx bx-arrow-back'></i></a>
                    <h5>Search Results for "{{ $query }}"</h5>
                </div>

            </div>
            @if ($results->isEmpty())
                <div class="alert alert-info">No results found.</div>
            @else
                @foreach ($results as $result)
                    <div class="card mb-3">
                        <div class="card-body">
                            {{-- Different display for each type --}}
                            @if ($result->type == 'user')
                                <h5 class="card-title">User: {{ $result->name }}</h5>
                                <p class="card-text">Email: {{ $result->email }}</p>
                                <a href="{{ route('userManagement.user.show', $result->id) }}"
                                    class="btn btn-sm btn-primary">View User</a>
                            @elseif($result->type == 'advertisement')
                                <h5 class="card-title">Advertisement: {{ $result->inf_number }}</h5>
                                <p class="card-text">Result: {{ $result->memo_number }}
                                    {{-- | Description:
                                    {{ Str::limit($result->description, 100) }} --}}
                                </p>
                                <a href="{{ route('advertisements.show', $result->id) }}"
                                    class="btn btn-sm btn-primary">View
                                    Ad</a>
                            @elseif($result->type == 'treasury_challan')
                                <h5 class="card-title">Treasury Challan #{{ $result->id }}</h5>
                                <p class="card-text">Number: {{ $result->challan_number ?? 'N/A' }} | Amount:
                                    {{ $result->amount ?? 'N/A' }}</p>
                                <a href="{{ route('billings.treasury-challans.show', $result->id) }}"
                                    class="btn btn-sm btn-primary">View Challan</a>
                            @elseif($result->type == 'payment')
                                <h5 class="card-title">Payment #{{ $result->id }}</h5>
                                <p class="card-text">Invoice: {{ $result->invoice_number ?? 'N/A' }} | Amount:
                                    {{ $result->amount ?? 'N/A' }}</p>
                                <a href="{{ route('payment.newspapers.show', $result->id) }}"
                                    class="btn btn-sm btn-primary">View Payment</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

@endpush

@push('scripts')
    {{-- <script>
        flatpickr("#submission_date", {
            mode: "range",
            dateFormat: "d-m-Y",
            allowInput: true
        });
        flatpickr("#publication_date", {
            mode: "range",
            dateFormat: "d-m-Y",
            allowInput: true
        });
    </script> --}}
@endpush

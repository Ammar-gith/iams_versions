@extends('layouts.masterVertical')

{{-- Custom CSS --}}
@push('style')
    <style>
        .custom-padding {
            padding-inline: 1.4rem !important;
            padding-block: 1.2rem !important;
        }

        .custom-caption {
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 2px;
        }

        .custom-heading-5 {
            font-size: 1.4rem;
            font-family: "poppins", sans-serif;
            font-weight: bold;
            margin-top: -12px;
            color: #006c4b;
        }

        .custom-heading-6 {
            font-size: 1.2rem;
            font-family: "poppins", sans-serif;
            font-weight: bold;
            color: #006c4b;
        }

        .decorated-para {
            font-style: italic;
            font-family: "poppins", sans-serif;
            margin-bottom: .5rem;
        }

        .lead-block {
            border-right: .15rem solid #0FA577;
        }

        .lead-para {
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .info-para {
            font-weight: bold;
            padding-left: 1rem;
            margin-bottom: 0;
        }

        .p-info-para {
            display: flex;
            align-items: center;
        }

        table {
            background-color: #e3e3e3;
            border-radius: .5rem;
            margin-inline: 13px;
        }

        table tbody tr:last-child() {
            border-bottom-width: 0;
        }
    </style>
@endpush

@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="flex-grow-1 mb-4">
        <div class="row">

            {{-- INF Series --}}
            <div class="card custom-padding">
                <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                <div class="card-body">
                    <div class="row">

                        <div class="row">

                            <div class="col-md-12">
                                <p class="custom-caption">what is</p>
                                <h5 class="custom-heading-5">Information &lpar;INF&rpar; Series</h5>
                            </div>
                        </div>
                        <div class="row" style="margin-top: -6px;">
                            <div class="col-md-8 lead-block">
                                <p class="decorated-para text-muted">System auto-generated numerical series</p>
                                <p class="lead-para">The INF Series is used to identify each Advertisement uniqly, typically
                                    begins with one &lpar;01/Year&rpar;, incrementing for each Advertisement entering into
                                    the
                                    IAMS ecosystem.</p>
                            </div>
                            <div class="col-md-4 p-info-para">
                                <p class="info-para">A new series initiates at the start of new year, automatically closing
                                    the previous one.</p>
                            </div>
                        </div>
                    </div>
                    <hr class="my-4" />
                    <div class="row" style="margin-bottom: 2rem;">
                        <div class="row">
                            <h5 class="custom-heading-6">Current INF Series</h5>
                            @if ($currentSeries)
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Series</th>
                                            <th>Total Advertisements</th>
                                            <th>Started From</th>
                                            <th>Numbers Issued</th>
                                            <th>Next Issue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $currentSeries->series }}</td>
                                            <td>{{ $advertisements->count() }}</td>
                                            <td>{{ sprintf('%02d/%s', $currentSeries->start_number, substr($currentSeries->series, -2)) }}
                                            </td>
                                            <td>{{ $currentSeries->issued_numbers }}</td>
                                            <td>{{ sprintf('%02d/%s', $currentSeries->start_number + $currentSeries->issued_numbers, substr($currentSeries->series, -2)) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            @else
                                <p>No current INF series found.</p>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <h5 class="custom-heading-6">Previous INF Serieses</h5>
                        @if ($previousSeries->count() > 0)
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">S. No.</th>
                                        {{-- <th scope="col">Serieses</th> --}}
                                        <th scope="col">Started From</th>
                                        <th scope="col">Ended With</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($previousSeries as $index => $series)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ sprintf('%05d/%s', $series->start_number, substr($series->series, -2)) }}
                                            </td>
                                            <td>{{ sprintf('%05d/%s', $series->start_number + $series->issued_numbers - 1, substr($series->series, -2)) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>No previous INF series&lpar;es&rpar; found.</p>
                        @endif
                    </div>
                </div>
            </div>
            {{-- ! / INF Series --}}
        </div>
    </div>
    {{-- ! / Page Content --}}

    {{-- Custom JavaScript --}}
    <script>
        // Simulate fetching value from the database
        const dbValue = "1234"; // Replace this with your DB value

        const input = document.getElementById("dynamicInput");

        // Set the default value and adjust the width on load
        input.value = dbValue;
        input.style.width = `${Math.max(dbValue.length + 1, 1)}ch`;

        // Adjust width dynamically when the input changes
        input.addEventListener("input", () => {
            input.style.width = `${Math.max(input.value.length + 1, 1)}ch`;
        });
    </script>
@endpush

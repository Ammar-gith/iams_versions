@extends('layouts.masterVertical')

{{-- Page Content --}}
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    {{-- Page Content --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ url()->previous() }}" class="back-button"><i class='bx bx-arrow-back'></i></a>
                        <h5 class="mt-2">Status Wise Report</h5>
                    </div>

                    {{-- Export Buttons --}}
                    <div class="mb-3">
                        <a href="{{ route('reports.status', ['export' => 'excel']) }}" class="custom-primary-button">Export
                            Excel</a>
                        <a href="{{ route('reports.status', ['export' => 'pdf']) }}" class="btn btn-danger">Export PDF</a>
                    </div>

                    {{-- Table --}}
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ $row['count'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Chart --}}
                    <div id="statusChart" style="height: 350px;"></div>

                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        var options = {
            chart: {
                type: 'bar',
                height: 350
            },
            series: [{
                name: 'Ads',
                data: @json(array_column($data, 'count'))
            }],
            xaxis: {
                categories: @json(array_column($data, 'label'))
            }
        };
        new ApexCharts(document.querySelector("#statusChart"), options).render();
    </script>
@endpush

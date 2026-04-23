@extends('layouts.masterVertical')

{{-- Custom CSS --}}
@push('style')
    <style>
        /* Title */
        .dashboard-title {
            position: relative;
        }

        .dashboard-title::after {
            content: "";
            position: absolute;
            top: 103%;
            left: 0;
            width: 100%;
            height: .1rem;
            background-color: rgb(224 224 224);
            border-radius: 2px;
        }

        .h4-reset,
        .h5-reset,
        .h6-reset {
            margin: 0 !important;
            color: var(--dark-text);
        }

        .custom-toggle {
            position: relative;
            font-weight: 500;
            padding: 0.55rem 0;
            color: #1c352c;
        }

        .custom-toggle.active {
            color: #59A683;
        }

        /* Show underline only for active */
        .custom-toggle.active::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 0.14rem;
            background-color: #59A683;
            border-radius: 2px;
            z-index: 100;
        }

        .custom-toggle:hover {
            color: #59A683;
        }

        /* ! / End Title */
        /* Filters */
        .custom-export {
            font-size: .8rem;
            font-weight: 500;
            padding: .4rem;
            color: var(--dark-bg);
            border: 1px solid #d4d8dd;
            border-radius: 0.25rem;
        }

        .custom-export:hover {
            color: var(--white);
            background: linear-gradient(135deg, #AAD9C9, #5DB698);
        }

        .status-badge {
            padding: .8rem .6rem;
            border-radius: 12px;
            gap: .4rem;
        }

        .status-icon {
            padding: .35rem;
            border-radius: 50%;
            width: fit-content;
            color: #fff !important;
            font-size: 1rem;
        }

        .status-count {
            font-weight: bold;
        }

        .export-icon {
            font-size: 1rem !important;
        }

        /* Base circular badge */
        .password-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            /* ensures small numbers don’t collapse */
            padding: 6px;
            /* dynamic spacing */
            font-size: 1rem;
            /* consistent readable size */
            font-weight: 600;
            line-height: 1;
            background-color: #E4BF61;
            color: #fff;
            border-radius: 32px;
            /* pill style */
            white-space: nowrap;
            /* prevent wrapping */
            transition: all 0.2s ease-in-out;
        }

        .password-badge.digits-1 {
            min-width: 28px;
        }

        .password-badge.digits-2 {
            min-width: 36px;
        }

        .password-badge.digits-3 {
            min-width: 44px;
        }

        .password-badge.digits-4 {
            min-width: 52px;
        }

        .password-badge.digits-5 {
            min-width: 60px;
        }

        .password-badge.digits-6 {
            min-width: 68px;
        }

        .status-badge:nth-child(1) {
            background-color: var(--new-light);
        }

        .status-icon-new {
            background-color: var(--new-dark);
        }

        .status-badge:nth-child(2) {
            background-color: var(--inprogress-light);
        }

        .status-icon-approved {
            background-color: var(--approved-dark);
        }

        .status-badge:nth-child(3) {
            background-color: var(--approved-light);
        }

        .status-icon-published {
            background-color: var(--published-dark);
        }

        .status-badge:nth-child(4) {
            background-color: var(--published-light);
        }

        .status-icon-inprogress {
            background-color: var(--inprogress-dark);
        }

        .status-badge:nth-child(5) {
            background-color: var(--rejected-light);
        }

        .status-icon-rejected {
            background-color: var(--rejected-dark);
        }

        /* ! / End Filters */
        .container-xxl {
            padding-right: 1rem;
            padding-left: 1rem;
        }

        /* Billings and Payments */
        .billings p,
        .payments p {
            font-size: .8rem;
        }
    </style>
@endpush

<!-- Page Content -->
@push('content')


    {{-- Dashboard Title --}}
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-title d-flex align-items-end justify-content-between mb-3 pb-1">
                <div class="dashboard-date">
                    <h5 class="h5-reset">Client Office Dashboard</h5>
                    <small>{{ $today }}</small>
                </div>
                @include('components.filters.date-range', ['route' => 'dashboard'])
                <div class="dashboard-ad-category d-flex gap-4">
                    <div class="classified-category">
                        <a href="#" class="custom-toggle ad-type-tab active" data-type="classified">
                            <i class="bx bx-news me-1"></i> Classified Ads
                        </a>
                    </div>
                    <div class="compaign-category">
                        <a href="#" class="custom-toggle ad-type-tab" data-type="campaign">
                            <i class="bx bxs-megaphone me-1"></i> Compaign Ads
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Filters & Password Change Requests --}}
    <div class="row">
        {{-- Status Filters --}}
        <div class="col-lg-7 col-md-7">
            <div class="status-container card p-3 mb-2" id="status-badges">
                {{-- Status Title --}}
                <div class="status-title-container d-flex align-items-center justify-content-between mb-3">
                    <div class="status-title">
                        <h6 class="h6-reset">Ad Performance Overview</h6>
                        <small>Year-to-Date Summary</small>
                    </div>
                    <div class="status-export d-flex align-items-center gap-1">
                        <a href="javascript:void(0)" onclick="saveAsPNG()" class="custom-export">
                            <i class="bx bx-export export-icon"></i>
                        </a>
                    </div>
                </div>
                {{-- Status Badges --}}
                <div class="status-badges d-flex justify-content-between">
                    <div class="status-badge d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between mb-3">
                            <i class="bx bxs-file-import status-icon status-icon-new"></i>
                            <h5 class="h5-reset status-count pt-1">{{ $newCount }}</h5>
                        </div>
                        <h6 class="h6-reset">New Ads</h6>
                        {{-- <small class="small-text">
                            @if ($newChangePercent >= 0)
                                + {{ $newChangePercent }}% from yesterday
                            @else
                                {{ $newChangePercent }}% from yesterday
                            @endif
                        </small> --}}
                    </div>
                    <div class="status-badge d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between mb-3">
                            <i class="bx bxs-hourglass-top status-icon status-icon-inprogress"></i>
                            <h5 class="h5-reset status-count pt-1">{{ $inprogressCount }}</h5>
                        </div>
                        <h6 class="h6-reset">Inprogress</h6>
                        {{-- <small class="small-text">+ 2% from yesterday</small> --}}
                    </div>
                    <div class="status-badge d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between mb-3">
                            <i class="bx bxs-file-plus status-icon status-icon-approved"></i>
                            <h5 class="h5-reset status-count pt-1">{{ $approvedCount }}</h5>
                        </div>
                        <h6 class="h6-reset">Approved</h6>
                        {{-- <small class="small-text">+ 2% from yesterday</small> --}}
                    </div>
                    <div class="status-badge d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between mb-3">
                            <i class="bx bxs-check-circle status-icon status-icon-published"></i>
                            <h5 class="h5-reset status-count pt-1">{{ $publishedCount }}</h5>
                        </div>
                        <h6 class="h6-reset">Published</h6>
                        {{-- <small class="small-text">+ 2% from yesterday</small> --}}
                    </div>
                    <div class="status-badge d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between mb-3">
                            <i class="bx bxs-x-circle status-icon status-icon-rejected"></i>
                            <h5 class="h5-reset status-count pt-1">{{ $rejectedCount }}</h5>
                        </div>
                        <h6 class="h6-reset">Rejected</h6>
                        {{-- <small class="small-text">+ 2% from yesterday</small> --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Personal Ledger Account (PLA) --}}
        {{-- <div class="col-lg-5 col-md-5" style="padding-left: 0 !important;">
            <div class="status-title-container card mb-2 p-3">
                <div class="d-flex flex-column gap-2">
                    <h6 class="h6-reset mb-0 mr-1">Personal Ledger Account &lpar;PLA&rpar;</h6>
                    <div class="d-flex justify-content-around">
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar pla-avatar">
                                    <span class="avatar-initial bg-label-warning rounded-circle">
                                        <i class='bx bxs-bank pla-size'></i>
                                    </span>
                                </div>
                                {{-- total estimated cost sent to  client offices in billings --}
                                <div class="card-info">
                                    <h4 class="h4-reset mb-0">Rs {{ number_format($totalEstimatedCost) }}
                                    </h4>
                                    {{-- <h4 class="h4-reset mb-0">Rs {{ number_format($totalEstimatedCost / 1_000_000, 2) M}}
                                    </h4> --}
                                    <small class="text-muted">Balance</small>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <small>Newspapers &#8211; 08</small>
                                <small>Agencies &#8211; 01 </small>
                            </div>
                        </div>

                        <!-- Vertical Divider -->
                        <div class="divider-vertical"></div>
                        <div class="d-flex flex-column justify-content-around">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                {{-- total cheque cost sent by client offices and added to pla account --}
                                <div>
                                    <h6 class="h6-reset mb-0 me-2">Rs
                                        {{ number_format($totalRecevieAbleCost) }}</h6>
                                    {{-- <h6 class="h6-reset mb-0 me-2">Rs
                                        {{ number_format($totalRecevieAbleCost / 1_000_000, 2) }} M</h6> --}
                                    <small class="text-muted">A/C Receivable</small>
                                </div>
                                <div class="avatar pla-avatar-up">
                                    <span class="avatar-initial bg-label-success rounded-circle">
                                        <i class='bx bx-down-arrow-alt'></i>
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <h6 class="h6-reset mb-0 me-2">Rs
                                        94,212 </h6>
                                    {{-- <h6 class="h6-reset mb-0 me-2">Rs
                                        {{ number_format($totalEstimatedCost / 1_000_000, 2) }} M</h6> --}
                                    <small class="text-muted">A/C Payable</small>
                                </div>
                                <div class="avatar pla-avatar-up">
                                    <span class="avatar-initial bg-label-danger rounded-circle">
                                        <i class='bx bx-up-arrow-alt'></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Ads Insights By - Status, Office and Category -->
    {{-- Charts --}}
    <div class="card mb-2">
        <!-- Card Toggle -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Ads Insights By &ndash; Status, Office and Category</h5>
            <div class="d-flex align-items-center gap-2">
                <div class="collapse-tab" data-id="2" onclick="toggleCardBody(this)" style="cursor: pointer;">
                    <i class="bx bx-chevron-down form-control w-auto" id="toggleIcon2"></i>
                </div>
            </div>
        </div>
        <div id="cardBody2" style="display: block;">
            <div class="card-body" style="padding-top: 0 !important;">

                <!-- Status Chart -->
                <div class="card mb-3 mt-3">
                    <div class="card-header">
                        <h6 class="h6-reset">Status wise {{ $currentMonth }} Insights</h6>
                    </div>
                    <div class="card-body">
                        <div id="monthlyAdChart"></div>
                    </div>
                </div>

                <!-- Office/Department Chart -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="h6-reset">Top Departments &ndash; {{ $currentMonth }} Ads</h6>
                    </div>
                    <div class="card-body">
                        <div id="officeAdChart"></div>
                    </div>
                </div>

                {{-- Category, Monthly Trend, Weekly Submission, Year-wise Breakdown --}}
                <div class="row">
                    <!-- Category Chart -->
                    <div class="col-md-6" style="padding-right: 0 !important;">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="h6-reset">Most Popular Categories &ndash; {{ $currentMonth }}</h6>
                            </div>
                            <div class="card-body">
                                <div id="categoryAdChart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Ads Trend -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="h6-reset">Monthly Ad Submission Trends</h6>
                            </div>
                            <div class="card-body">
                                <div id="monthlyAdsChart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Submission Heatmap -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="h6-reset">Weekly Ad Submission Patterns</h6>
                            </div>
                            <div class="card-body">
                                <div id="weeklySubmission"></div>
                                <hr />
                                <div class="d-flex justify-content-around">
                                    <p><strong>Busiest Day:</strong> {{ $busiestDay }}</p>
                                    <p><strong>Peak Time Block:</strong> {{ $busiestBlock }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Year-wise Breakdown -->
                    <div class="col-md-12 mt-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="h6-reset">Yearly Ad Performance Summary</h6>
                            </div>
                            <div class="card-body">
                                <div id="yearlyTrendChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ Ads Insights By - Status, Office and Category -->

    <!-- Financial Insights By - -->
    {{-- <div class="card mb-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Financial Insights By - </h5>
            <div class="d-flex align-items-center gap-2">
                <!-- Card Toggle -->
                <div class="collapse-tab" data-id="3" onclick="toggleCardBody(this)" style="cursor: pointer;">
                    <i class="bx bx-chevron-down form-control w-auto" id="toggleIcon3"></i>
                </div>
            </div>
        </div>
        <div id="cardBody3" style="display: block;">
            <div class="card-body" style="padding-top: 0 !important;">

                <!-- Add row here for two cards -->
                <div class="row">
                    <!-- Referral Card -->
                    <div class="col-lg-6 col-md-6 col-sm-12 mt-3">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="h6-reset">Status wise {{ $currentMonth }} Insights</h6>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="mb-1">$32,690</h2>
                                <span class="text-muted">Referral 40%</span>
                                <div id="referralLineChart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Impression Card -->
                    <div class="col-lg-6 col-md-6 col-sm-12 mt-3">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="h6-reset">Top Offices &ndash; {{ $currentMonth }} Ads</h6>
                            </div>
                            <div class="card-body text-center">
                                <div id="impressionDonutChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End row -->
            </div>
        </div>
    </div> --}}
    <!--/ Financial Insights By - -->
@endpush
<!--/ Page Content -->

@push('scripts')
    {{-- Toggleable Card View JS --}}
    <script>
        function toggleCardBody(element) {
            const id = element.getAttribute('data-id');
            const cardBody = document.getElementById(`cardBody${id}`);
            const icon = document.getElementById(`toggleIcon${id}`);

            if (cardBody.style.display === 'none') {
                cardBody.style.display = 'block';
                icon.classList.remove('bx-chevron-up');
                icon.classList.add('bx-chevron-down');
            } else {
                cardBody.style.display = 'none';
                icon.classList.remove('bx-chevron-down');
                icon.classList.add('bx-chevron-up');
            }
        }
    </script>

    {{-- Chart JS --}}
    <script>
        // ---- Status Chart ----
        window.chartData = @json($chartData);
        window.chartCategories = @json($categories);

        // ---- Top Departments Chart ----
        window.officeData = @json($officeData);
        window.officeNames = @json($officeNames);

        // ---- Category Chart ----
        window.categoryLabels = @json($categoryLabels);
        window.categoryCounts = @json($categoryCounts);
    </script>

    {{-- Monthly Ads Trend --}}
    <script>
        var options = {
            chart: {
                type: 'bar',
                height: 350
            },
            series: [{
                name: 'Ads Submitted',
                data: @json($monthlyAds)
            }],
            xaxis: {
                categories: [
                    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                ]
            },
            colors: ['#2b7cb9', '#437623', '#0FA577', '#9039F6', '#f0ad40', '#8e4848', '#1B9AAA', '#74B9FF', '#06D6A0',
                '#9B5DE5', '#FAB1A0', '#2D3436'
            ]
        };

        var chart = new ApexCharts(document.querySelector("#monthlyAdsChart"), options);
        chart.render();
    </script>

    {{-- Weekly Submission Heatmap --}}
    <script>
        var options = {
            chart: {
                height: 350,
                type: 'heatmap',
            },
            dataLabels: {
                enabled: false
            },
            series: @json($weeklyData),
            xaxis: {
                categories: @json($timeCategories),
            },
            colors: ["#245142"],
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " ads submitted";
                    }
                }
            }
        };
        new ApexCharts(document.querySelector("#weeklySubmission"), options).render();
    </script>

    {{-- Year-wise Breakdown --}}
    <script>
        fetch('/yearly-ads-trend')
            .then(res => res.json())
            .then(data => {
                var options = {
                    chart: {
                        type: 'line',
                        height: 420
                    },
                    series: [{
                        name: 'Ads Count',
                        data: data.counts
                    }],
                    xaxis: {
                        categories: data.years,
                        // title: { text: 'Year' }
                    },
                    // yaxis: {
                    //     title: { text: 'Number of Ads' }
                    // },
                    colors: ['#397F67'],
                    markers: {
                        size: 5.7
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2.4
                    }
                };

                var chart = new ApexCharts(document.querySelector("#yearlyTrendChart"), options);
                chart.render();
            });
    </script>

    {{-- Save as PNG JS --}}
    {{-- Include html2canvas --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function saveAsPNG() {
            const element = document.getElementById("status-badges");
            html2canvas(element).then((canvas) => {
                const link = document.createElement("a");
                link.download = "Ads-Summary.png"; // filename
                link.href = canvas.toDataURL("image/png");
                link.click();
            });
        }
    </script>

    {{-- Include Chart JS --}}
    <script src="{{ asset('js/monthly-ads-chart.js') }}"></script>
@endpush

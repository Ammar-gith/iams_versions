<!-- Sidebar Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/branding/Favicon.png') }}" alt="logo" width="30">
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">DG-IPR</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx menu-toggle-icon d-none d-xl-block fs-4 align-middle"></i>
            <i class="bx bx-x d-block d-xl-none bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ Request::routeIs('dashboard') ? 'open active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <!-- Ads -->
        @can('View Advertisements')
            @php
                $routesToMatch = [
                    'advertisements.index',
                    'advertisements.show',
                    'advertisements.edit',
                    'advertisements.create',
                    'advertisements.inprogress',
                    'advertisements.show-inprogress',
                    'advertisement.trackAd',
                    'advertisements.approved',
                    'advertisements.rejected',
                    'advertisements.published',
                    'advertisements.published.show',
                    'advertisements.unpublished',
                    'advertisements.inf_series',
                    'advertisements.archived',
                    'advertisements.draft.index',
                    'advertisements.draft.edit',
                    'advertisements.draft.show',
                ];

            @endphp
            <li class="menu-item {{ Request::routeIs($routesToMatch) ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-book-add"></i>
                    <div data-i18n="Advertisements">Advertisements</div>
                </a>
                <ul class="menu-sub">
                    {{-- create Ad --}}
                    @can('Create Advertisement')
                        <li class="menu-item {{ Request::routeIs('advertisements.create') ? 'open active' : '' }}">
                            <a href="{{ route('advertisements.create') }}" class="menu-link">
                                <div data-i18n="Create">Create</div>
                            </a>
                        </li>
                    @endcan

                    @php
                        $user = auth()->user();
                    @endphp
                    {{-- New Ads --}}

                    @php
                        $isNewTrack = request()->routeIs('advertisement.trackAd') && request()->get('from') === 'new';
                    @endphp
                    <li
                        class="menu-item {{ Request::routeIs('advertisements.index', 'advertisements.show', 'advertisements.edit') || $isNewTrack ? 'active' : '' }}">
                        <a href="{{ route('advertisements.index') }}" class="menu-link">
                            <div data-i18n="New Ads">New Ads</div>
                        </a>
                    </li>

                    {{-- Inprogress --}}
                    @can('view inprogress ads')
                        @php
                            $isInProgressTrack =
                                request()->routeIs('advertisement.trackAd') && request()->get('from') === 'inprogress';
                        @endphp
                        <li
                            class="menu-item {{ Request::routeIs('advertisements.inprogress', 'advertisements.show-inprogress') || $isInProgressTrack ? 'active' : '' }}">
                            <a href="{{ route('advertisements.inprogress') }}" class="menu-link">
                                <div data-i18n="In Progress">In Progress</div>
                            </a>
                        </li>
                    @endcan

                    {{-- Draft --}}
                    @can('view draft')
                        @php
                            $draftRoutes = [
                                'advertisements.draft.index',
                                'advertisements.draft.edit',
                                'advertisements.draft.show',
                                'advertisements.draft.update',
                            ];
                            $isDraftRoute =
                                Request::routeIs(...$draftRoutes) ||
                                (request()->routeIs('advertisement.trackAd') && request()->get('from') === 'draft');
                        @endphp
                        <li class="menu-item {{ $isDraftRoute ? 'open active' : '' }}">
                            <a href="{{ route('advertisements.draft.index') }}" class="menu-link">
                                <div data-i18n="Drafts">Drafts</div>
                            </a>
                        </li>
                    @endcan

                    {{-- Approved --}}
                    @can('view approved adv')
                        <li class="menu-item {{ Request::routeIs('advertisements.approved') ? 'open active' : '' }}">
                            <a href="{{ route('advertisements.approved') }}" class="menu-link">
                                <div data-i18n="Approved">Approved</div>
                            </a>
                        </li>
                    @endcan

                    {{-- Rejected --}}
                    @can('view rejected adv')
                        <li class="menu-item {{ Request::routeIs('advertisements.rejected') ? 'open active' : '' }}">
                            <a href="{{ route('advertisements.rejected') }}" class="menu-link">
                                <div data-i18n="Rejected">Rejected</div>
                            </a>
                        </li>
                    @endcan

                    {{-- Published --}}
                    @can('view published adv')
                        <li
                            class="menu-item {{ Request::routeIs('advertisements.published', 'advertisements.published.show') ? 'open active' : '' }}">
                            <a href="{{ route('advertisements.published') }}" class="menu-link">
                                <div data-i18n="Published">Published</div>
                            </a>
                        </li>
                    @endcan

                    {{-- Archive --}}
                    <li class="menu-item {{ Request::routeIs('advertisements.archived') ? 'open active' : '' }}">
                        <a href=" {{ route('advertisements.archived') }}" class="menu-link">
                            <div data-i18n="Archive">Archive</div>
                        </a>
                    </li>

                    {{-- INF Series --}}
                    @can('view inf series')
                        <li class="menu-item {{ Request::routeIs('advertisements.inf_series') ? 'open active' : '' }}">
                            <a href="{{ route('advertisements.inf_series') }}" class="menu-link">
                                <div data-i18n="INF Series">INF Series</div>
                            </a>
                        </li>
                    @endcan

                    {{-- Classified Ads - Agencies --}}
                    @can('view classified ads')
                        <li class="menu-item">
                            <a href="" class="menu-link">
                                <div data-i18n="Classified Ads - Agencies">Classified Ads - Agencies</div>
                            </a>
                        </li>
                    @endcan

                </ul>
            </li>
        @endcan


        {{-- super admin role --}}

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Super Admin Role</span>
        </li>

        <!-- Diary Dispatch -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Diary Dispatch">Diary Dispatch</div>
            </a>

            <ul class="menu-sub">
                {{-- Create Ads --}}
                <li class="menu-item">
                    <a href="" class="menu-link">
                        <div data-i18n="Create Ad">Create Ad</div>
                    </a>
                </li>

                {{-- Diary dispatch index --}}
                <li class="menu-item">
                    <a href="" class="menu-link">
                        <div data-i18n="Index">Index</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Super Intendent -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Super Intendent">Super Intendent</div>
            </a>

            <ul class="menu-sub">
                {{-- Pending Ads --}}
                <li class="menu-item">
                    <a href="" class="menu-link">
                        <div data-i18n="Pending Ads">Pending Ads</div>
                    </a>
                </li>

                {{-- Approved Ads --}}
                <li class="menu-item">
                    <a href="" class="menu-link">
                        <div data-i18n="Approved Ads">Approved Ads</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Deputy Director -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Deputy Director">Deputy Director</div>
            </a>

            <ul class="menu-sub">
                {{-- Pending Ads --}}
                <li class="menu-item">
                    <a href="" class="menu-link">
                        <div data-i18n="Pending Ads">Pending Ads</div>
                    </a>
                </li>

                {{-- Approved Ads --}}
                <li class="menu-item">
                    <a href="" class="menu-link">
                        <div data-i18n="Approved Ads">Approved Ads</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Director General -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Director General">Director General</div>
            </a>

            <ul class="menu-sub">
                {{-- Pending Ads --}}
                <li class="menu-item">
                    <a href="" class="menu-link">
                        <div data-i18n="Pending Ads">Pending Ads</div>
                    </a>
                </li>

                {{-- Approved Ads --}}
                <li class="menu-item">
                    <a href="" class="menu-link">
                        <div data-i18n="Approved Ads">Approved Ads</div>
                    </a>
                </li>
            </ul>
        </li>

          <!-- Secretary -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Secretary">Secretary</div>
            </a>

            <ul class="menu-sub">
                {{-- Pending Ads --}}
                <li class="menu-item">
                    <a href="" class="menu-link">
                        <div data-i18n="Pending Ads">Pending Ads</div>
                    </a>
                </li>

                {{-- Approved Ads --}}
                <li class="menu-item">
                    <a href="" class="menu-link">
                        <div data-i18n="Approved Ads">Approved Ads</div>
                    </a>
                </li>
            </ul>
        </li>


        <!-- Financials  -->
        @can('View billings')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Financials</span>
            </li>
            <li
                class="menu-item {{ Request::routeIs(['billings.newspapers.*', 'billings.agencies.*']) ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Billings">Billings</div>
                </a>
                @php
                    $user = auth()->user();
                    $userRole = $user->roles->pluck('name')->first();
                @endphp

                <ul class="menu-sub">

                    {{-- Billing Newspapers --}}
                    <li class="menu-item {{ Request::routeIs('billings.newspapers.index') ? 'active' : '' }}">
                        <a href="{{ route('billings.newspapers.index') }}" class="menu-link">
                            <div data-i18n="Billing Newspapers">Billing Newspapers</div>
                        </a>
                    </li>


                    {{-- Billing Agencies --}}
                    <li class="menu-item {{ Request::routeIs('billings.agencies.index') ? 'active' : '' }}">
                        <a href="{{ route('billings.agencies.index') }}" class="menu-link">
                            <div data-i18n="Billing Agencies">Billing Agencies</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @role('Client Office')
            <li
                class="menu-item {{ Request::routeIs('billings.treasury-challans.showOnlinCheque') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Billings">Cheque Submission</div>
                </a>
                <ul class="menu-sub">
                    <li
                        class="menu-item {{ Request::routeIs('billings.treasury-challans.showOnlinCheque') ? 'active' : '' }}">
                        <a href="{{ route('billings.treasury-challans.showOnlinCheque') }}" class="menu-link">
                            <div data-i18n="Cheques">Cheques </div>
                        </a>
                    </li>
                </ul>
            </li>
        @endrole

        @can('View financials')
            {{-- Recovery and PLA --}}
            <li class="menu-item {{ Request::routeIs('billings.treasury-challans.*') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-coin"></i>
                    <div data-i18n="Billings">Recovery & PLA</div>
                </a>
                <ul class="menu-sub">
                    {{-- Treasury Challans --}}
                    @if ($user->hasRole(['Superintendent', 'Super Admin']))
                        <li class="menu-item {{ Request::routeIs('billings.treasury-challans.index') ? 'active' : '' }}">
                            <a href="{{ route('billings.treasury-challans.index') }}" class="menu-link">
                                <div data-i18n="Diary Of Cheques">Diary Of Cheques</div>
                            </a>
                        </li>
                    @elseif ($user->hasRole('Director General'))
                        <li class="menu-item {{ Request::routeIs('billings.treasury-challans.index') ? 'active' : '' }}">
                            <a href="{{ route('billings.treasury-challans.index') }}" class="menu-link">
                                <div data-i18n="Diary Of Cheques">Cheques Approval</div>
                            </a>
                        </li>
                    @endif
                    <li class="menu-item  {{ Request::routeIs('billings.treasury-challans.plaIndex') ? 'active' : '' }}">
                        <a href="{{ route('billings.treasury-challans.plaIndex') }}" class="menu-link">
                            <div data-i18n="PLA Account">PLA Account</div>
                        </a>
                    </li>




                    {{-- Cheque Receipts - Agencies --}}
                    {{-- <li class="menu-item {{ Request::routeIs('billings.cheque-receipts-ag.*') ? 'open active' : '' }}">
                        <a href="" class="menu-link">
                            <div data-i18n="Cheque Receipts - Agencies">Cheque Receipts - Agencies</div>
                        </a>
                    </li> --}}

                </ul>
            </li>

            @can('View payments')
                <li class="menu-item {{ Request::routeIs('payment.newspapers.*') ? 'open active' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-money"></i>
                        <div data-i18n="Payments">Payments</div>
                    </a>
                    <ul class="menu-sub">
                        {{-- Payments Newspapers --}}
                        <li class="menu-item {{ Request::routeIs('payment.newspapers.index') ? 'open active' : '' }}">
                            <a href="{{ route('payment.newspapers.index') }}" class="menu-link">
                                <div data-i18n="Ledger">Ledger</div>
                            </a>
                        </li>

                        {{-- Payments Agencies --}}
                        <li class="menu-item {{ Request::routeIs('payment.newspapers.bulkview') ? 'open active' : '' }}">
                            <a href="{{ route('payment.newspapers.bulkview') }}" class="menu-link">
                                <div data-i18n="Book">Book</div>
                            </a>
                        </li>

                        {{-- Payements Radios --}}
                        <li class="menu-item {{ Request::routeIs('payment.newspapers.summary') ? 'open active' : '' }}">
                            <a href="{{ route('payment.newspapers.summary') }}" class="menu-link">
                                <div data-i18n="Summary">Summary</div>
                            </a>
                        </li>

                        {{-- Tax Payments --}}
                        <li
                            class="menu-item {{ Request::routeIs('payment.newspapers.bank-name-wise') ? 'open active' : '' }}">
                            <a href="{{ route('payment.newspapers.bank-name-wise') }}" class="menu-link">
                                <div data-i18n="Bank Schedule">Bank Schedule</div>
                            </a>
                        </li>

                        <li
                            class="menu-item {{ Request::routeIs('payment.newspapers.po-list-summary') ? 'open active' : '' }}">
                            <a href="{{ route('payment.newspapers.po-list-summary') }}" class="menu-link">
                                <div data-i18n="PO List Summary">PO List Summary</div>
                            </a>
                        </li>
                        <li
                            class="menu-item {{ Request::routeIs('payment.newspapers.pay-order-list') ? 'open active' : '' }}">
                            <a href="{{ route('payment.newspapers.pay-order-list') }}" class="menu-link">
                                <div data-i18n="Pay Order List">Pay Order List</div>
                            </a>
                        </li>


                        <li class="menu-item {{ Request::routeIs('payment.newspapers.paid-amount') ? 'open active' : '' }}">
                            <a href="{{ route('payment.newspapers.paid-amount') }}" class="menu-link">
                                <div data-i18n="Pay Amount">Pay Amount</div>
                            </a>
                        </li>
                        <li
                            class="menu-item {{ Request::routeIs('payment.newspapers.paid-amount.history') ? 'open active' : '' }}">
                            <a href="{{ route('payment.newspapers.paid-amount.history') }}" class="menu-link">
                                <div data-i18n="Pay Amount">Payment History</div>
                            </a>
                        </li>


                        {{-- <li class="menu-item {{ Request::routeIs('payment.batches.index') ? 'open active' : '' }}">
                        <a href="{{ route('payment.batches.index') }}" class="menu-link">
                            <div data-i18n="Pay Amount">Pay Index</div>
                        </a>
                    </li> --}}

                    </ul>
                </li>
            @endcan
        @endcan

        {{--  Newspapers --}}
        @can('View newspapers')
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Media &amp; Reports</span></li>
            <li class="menu-item {{ Request::routeIs('newspaper.*') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-book"></i>
                    <div data-i18n="Newspapers">Newspapers</div>
                </a>
                <ul class="menu-sub">
                    {{-- Newspapers --}}
                    @php
                        $routesToMatch = ['newspaper.index', 'newspaper.show', 'newspaper.edit', 'newspaper.create'];
                    @endphp
                    <li class="menu-item {{ Request::routeIs($routesToMatch) ? 'open active' : '' }}">
                        <a href="{{ route('newspaper.index') }}" class="menu-link">
                            <div data-i18n="All">All</div>
                        </a>
                    </li>
                    {{-- Newspaper Category --}}
                    @php
                        $routesToMatch = [
                            'newspaper.newspaperCategory.index',
                            'newspaper.newspaperCategory.show',
                            'newspaper.newspaperCategory.edit',
                            'newspaper.newspaperCategory.create',
                        ];
                    @endphp
                    <li class="menu-item {{ Request::routeIs($routesToMatch) ? 'open active' : '' }}">
                        <a href="{{ route('newspaper.newspaperCategory.index') }}" class="menu-link">
                            <div data-i18n="Category">Category</div>
                        </a>
                    </li>
                    {{-- Newspaper Periodicity --}}
                    @php
                        $routesToMatch = [
                            'newspaper.newspaperPeriodicity.index',
                            'newspaper.newspaperPeriodicity.show',
                            'newspaper.newspaperPeriodicity.edit',
                            'newspaper.newspaperPeriodicity.create',
                        ];
                    @endphp
                    <li class="menu-item {{ Request::routeIs($routesToMatch) ? 'open active' : '' }}">
                        <a href="{{ route('newspaper.newspaperPeriodicity.index') }}" class="menu-link">
                            <div data-i18n="Periodicity">Periodicity</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        {{--  Advertising Agencies --}}
        @can('View advertising agencies')
            <li class="menu-item {{ Request::routeIs('advAgency.*') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-briefcase"></i>
                    <div data-i18n="Advertising Agencies">Advertising Agencies</div>
                </a>
                {{-- {{ Request::routeIs('master.province') ? 'active' : '' }} --}}
                <ul class="menu-sub">

                    {{-- Adv. Agencies --}}
                    @php
                        $routesToMatch = ['advAgency.index', 'advAgency.show', 'advAgency.edit', 'advAgency.create'];
                    @endphp
                    <li class="menu-item {{ Request::routeIs($routesToMatch) ? 'open active' : '' }}">
                        <a href="{{ route('advAgency.index') }}" class="menu-link">
                            <div data-i18n="Adv. Agencies"> Adv. Agencies</div>
                        </a>
                    </li>
                    {{-- Digital Agencies --}}
                    <li class="menu-item">
                        <a href="{{ route('digitalAgency.index') }}" class="menu-link">
                            <div data-i18n="Digital Agencies">Digital Agencies</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        {{--  TV Channels --}}
        {{-- <li class="menu-item">
            <a href="" class="menu-link">
                <i class="menu-icon tf-icons bx bx-tv"></i>
                <div data-i18n=" TV Channels"> TV Channels</div>
            </a>
        </li> --}}

        {{--  Radio Stations --}}
        {{-- <li class="menu-item">
            <a href="" class="menu-link">
                <i class="menu-icon tf-icons bx bx-radio"></i>
                <div data-i18n=" Radio Stations"> Radio Stations</div>
            </a>
        </li> --}}

        {{--  Telecom Operators --}}
        {{-- <li class="menu-item">
            <a href="" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wifi"></i>
                <div data-i18n=" Telecom Operators"> Telecom Operators</div>
            </a>
        </li> --}}

        {{--  Campaigns --}}
        {{-- <li class="menu-item">
            <a href="" class="menu-link">
                <i class="menu-icon tf-icons bx bx-flag"></i>
                <div data-i18n=" Campaigns"> Campaigns</div>
            </a>
        </li> --}}

        {{--  Reports --}}
        @can('view reports')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Reports</span>
            </li>
            <li class="menu-item {{ Request::routeIs(['reports.*', 'audit-logs.*']) ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-book"></i>
                    <div data-i18n="Reports">Reports</div>
                </a>
                <ul class="menu-sub">

                    {{-- Audit Trails --}}
                    <li class="menu-item {{ Request::routeIs('audit-logs.index') ? 'active' : '' }}">
                        <a href="{{ route('audit-logs.index') }}" class="menu-link">
                            <div data-i18n="Audit Trail">Audit Trail</div>
                        </a>
                    </li>


                    @if (!$user->hasRole('Client Office'))
                        {{-- Statuses --}}
                        <li class="menu-item {{ Request::routeIs('reports.index') ? 'active' : '' }}">
                            <a href="{{ route('reports.index') }}" class="menu-link">
                                <div data-i18n="All">Status</div>
                            </a>
                        </li>

                        {{-- department  --}}
                        <li class="menu-item {{ Request::routeIs('reports.departments') ? 'active' : '' }}">
                            <a href="{{ route('reports.departments') }}" class="menu-link">
                                <div data-i18n="Office Wise Report">Department Wise Report</div>
                            </a>
                        </li>

                        {{-- offices  --}}
                        <li class="menu-item {{ Request::routeIs('reports.offices') ? 'active' : '' }}">
                            <a href="{{ route('reports.offices') }}" class="menu-link">
                                <div data-i18n="Office Wise Report">Office Wise Report</div>
                            </a>
                        </li>

                        {{-- categories  --}}
                        <li class="menu-item {{ Request::routeIs('reports.categories') ? 'active' : '' }}">
                            <a href="{{ route('reports.categories') }}" class="menu-link">
                                <div data-i18n="Category Wise Report">Category Wise Report</div>
                            </a>
                        </li>

                        {{-- years  --}}
                        <li class="menu-item {{ Request::routeIs('reports.years') ? 'active' : '' }}">
                            <a href="{{ route('reports.years') }}" class="menu-link">
                                <div data-i18n="Year Wise Report">Year Wise Report</div>
                            </a>
                        </li>

                        <li class="menu-item {{ Request::routeIs('reports.billing') ? 'active' : '' }}">
                            <a href="{{ route('reports.billing') }}" class="menu-link">
                                <div data-i18n="Billings">Billings</div>
                            </a>
                        </li>

                        <li class="menu-item {{ Request::routeIs('reports.newspaper.pla.amount') ? 'active' : '' }}">
                            <a href="{{ route('reports.newspaper.pla.amount') }}" class="menu-link">
                                <div data-i18n="Newspapers PLA Amount">Newspapers PLA Amount</div>
                            </a>
                        </li>

                        <li class="menu-item {{ Request::routeIs('reports.agency.pla.amount') ? 'active' : '' }}">
                            <a href="{{ route('reports.agency.pla.amount') }}" class="menu-link">
                                <div data-i18n="Adv.Agency PLA Amount">Adv.Agency PLA Amount</div>
                            </a>
                        </li>




                        {{-- Departments --}}
                        {{-- <li class="menu-item {{ Request::routeIs('reports.department') ? 'active' : '' }}">
                            <a href="{{ route('reports.department') }}" class="menu-link">
                                <div data-i18n="Department">Department</div>
                            </a>
                        </li> --}}

                        {{-- Offices --}}
                        {{-- <li class="menu-item {{ Request::routeIs('reports.office') ? 'active' : '' }}">
                            <a href="{{ route('reports.office') }}" class="menu-link">
                                <div data-i18n="Office">Office</div>
                            </a>
                        </li> --}}

                        {{-- Categories --}}
                        {{-- <li class="menu-item {{ Request::routeIs('reports.category') ? 'active' : '' }}">
                            <a href="{{ route('reports.category') }}" class="menu-link">
                                <div data-i18n="Category">Category</div>
                            </a>
                        </li> --}}

                        {{-- Years --}}
                        {{-- <li class="menu-item {{ Request::routeIs('reports.year') ? 'active' : '' }}">
                            <a href="{{ route('reports.year') }}" class="menu-link">
                                <div data-i18n="Year">Year</div>
                            </a>
                        </li> --}}
                    @endif
                    @if ($user->hasRole(['Client Office']))
                        {{-- Offices Advt List --}}
                        <li class="menu-item {{ Request::routeIs('reports.officesAdvtList') ? 'active' : '' }}">
                            <a href="{{ route('reports.officesAdvtList') }}" class="menu-link">
                                <div data-i18n="Offices Advt. List">Offices Advt. List</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endcan

        <!-- Master Data -->
        @role('Super Admin')
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Master Data</span></li>
            <li class="menu-item {{ Request::routeIs('master.*') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-folder"></i>
                    <div data-i18n="Master Data">Master Data</div>
                </a>

                <ul class="menu-sub">

                    {{-- Publisher Types --}}
                    <li class="menu-item {{ Request::routeIs('master.publisherType.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.publisherType.index') }}" class="menu-link">
                            <div data-i18n="Publisher types">Publisher types</div>
                        </a>
                    </li>

                    {{-- Tax Types --}}
                    <li class="menu-item {{ Request::routeIs('master.taxType.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.taxType.index') }}" class="menu-link">
                            <div data-i18n="Tax Types">Tax Types</div>
                        </a>
                    </li>

                    {{-- Tax Payees --}}
                    <li class="menu-item {{ Request::routeIs('master.taxPayee.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.taxPayee.index') }}" class="menu-link">
                            <div data-i18n="Tax Payees">Tax Payees</div>
                        </a>
                    </li>

                    {{-- Newspaper partners --}}
                    <li class="menu-item {{ Request::routeIs('master.newspaperPartner.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.newspaperPartner.index') }}" class="menu-link">
                            <div data-i18n="Newspaper partners">Newspaper partners</div>
                        </a>
                    </li>

                    {{-- Media bank details --}}
                    <li class="menu-item {{ Request::routeIs('master.mediaBankDetail.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.mediaBankDetail.index') }}" class="menu-link">
                            <div data-i18n="Media bank details">Media bank details</div>
                        </a>
                    </li>

                    {{-- Newspaper Positions & Rates --}}
                    <li class="menu-item {{ Request::routeIs('master.newsPosRate.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.newsPosRate.index') }}" class="menu-link">
                            <div data-i18n="Newspapers Positions & Rates">Newspapers Positions & Rates</div>
                        </a>
                    </li>

                    {{-- Ad Worth Parameters --}}
                    <li class="menu-item {{ Request::routeIs('master.adWorthParameter.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.adWorthParameter.index') }}" class="menu-link">
                            <div data-i18n="Ad Worth Parameters">Ad Worth Parameters</div>
                        </a>
                    </li>

                    {{-- Classified Ad Types --}}
                    <li class="menu-item {{ Request::routeIs('master.classifiedAdType.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.classifiedAdType.index') }}" class="menu-link">
                            <div data-i18n="Classified Ad Types">Classified Ad Types</div>
                        </a>
                    </li>

                    {{-- Ad Categories --}}
                    <li class="menu-item {{ Request::routeIs('master.adCategory.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.adCategory.index') }}" class="menu-link">
                            <div data-i18n="Ad Categories">Ad Categories</div>
                        </a>
                    </li>

                    {{-- Ad Submission Threshold --}}
                    <li class="menu-item {{ Request::routeIs('') ? 'open active' : '' }}">
                        <a href="" class="menu-link">
                            <div data-i18n="Ad Submission Threshold">Ad Submission Threshold</div>
                        </a>
                    </li>

                    {{-- Ad Rejection Reasons --}}
                    <li class="menu-item {{ Request::routeIs('master.adRejectionReason.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.adRejectionReason.index') }}" class="menu-link">
                            <div data-i18n="Ad Rejection Reasons">Ad Rejection Reasons</div>
                        </a>
                    </li>

                    {{-- Departments --}}
                    <li class="menu-item {{ Request::routeIs('master.department.*') ? 'open active' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <div data-i18n="Departments">Departments</div>
                        </a>
                        <ul class="menu-sub">

                            {{-- All Departments --}}
                            @php
                                $routesToMatch = [
                                    'master.department.index',
                                    'master.department.show',
                                    'master.department.edit',
                                    'master.department.create',
                                ];
                            @endphp
                            <li class="menu-item {{ Request::routeIs($routesToMatch) ? 'open active' : '' }}">
                                <a href="{{ route('master.department.index') }}" class="menu-link">
                                    <div data-i18n="All">All</div>
                                </a>
                            </li>

                            {{-- Department Categories --}}
                            @php
                                $routesToMatch = [
                                    'master.department.departmentCategory.index',
                                    'master.department.departmentCategory.show',
                                    'master.department.departmentCategory.edit',
                                    'master.department.departmentCategory.create',
                                ];
                            @endphp
                            <li class="menu-item {{ Request::routeIs($routesToMatch) ? 'open active' : '' }}">
                                <a href="{{ route('master.department.departmentCategory.index') }}" class="menu-link">
                                    <div data-i18n="Categories">Categories</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Offices --}}
                    <li class="menu-item {{ Request::routeIs('master.office.*') ? 'open active' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <div data-i18n="Offices">Offices</div>
                        </a>
                        <ul class="menu-sub">

                            {{-- All Offices --}}
                            @php
                                $routesToMatch = [
                                    'master.office.index',
                                    'master.office.show',
                                    'master.office.edit',
                                    'master.office.create',
                                ];
                            @endphp
                            <li class="menu-item {{ Request::routeIs($routesToMatch) ? 'open active' : '' }}">
                                <a href="{{ route('master.office.index') }}" class="menu-link">
                                    <div data-i18n="All">All</div>
                                </a>
                            </li>

                            {{-- Office Categories --}}
                            @php
                                $routesToMatch = [
                                    'master.office.officeCategory.index',
                                    'master.office.officeCategory.show',
                                    'master.office.officeCategory.edit',
                                    'master.office.officeCategory.create',
                                ];
                            @endphp
                            <li class="menu-item {{ Request::routeIs($routesToMatch) ? 'open active' : '' }}">
                                <a href="{{ route('master.office.officeCategory.index') }}" class="menu-link">
                                    <div data-i18n="Categories">Categories</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Provinces --}}
                    <li class="menu-item {{ Request::routeIs('master.province.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.province.index') }}" class="menu-link">
                            <div data-i18n="Provinces">Provinces</div>
                        </a>
                    </li>

                    {{-- Districts --}}
                    <li class="menu-item {{ Request::routeIs('master.district.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.district.index') }}" class="menu-link">
                            <div data-i18n="Districts">Districts</div>
                        </a>
                    </li>

                    {{-- Languages --}}
                    <li class="menu-item {{ Request::routeIs('master.language.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.language.index') }}" class="menu-link">
                            <div data-i18n="Languages">Languages</div>
                        </a>
                    </li>

                    {{-- Status --}}
                    <li class="menu-item {{ Request::routeIs('master.status.*') ? 'open active' : '' }}">
                        <a href="{{ route('master.status.index') }}" class="menu-link">
                            <div data-i18n="Status">Status</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endrole

        <!-- User Management -->
        @role(['Super Admin'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Roles &amp; Permissions</span>
            </li>
            <li class="menu-item {{ Request::routeIs('userManagement.*') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div data-i18n="User Management">User Management</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Request::routeIs('userManagement.user.*') ? 'open active' : '' }}">
                        <a href="{{ route('userManagement.user.index') }}" class="menu-link">
                            <div data-i18n="Users">Users</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::routeIs('userManagement.role.*') ? 'open active' : '' }}">
                        <a href="{{ route('userManagement.role.index') }}" class="menu-link">
                            <div data-i18n="Roles">Roles</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::routeIs('userManagement.permission.*') ? 'open active' : '' }}">
                        <a href="{{ route('userManagement.permission.index') }}" class="menu-link">
                            <div data-i18n="Permissions">Permissions</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::routeIs('master.taxPayee.index') ? 'open active' : '' }}">
                        <a href="#" class="menu-link">
                            <div data-i18n="Password Reset Request">Password Reset Request</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::routeIs('master.taxPayee.index') ? 'open active' : '' }}">
                        <a href="#" class="menu-link">
                            <div data-i18n="User Log">User Log</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endrole

        <!-- Digital Assets -->
        @can('View digital assets')
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Support Section</span></li>
            {{--  Digital Assets --}}
            <li class="menu-item">
                <a href="" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-cloud"></i>
                    <div data-i18n=" Digital Assets"> Digital Assets</div>
                </a>
            </li>
        @endcan

        <!-- Support Section -->
        @can('view support')
            <li class="menu-item">
                <a href="https://pixinvent.ticksy.com/" target="_blank" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-support"></i>
                    <div data-i18n="Support">Support</div>
                </a>
            </li>
        @endcan

        <!-- Documentation -->
        @can('view documentation')
            <li class="menu-item">
                <a href="https://pixinvent.com/demo/frest-clean-bootstrap-admin-dashboard-template/documentation-bs5/"
                    target="_blank" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Documentation">Documentation</div>
                </a>
            </li>
        @endcan
    </ul>
</aside>
<!-- / sidebar Menu -->

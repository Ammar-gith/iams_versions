{{-- Navbar content --}}
<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar"
    style="box-shadow: 0px 4px 10px #DCE0DE; !important;">
    <div class="container-fluid">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
            </a>
        </div>

        <div class="navbar-nav-right d-flex align-items-center justify-content-between" id="navbar-collapse">
            <!-- Search -->
            <div class="navbar-nav app-name align-items-center">
                <div class="nav-item navbar-search-wrapper mb-0">
                    <a class="nav-item nav-link search-toggler px-0" href="javascript:void(0);">
                        <i class="bx bx-search-alt bx-sm"></i>
                        <span class="d-none d-md-inline-block text-muted">Search (Ctrl+/)</span>
                    </a>
                </div>
            </div>
            <!-- /Search -->

            <!-- Search Small Screens -->
            <div class="col-md-8 navbar-search-wrapper search-input-wrapper d-none">
                <form action="{{ route('global.search') }}" method="GET" class="w-100">
                    <div class="position-relative">
                        <input type="text" name="q" class="form-control search-input container-fluid border-0"
                            placeholder="Search..." aria-label="Search..." value="{{ request('q') }}">
                        <i
                            class="bx bx-x bx-sm search-toggler cursor-pointer position-absolute top-50 end-0 translate-middle-y me-2"></i>
                    </div>
                </form>
            </div>
            <!-- Search Small Screens -->

            {{-- Application Name --}}
            <div class="navbar-nav app-name align-items-center">
                <div class="nav-item mb-0">
                    <h5 style="margin-bottom: 0; color: #2C6350;" class="d-md-inline-block">Integrated Advertisement
                        Management System <small>&lpar;IAMS&rpar;</small></h5>
                </div>
            </div>

            <!-- User/Notification -->
            <ul class="navbar-nav flex-row align-items-center">
                <!-- Notification -->
                <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                        data-bs-auto-close="outside" aria-expanded="false">
                        <i class="bx bx-bell fs-2"></i>
                        <span
                            class="badge bg-danger rounded-pill badge-notifications">{{ auth()->user()->unreadNotifications->count() }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end py-0">
                        <li class="dropdown-menu-header border-bottom">
                            <div class="dropdown-header d-flex align-items-center py-3">
                                <h5 class="text-body mb-0 me-auto">Notification</h5>
                                <a href="javascript:void(0)" class="dropdown-notifications-all text-body"
                                    id="mark-all-notifications-read" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Mark all as read"><i class="bx fs-4 bx-envelope-open"></i></a>
                            </div>
                        </li>
                        <li class="dropdown-notifications-list scrollable-container"
                            style="max-height: 400px; overflow-y: auto;">
                            <ul class="list-group list-group-flush">
                                @forelse(auth()->user()->unreadNotifications as $notification)
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <span
                                                        class="avatar-initial rounded-circle bg-label-danger">{{ substr($notification->data['title'] ?? 'N', 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $notification->data['title'] ?? 'Notification' }}
                                                </h6>
                                                <p class="mb-0">{{ $notification->data['message'] ?? '' }}</p>
                                                <small
                                                    class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="javascript:void(0)"
                                                    class=" mark-notification-read dropdown-notifications-read"><span
                                                        class="badge badge-dot "></span></a>
                                                <a href="javascript:void(0)" class="dropdown-notifications-archive"
                                                    data-notification-id="{{ $notification->id }}"><span
                                                        class="bx bx-x"></span></a>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li
                                        class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <span class="avatar-initial rounded-circle bg-label-warning"><i
                                                            class="bx bx-info"></i></span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">No new notification found</h6>
                                            </div>

                                        </div>
                                    </li>
                                @endforelse
                            </ul>
                        </li>
                        <li class="dropdown-menu-footer border-top">
                            <a href="{{ route('notifications.index') }}"
                                class="dropdown-item d-flex justify-content-center p-3">
                                View all notifications
                            </a>
                        </li>
                    </ul>
                </li>
                <!--/ Notification -->

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow d-flex gap-2 align-items-end"
                        href="javascript:void(0);" data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="rounded-circle" />
                        </div>
                        @php
                            $user = auth()->user();
                        @endphp
                        {{-- office --}}
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item mb-0">
                                <small style="margin-bottom: 0; color: #2C6350;" class="d-md-inline-block">
                                    {{ $user->name }}
                                    @if (!is_null($user->newspaper_id))
                                        Newspaper
                                    @endif
                                </small>
                            </div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            {{-- <a class="dropdown-item" href="pages-account-settings-account.html"> --}}
                            <div class="d-flex" style="padding: 0.7rem 1rem;">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt
                                            class="rounded-circle" />
                                    </div>
                                </div>
                                @if (auth()->check())
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold d-block lh-1">{{ auth()->user()->name }}</span>
                                        <small>{{ auth()->user()->roles->first()->name }}</small>
                                    </div>
                                @endif
                            </div>
                            {{-- </a> --}}
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.index', $user->id) }}">
                                <i class="bx bx-user me-2"></i>
                                <span class="align-middle">My Profile</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                            this.closest('form').submit();">
                                    <i class="bx bx-power-off me-2"></i>
                                    <span class="align-middle">Log Out</span>
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>
                <!--/ User -->
            </ul>
            <!-- /User/Theme Switcher/Notification -->
        </div>


    </div>
</nav>
<!-- / Navbar -->

<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Login DG-IPR &#x2053; IAMS</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/branding/Favicon.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('assets/img/branding/Favicon.png') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <!-- Vendor -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') }}" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />

    {{-- Toast styles --}}
    <style>
        .toast-msg {
            position: fixed;
            top: 55px;
            right: 20px;
            z-index: 9999;
            padding: 15px 20px;
            color: #fff;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: opacity 0.5s ease-in-out;
            font-weight: 500;
            max-width: 300px;
        }

        .toast-msg.bg-success {
            background-color: rgba(69, 154, 125, .8) !important;
        }

        .toast-msg.bg-danger {
            background-color: rgba(196, 71, 71, .8) !important;
        }
    </style>

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    {{-- Toast / Feedback Messages --}}
    @if (session('status') || session('success'))
        <div id="toastSuccess" class="toast-msg bg-success">
            {{ session('status') ?? session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div id="toastError" class="toast-msg bg-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div id="toastError" class="toast-msg bg-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Content -->
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <!-- Register -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <span class="">
                                <img width="65" height="60"
                                    src={{ asset('assets/img/branding/Favicon.png') }} />
                            </span>
                            {{-- </a> --}}
                        </div>
                        <!-- /Logo -->
                        <h3 class="mb-2 text-center" style="color: #245142;">Integrated Advertisement Management System
                        </h3>
                        <p class="mb-4 text-center">Sign-in to your account and start the adventure</p>

                        <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Username or Email</label>
                                <input type="text" class="form-control" id="email" name="email"
                                    placeholder="Enter your username" autofocus />
                            </div>
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label fw-bold" for="password">Password</label>
                                    <a href="{{ route('password-reset.form') }}">
                                        <small>Request Password Reset</small>
                                    </a>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password-toggle" />
                                    <span class="input-group-text cursor-pointer" id="toggle-password">
                                        <i class="bx bx-hide" id="toggle-icon"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
                            </div>
                        </form>

                    </div>
                </div>
                <!-- /Register -->
            </div>
        </div>
    </div>
    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets/js/pages-auth.js') }}"></script>

    <!-- Show/Hide Password JS-->
    <script>
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggle-icon');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icon classes
            toggleIcon.classList.toggle('bx-hide');
            toggleIcon.classList.toggle('bx-show');
        });
    </script>

    {{-- Feedback Message JS --}}
    <script>
        ;
        ['toastSuccess', 'toastError'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            setTimeout(() => {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }, 3500);
        });
    </script>

</body>

</html>

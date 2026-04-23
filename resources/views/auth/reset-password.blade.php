<!DOCTYPE html>
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../assets/"
  data-template="vertical-menu-template"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

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
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="../../assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="../../assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="../../assets/vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../../assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../../assets/vendor/libs/typeahead-js/typeahead.css" />
    <!-- Vendor -->
    <link rel="stylesheet" href="../../assets/vendor/libs/formvalidation/dist/css/formValidation.min.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="../../assets/vendor/css/pages/page-auth.css" />
    <!-- Helpers -->
    <script src="../../assets/vendor/js/helpers.js"></script>

    <script src="../../assets/vendor/js/template-customizer.js"></script>
    <script src="../../assets/js/config.js"></script>
  </head>

    <body>
    <!-- Content -->
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <!-- Reset Password -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <span class="">
                                <img width="65" height="60" src={{ asset('assets/img/branding/Favicon.png') }} />
                            </span>
                            {{-- </a> --}}
                        </div>
                        <!-- /Logo -->
                        <h3 class="mb-1 text-center" style="color: #245142;">Integrated Advertisement Management System</h3>
                        <p class="mb-2 text-center" style="color: #E4BF61; font-style: italic">Provide password and confirm password to reset</p>

                        <form method="POST" action="{{ route('password.store') }}">
                            @csrf

                            <!-- Password Reset Token -->
                            <input type="hidden" name="token" value="{{ $token }}">

                            <!-- Email Address -->
                            <div class="mb-2">
                                <x-input-label for="email" :value="__('Email')" class="form-label fw-bold" />
                                <x-text-input id="email" class="form-control" type="email" name="email" :value="old('email', $email)" required readonly />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div class="mb-2">
                                <x-input-label for="password" :value="__('Password')" class="form-label fw-bold" />
                                <div class="input-group input-group-merge">
                                    <x-text-input id="password" class="form-control" type="password" name="password" required autocomplete="new-password" aria-describedby="password-toggle" />
                                    <span class="input-group-text cursor-pointer" id="toggle-password1">
                                        <i class="bx bx-hide" id="toggle-icon1"></i>
                                    </span>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-2">
                                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="form-label fw-bold" />

                                <div class="input-group input-group-merge">
                                    <x-text-input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" aria-describedby="password-toggle" />
                                    <span class="input-group-text cursor-pointer" id="toggle-password2">
                                        <i class="bx bx-hide" id="toggle-icon2"></i>
                                    </span>
                                </div>

                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-primary d-grid w-100" type="submit">{{ __('Reset Password') }}</button>
                            </div>

                            <div class="text-center mt-3">
                                <a href="{{ route('dashboard') }}">Back to Dashboard</a>
                            </div>
                        </form>

                    </div>
                </div>
                <!-- /Reset Password -->
            </div>
        </div>
    </div>
    <!-- / Content -->

<!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="../../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../../assets/vendor/libs/hammer/hammer.js"></script>

    <script src="../../assets/vendor/libs/i18n/i18n.js"></script>
    <script src="../../assets/vendor/libs/typeahead-js/typeahead.js"></script>

    <script src="../../assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="../../assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js"></script>
    <script src="../../assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js"></script>
    <script src="../../assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js"></script>

    <!-- Main JS -->
    <script src="../../assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="../../assets/js/pages-auth.js"></script>

    <!-- Show/Hide Password JS-->
    <script>
        const passwordInput = document.getElementById('password');
        const passwordInputConf = document.getElementById('password_confirmation');

        const togglePassword1 = document.getElementById('toggle-password1');
        const togglePassword2 = document.getElementById('toggle-password2');

        const toggleIcon1 = document.getElementById('toggle-icon1');
        const toggleIcon2 = document.getElementById('toggle-icon2');

        togglePassword1.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            toggleIcon1.classList.toggle('bx-hide');
            toggleIcon1.classList.toggle('bx-show');
        });

        togglePassword2.addEventListener('click', () => {
            const type = passwordInputConf.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInputConf.setAttribute('type', type);

            toggleIcon2.classList.toggle('bx-hide');
            toggleIcon2.classList.toggle('bx-show');
        });
    </script>

  </body>
</html>

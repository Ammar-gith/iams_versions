<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-style layout-navbar-fixed layout-menu-fixed"
    dir="ltr" data-theme="theme-default" data-assets-path="../../assets/" data-template="vertical-menu-template">

{{-- Head --}}
@include('components.head')

{{-- Body --}}

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Menu -->
            @include('components.sidebar')

            <!-- Layout container -->
            <div class="layout-page">

                <!-- Navbar -->
                @include('components.navbar')

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <div class="container-xxl flex-grow-1 container-p-y">
                        @stack('content')
                    </div>

                    {{-- Footer --}}
                    @include('components.footer')

                    <div class="content-backdrop fade"></div>

                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        {{-- Password Reset Modal (outside navbar) --}}
        @include('components.password-reset-modal')

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    {{-- scripts --}}
    {{-- Actions Tooltip --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                new bootstrap.Tooltip(el);
            });
        });
    </script>

    @include('components.scripts')

    {{-- Feeback Message --}}
    @if (session('success') || session('danger'))
        <div id="toastMessage" class="toast-msg {{ session('success') ? 'bg-success' : 'bg-danger' }}">
            {{ session('success') ?? session('danger') }}
        </div>
    @endif

    {{-- Feedback JS --}}
    <script>
        const toast = document.getElementById('toastMessage');
        if (toast) {
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500); // Remove from DOM after fade
            }, 3500); // 3.5 seconds
        }

        // Mark all notifications as read handler
        $(document).on('click', '#mark-all-notifications-read', function(e) {
            e.preventDefault();

            fetch('/notifications/mark-all-as-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Show success toast notification
                    const toast = document.createElement('div');
                    toast.className = 'toast-msg bg-success';
                    toast.textContent = data.message;
                    toast.style.position = 'fixed';
                    toast.style.top = '20px';
                    toast.style.right = '20px';
                    toast.style.zIndex = '9999';
                    document.body.appendChild(toast);

                    // Remove toast after 3.5 seconds
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 500);
                    }, 3500);

                    // Optionally refresh the page or update UI
                    // location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });

        // Mark single notification as read handler
        $(document).on('click', '.mark-notification-read', function(e) {
            e.preventDefault();

            const notificationId = $(this).data('notification-id');
            const notificationItem = $(this).closest('.dropdown-notifications-item');

            fetch(`/notifications/mark-as-read/${notificationId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Remove the notification from the dropdown
                    notificationItem.fadeOut(300, function() {
                        $(this).remove();
                    });

                    // Update notification count if needed
                    const notificationCount = $('.dropdown-notifications-list .list-group-item').length;
                    if (notificationCount === 0) {
                        $('.dropdown-notifications-list').html(
                            '<li class="text-center p-3">No new notifications</li>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    </script>
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get elements
            const searchToggler = document.querySelector('.search-toggler');
            const searchWrapper = document.querySelector('.search-input-wrapper');

            if (searchToggler && searchWrapper) {
                // Toggle search wrapper on click of the search icon (the first .search-toggler)
                searchToggler.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchWrapper.classList.toggle('d-none');
                    if (!searchWrapper.classList.contains('d-none')) {
                        // Focus the input when opened
                        searchWrapper.querySelector('input').focus();
                    }
                });

                // Close when clicking the close icon (the .search-toggler inside the wrapper)
                const closeIcon = searchWrapper.querySelector('.search-toggler');
                if (closeIcon) {
                    closeIcon.addEventListener('click', function(e) {
                        e.preventDefault();
                        searchWrapper.classList.add('d-none');
                    });
                }

                // Optional: Close when clicking outside the wrapper
                document.addEventListener('click', function(event) {
                    if (!searchWrapper.classList.contains('d-none') &&
                        !searchWrapper.contains(event.target) &&
                        !searchToggler.contains(event.target)) {
                        searchWrapper.classList.add('d-none');
                    }
                });
            }
        });
    </script> --}}
    <script>
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === '/') {
                e.preventDefault();
                const searchWrapper = document.querySelector('.search-input-wrapper');
                const searchToggler = document.querySelector('.search-toggler');
                if (searchWrapper && searchWrapper.classList.contains('d-none')) {
                    // If hidden, trigger the toggler click
                    searchToggler.click();
                } else if (searchWrapper && !searchWrapper.classList.contains('d-none')) {
                    // If visible, focus the input
                    searchWrapper.querySelector('input').focus();
                }
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            // Selectors
            const $appNameContainer = $('.app-name').closest(
                '.navbar-nav'); // The div containing the app name
            const $searchWrapper = $('.search-input-wrapper'); // The hidden search bar container

            // Search icon (the one that triggers the search bar)
            $('.navbar-search-wrapper .search-toggler').on('click', function(e) {
                e.preventDefault();
                // Only act if the search bar is currently hidden
                if ($searchWrapper.hasClass('d-none')) {
                    $appNameContainer.addClass('d-none'); // Hide app name
                    $searchWrapper.removeClass('d-none'); // Show search bar
                    $searchWrapper.find('input').focus(); // Focus the input field
                }
            });

            // Close icon inside the search bar
            $('.search-input-wrapper .search-toggler').on('click', function(e) {
                e.preventDefault();
                $searchWrapper.addClass('d-none'); // Hide search bar
                $appNameContainer.removeClass('d-none'); // Show app name
            });
        });
    </script>
</body>

</html>

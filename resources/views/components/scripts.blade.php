<!-- Core JS -->
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
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}"></script>

<!-- Page JS -->
<script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

<!-- Vendors JS -->
{{-- <script src="{{ asset('assets/vendor/libs/cleavejs/cleave.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/cleavejs/cleave-phone.js') }}"></script> --}}
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
{{-- <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script> --}}
<script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>

{{-- flatpicr for customize date --}}
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

<!-- Page JS -->

<script src="{{ asset('assets/js/modal-edit-user.js') }}"></script>
<!-- Vendors JS -->
<script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script>

{{-- Include Chart JS --}}
<script src="{{ asset('assets/js/monthly-ads-chart.js') }}"></script>

<!-- Page JS -->
{{-- <script src="{{ asset('assets/js/forms-file-upload.js') }}"></script> --}}

{{--  --}}


@stack('scripts')

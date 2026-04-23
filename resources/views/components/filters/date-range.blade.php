  {{-- Search & Filteration --}}
  <form method="GET" id="dateRangeForm" action="{{ route($route) }}" class="row g-2 mb-3">

      <div class="col-md-4">
          <input type="date" name="from" id="fromDate" class="form-control rounded-pill" value="{{ $from ?? '' }}"
              placeholder="DD-MM-YYYY">
      </div>

      <div class="col-md-4">
          <input type="date" name="to" id="toDate" class="form-control rounded-pill"
              value="{{ $to ?? '' }}" placeholder="DD-MM-YYYY">
      </div>

      <div class="col-md-4 d-flex">
          <button type="button" class="btn btn-success rounded-pill me-2" onclick="applyFilter()">Filter</button>
          <a href="javascript:window.location.href=window.location.pathname"
              class="btn btn-outline-warning rounded-pill">Reset</a>
      </div>
  </form>

  @push('scripts')
      <script>
          function applyFilter() {
              const fromDate = document.getElementById('fromDate').value;
              const toDate = document.getElementById('toDate').value;

              if (!fromDate || !toDate) {
                  alert('Please select both from and to dates');
                  return false;
              }

              // Submit the form
              document.getElementById('dateRangeForm').submit();
          }

          flatpickr("#fromDate", {
              altInput: true,
              altFormat: "d-m-Y",
              dateFormat: "Y-m-d",
              defaultDate: "{{ request()->get('from') ?? '' }}"
          });

          flatpickr("#toDate", {
              altInput: true,
              altFormat: "d-m-Y",
              dateFormat: "Y-m-d",
              defaultDate: "{{ request()->get('to') ?? '' }}"
          });
      </script>
  @endpush

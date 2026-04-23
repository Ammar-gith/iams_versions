<h5>Classified Ads</h5>
<table class="table table-bordered">
  <thead>
    <tr>
      <th>Title</th>
      <th>Status</th>
      <th>Cost</th>
      <th>Created At</th>
    </tr>
  </thead>
  <tbody>
    {{-- @foreach($ads as $ad)
      <tr>
        <td>{{ $ad->title }}</td>
        <td>{{ $ad->status }}</td>
        <td>{{ $ad->estimated_cost ?? 'N/A' }}</td>
        <td>{{ $ad->created_at->format('d M Y') }}</td>
      </tr>
    @endforeach --}}
  </tbody>
</table>

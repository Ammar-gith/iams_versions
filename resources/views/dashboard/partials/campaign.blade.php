<h5>Campaign Ads</h5>
<table class="table table-bordered">
  <thead>
    <tr>
      <th>Title</th>
      <th>Status</th>
      <th>Budget</th>
      <th>Launched On</th>
    </tr>
  </thead>
  <tbody>
    {{-- @foreach($ads as $ad)
      <tr>
        <td>{{ $ad->title }}</td>
        <td>{{ $ad->status }}</td>
        <td>{{ $ad->budget ?? 'N/A' }}</td>
        <td>{{ $ad->created_at->format('d M Y') }}</td>
      </tr>
    @endforeach --}}
  </tbody>
</table>

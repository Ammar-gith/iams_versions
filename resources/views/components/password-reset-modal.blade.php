{{-- Password Reset Requests Modal --}}
<div class="modal fade" id="passwordRequestsModal" tabindex="-1" aria-labelledby="passwordRequestsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="padding: .8rem 1.8rem; border-bottom: .13rem solid #e7e7e7;">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title" id="passwordRequestsModalLabel">Password Reset Requests</h5>
                    <div>
                        {{-- @php $len = strlen((string) $requestsCount); @endphp --}}
                        {{-- <span class="badge bg-warning rounded-pill digits-{{ $len }}">{{ $requestsCount }}</span> --}}
                    </div>
                </div>
                <div class="x-hover">
                    <button type="button" class="button-x" data-bs-dismiss="modal" aria-label="Close">
                        <i class='bx bx-x bx-modal-icons'></i>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <div class="info-card bg-white rounded">
                    {{-- @forelse($requests as $request)
                        <a href="{{ route('password.reset', ['token' => $request->token, 'email' => $request->user->email]) }}"
                           class="d-flex justify-content-between align-items-center inner-card">
                            <span class="info-badge">{{ $request->user->name }}</span>
                            <small>{{ $request->created_at->format('d M Y') }}</small>
                        </a>
                    @empty
                        <p class="text-muted mb-0">No password reset requests.</p>
                    @endforelse --}}
                </div>
            </div>
        </div>
    </div>
</div>

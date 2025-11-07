@extends('layouts.app')
@section('title', 'Reallocation Details')

@section('content')
    <div class="container my-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-random me-2 text-primary"></i> Reallocation Details
            </h5>
            <a href="{{ route('budgetandanalytics.reallocation.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="row g-3">
            <!-- Left Column -->
            <div class="col-lg-4">
                <div class="card shadow-sm rounded-3">
                    <div class="card-header bg-light fw-bold">
                        <i class="fas fa-info-circle me-1 text-muted"></i> Basic Information
                    </div>
                    <div class="card-body small">
                        <p><strong>Budget:</strong> {{ $realloc->budget->Name ?? '—' }}</p>
                        <p><strong>Branch:</strong> {{ $realloc->branch->Name ?? '—' }}</p>
                        <p><strong>Department:</strong> {{ $realloc->department->Name ?? '—' }}</p>
                        <p><strong>Type:</strong> {{ $realloc->ReallocationType }}</p>
                        <p><strong>Amount:</strong> <span
                                class="fw-bold text-primary">{{ number_format($realloc->Amount,2) }}</span></p>

                        @php
                            $badgeClass = match(strtolower($realloc->Status)) {
                                'approved' => 'bg-success',
                                'pending'  => 'bg-warning text-dark',
                                'rejected' => 'bg-danger',
                                default    => 'bg-secondary'
                            };
                            $icon = match(strtolower($realloc->Status)) {
                                'approved' => 'fas fa-check-circle',
                                'pending'  => 'fas fa-hourglass-half',
                                'rejected' => 'fas fa-times-circle',
                                default    => 'fas fa-question-circle'
                            };
                        @endphp

                        <p><strong>Status:</strong>
                            <span class="badge {{ $badgeClass }}">
                            <i class="{{ $icon }} me-1"></i> {{ ucfirst($realloc->Status) }}
                        </span>
                        </p>

                        @if($realloc->ApprovalReason)
                            <p><strong>Reason:</strong> {{ $realloc->ApprovalReason }}</p>
                        @endif
                        <p><strong>Created:</strong> {{ optional($realloc->CreatedOn)->format('Y-m-d H:i') }}</p>
                        <p>
                            <strong>Approved:</strong> {{ optional($realloc->ApprovedOn)->format('Y-m-d H:i') ?? 'Pending' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-8">
                <div class="card shadow-sm rounded-3 mb-3">
                    <div class="card-header bg-light fw-bold">
                        <i class="fas fa-random me-1 text-muted"></i> Reallocation Lines
                    </div>
                    <div class="card-body row">
                        <div class="col-md-6">
                            <h6 class="text-primary"><i class="fas fa-arrow-left me-1"></i> From Line</h6>
                            <p class="mb-0">{{ $realloc->fromLine->LineName ?? '—' }}</p>
                            <small class="text-muted">{{ $realloc->fromLine->department->Name ?? '—' }}</small>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success"><i class="fas fa-arrow-right me-1"></i> To Line</h6>
                            <p class="mb-0">{{ $realloc->toLine->LineName ?? '—' }}</p>
                            <small class="text-muted">{{ $realloc->toLine->department->Name ?? '—' }}</small>
                        </div>
                    </div>
                </div>

                <!-- Budget Limits Two Column -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm rounded-3">
                            <div class="card-header bg-primary text-white fw-bold">
                                <i class="fas fa-minus-circle me-1"></i> From Line Limits
                            </div>
                            <div class="card-body p-2">
                                @if($fromLimits->count())
                                    <ul class="list-group list-group-flush small">
                                        @foreach($fromLimits as $lim)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                {{ \Carbon\Carbon::parse($lim->EffectiveFrom)->format('F') }}
                                                <span
                                                    class="fw-bold text-danger">{{ number_format($lim->LimitAmount,2) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted mb-0">No limits.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm rounded-3">
                            <div class="card-header bg-success text-white fw-bold">
                                <i class="fas fa-plus-circle me-1"></i> To Line Limits
                            </div>
                            <div class="card-body p-2">
                                @if($toLimits->count())
                                    <ul class="list-group list-group-flush small">
                                        @foreach($toLimits as $lim)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                {{ \Carbon\Carbon::parse($lim->EffectiveFrom)->format('F') }}
                                                <span
                                                    class="fw-bold text-success">{{ number_format($lim->LimitAmount,2) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted mb-0">No limits.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-3 d-flex gap-2">
            @if(strtolower($realloc->Status) === 'pending')
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#decisionModal"
                        data-action="approve">
                    <i class="fas fa-check me-1"></i> Approve
                </button>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#decisionModal"
                        data-action="reject">
                    <i class="fas fa-times me-1"></i> Reject
                </button>
            @endif
        </div>
    </div>

    <!-- Decision Modal -->
    <div class="modal fade" id="decisionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg rounded-3">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="fas fa-gavel me-2 text-primary"></i> Decision
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="decisionForm" method="POST" action="#">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="ApprovalReason" class="form-label">Reason</label>
                            <textarea class="form-control" name="ApprovalReason" id="ApprovalReason" rows="3"
                                      placeholder="Optional for approve, required for reject"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-ban me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="decisionSubmitBtn">
                            <i class="fas fa-paper-plane me-1"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const modalEl = document.getElementById('decisionModal');
        const form = document.getElementById('decisionForm');
        const submitBtn = document.getElementById('decisionSubmitBtn');

        modalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const title = modalEl.querySelector('.modal-title');

            if (action === 'approve') {
                form.action = "{{ route('budgetandanalytics.reallocation.approve', ['id' => $realloc->id]) }}";
                title.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i> Approve Reallocation';
                submitBtn.className = 'btn btn-success';
                submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Approve';
            } else {
                form.action = "{{ route('budgetandanalytics.reallocation.reject', ['id' => $realloc->id]) }}";
                title.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i> Reject Reallocation';
                submitBtn.className = 'btn btn-danger';
                submitBtn.innerHTML = '<i class="fas fa-times me-1"></i> Reject';
            }
        });

        // Button disable + spinner on submit
        form.addEventListener('submit', function (e) {
            if (form.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Please wait...';
            }
        });
    </script>
@endsection

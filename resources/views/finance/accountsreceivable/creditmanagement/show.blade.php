@extends('layouts.app')
@section('title','Credit Profile')

@section('content')
    <div class="container my-3">
        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-exclamation-triangle me-1"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-2">
            <a href="{{ route('creditmanagement.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <div class="d-flex gap-2">
                @php $isApproved = strtolower($credit->Status ?? '') === 'approved'; @endphp
                @if($isApproved)
                    <a href="{{ route('creditadjustment.create', $credit->Id) }}"
                       class="btn btn-sm btn-outline-success">
                        <i class="fas fa-plus me-1"></i> Add Credit Adjustment
                    </a>
                @else
                    <a href="{{ route('creditmanagement.edit', $credit->Id) }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                @endif
                <a href="{{ route('creditmanagement.history', $credit->Id) }}" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-history me-1"></i> View History
                </a>
                <button class="btn btn-sm btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>

        <div id="printRoot" class="card shadow-sm rounded-4 border-0 p-3 p-md-4">
            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start">
                <div>
                    <div class="h5 mb-0">Credit Profile — <span
                            class="fw-semibold">{{ $credit->customer->ThirdPartyName ?? '-' }}</span></div>
                    <div class="small text-muted">ID: {{ $credit->customer->RegistrationNumber ?? '-' }}
                        • {{ $credit->customer->Email ?? '-' }}</div>
                </div>
                <div class="text-md-end mt-2 mt-md-0">
                    <div class="small text-muted">Status</div>
                    @php
                        $status = strtolower($credit->Status ?? 'pending');
                        $statusClass = match($status) {
                            'approved' => 'bg-success',
                            'rejected' => 'bg-danger',
                            'pending' => 'bg-warning text-dark',
                            default => 'bg-warning text-dark'
                        };
                    @endphp
                    <div><span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span></div>
                    <div class="small text-muted mt-2">Last Review: {{ $credit->ExpiryDate ?? '-' }}</div>
                </div>
            </div>

            <!-- KPIs -->
            <div class="row g-3 mt-3">
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Credit Limit</div>
                        <div class="fs-5 fw-semibold">KSh {{ number_format($credit->CreditLimit,2) }}</div>
                        <div class="small text-muted">Terms: {{ $credit->PaymentTerms }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Used</div>
                        <div class="fs-5 fw-semibold text-danger">KSh {{ number_format($used,2) }}</div>
                        <div class="small text-muted">Open AR</div>
                        <div class="text-muted mt-1" style="font-size: 0.65rem;">
                            <i class="fas fa-info-circle"></i>
                            From {{ $credit->EffectiveFrom ? \Carbon\Carbon::parse($credit->EffectiveFrom)->format('M d, Y') : 'N/A' }}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Available</div>
                        <div class="fs-5 fw-semibold text-success">KSh {{ number_format($available,2) }}</div>
                        <div class="small text-muted">As of {{ now()->format('Y-m-d') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Risk Assessment</div>
                        <div>
                            <span
                                class="badge {{ $credit->risk_badge_class }}">{{ $credit->RiskLevel ?? 'Medium' }}</span>
                            <span class="small text-muted">(Score {{ $credit->RiskScore ?? 50 }})</span>
                        </div>
                        <div class="small text-muted mt-1">Review Cycle: {{ $credit->ReviewCycleMonths ?? 6 }}months
                        </div>
                        @if($credit->NextReviewDate)
                            <div class="small {{ $credit->isReviewDue() ? 'text-danger' : 'text-muted' }} mt-1">
                                Next Review: {{ $credit->NextReviewDate->format('M d, Y') }}
                                @if($credit->isReviewDue())
                                    <i class="fas fa-exclamation-triangle ms-1" title="Review Overdue"></i>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Utilization bar -->
            <div class="mt-3">
                <div class="d-flex justify-content-between">
                    <div class="small text-muted">Utilization</div>
                    <div class="small text-muted">{{ number_format($util,2) }}%</div>
                </div>
                <div class="progress" style="height:10px;">
                    <div
                        class="progress-bar {{ $util < 50 ? 'bg-success' : ($util < 80 ? 'bg-warning' : 'bg-danger') }}"
                        style="width: {{ $util }}%"></div>
                </div>
            </div>

            <!-- Collateral / Notes -->
            <div class="row g-3 mt-3">
                <div class="col-lg-12">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small text-uppercase mb-2">Collateral / Notes</div>
                        <div class="small">Collateral: {{ $credit->Colleteral }}</div>
                        <div class="small mt-2">Remarks: {{ $credit->Remarks }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent credit events -->
            <div class="card border-0 shadow-sm rounded-4 mt-3">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-2">Recent Events</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Event</th>
                                <th>Ref</th>
                                <th class="text-end">Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $events = $credit->movements()->orderByDesc('EffectiveOn')->limit(15)->get();
                            @endphp
                            @forelse($events as $ev)
                                <tr>
                                    <td>{{ optional($ev->EffectiveOn)->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td>{{ strtoupper(str_replace('_',' ', $ev->MovementType)) }}</td>
                                    <td>{{ $ev->ReferenceType }} #{{ $ev->ReferenceID }}</td>
                                    <td class="text-end">{{ number_format($ev->Amount,2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted">No events yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Approval Action Buttons --}}
            @if(strtolower($credit->Status ?? '') === 'pending')
                <div class="mt-4 d-flex justify-content-end gap-3">
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#actionRejectModal"
                            data-action="reject">
                        <i class="fas fa-times-circle me-1"></i> Reject
                    </button>
                    <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#actionApproveModal"
                            data-action="approve">
                        <i class="fas fa-check-circle me-1"></i> Approve
                    </button>
                </div>
            @endif

        </div>
    </div>

    {{-- Approval Modals --}}
    @if(strtolower($credit->Status ?? '') === 'pending')
        {{-- Approve Modal --}}
        <div class="modal fade" id="actionApproveModal" tabindex="-1" aria-labelledby="approveModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('creditmanagement.approve', $credit->Id) }}">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="action_type" value="approve">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-success" id="approveModalLabel">Confirm Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Are you sure you want to approve this credit profile for
                                <strong>{{ $credit->customer->ThirdPartyName }}</strong>?</p>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Approval Reason</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required
                                          placeholder="Enter approval reason..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-success" type="submit"
                                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                                Approve
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="actionRejectModal" tabindex="-1" aria-labelledby="rejectModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('creditmanagement.approve', $credit->Id) }}">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="action_type" value="reject">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-danger" id="rejectModalLabel">Confirm Rejection</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Are you sure you want to reject this credit profile for
                                <strong>{{ $credit->customer->ThirdPartyName }}</strong>?</p>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Rejection Reason</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required
                                          placeholder="Enter rejection reason..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="submit"
                                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                                Reject
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('styles')
    <style>
        :root { --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif; }
        body, .card, .table { font-family: var(--font-sans); }
        .card { border: none; }
        @media print {
            body * { visibility: hidden; }
            #printRoot, #printRoot * { visibility: visible; }
            #printRoot { position: absolute; left: 0; top: 0; width: 100%; }
            @page { size: A4 portrait; margin: 14mm; }
            .btn, .navbar { display:none !important; }
            .shadow-sm { box-shadow: none !important; }
        }
    </style>
@endsection

@section('scripts')
    <script>
        // (Optional) You can add small interactivity here later if needed.
    </script>
@endsection

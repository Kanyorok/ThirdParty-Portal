@extends('layouts.app')
@section('title','Credit Adjustment Details')

@section('content')
<div class="container my-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <a href="{{ route('creditadjustment.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <div class="d-flex gap-2">
            @if($adjustment->isPending())
                <a href="{{ route('creditadjustment.edit', $adjustment->Id) }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            @endif
            <button class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <div id="printRoot" class="card shadow-sm rounded-4 border-0 p-3 p-md-4">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start">
            <div>
                <div class="h5 mb-0">Credit Adjustment — <span class="fw-semibold">{{ $adjustment->customer->ThirdPartyName ?? '-' }}</span></div>
                <div class="small text-muted">Adjustment #{{ $adjustment->Id }} • {{ $adjustment->getAdjustmentTypeLabel() }}</div>
            </div>
            <div class="text-md-end mt-2 mt-md-0">
                <div class="small text-muted">Status</div>
                <div><span class="badge {{ $adjustment->getStatusBadgeClass() }}">{{ ucfirst($adjustment->ApprovalStatus) }}</span></div>
                <div class="small text-muted mt-2">Created: {{ $adjustment->CreatedOn->format('M d, Y') }}</div>
                @if($adjustment->isApproved())
                    <div class="small text-muted">Approved: {{ $adjustment->ApprovedOn->format('M d, Y') }}</div>
                @endif
            </div>
        </div>

        <hr class="my-4">

        <!-- Adjustment Details -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted mb-3">Adjustment Information</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Type:</td>
                        <td>
                            @php
                                $typeClass = match($adjustment->AdjustmentType) {
                                    'increase' => 'text-success',
                                    'decrease' => 'text-danger',
                                    'revision' => 'text-info',
                                    default => 'text-muted'
                                };
                                $typeIcon = match($adjustment->AdjustmentType) {
                                    'increase' => 'fas fa-arrow-up',
                                    'decrease' => 'fas fa-arrow-down',
                                    'revision' => 'fas fa-edit',
                                    default => 'fas fa-question'
                                };
                            @endphp
                            <span class="{{ $typeClass }}">
                                <i class="{{ $typeIcon }} me-1"></i>
                                {{ $adjustment->getAdjustmentTypeLabel() }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Amount:</td>
                        <td class="fw-medium">KES {{ number_format($adjustment->Amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">New Credit Limit:</td>
                        <td class="fw-medium">KES {{ number_format($adjustment->NewCreditLimit, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Effective From:</td>
                        <td>{{ $adjustment->EffectiveFrom->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Reference Type:</td>
                        <td>{{ ucwords(str_replace('_', ' ', $adjustment->ReferenceType)) }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-3">Credit Profile</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Customer:</td>
                        <td class="fw-medium">{{ $adjustment->creditProfile->customer->ThirdPartyName ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Registration:</td>
                        <td>{{ $adjustment->creditProfile->customer->RegistrationNumber ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Current Limit:</td>
                        <td class="fw-medium">KES {{ number_format($adjustment->creditProfile->CreditLimit, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Requested By:</td>
                        <td>{{ $adjustment->requestedByUser->Name ?? '-' }}</td>
                    </tr>
                    @if($adjustment->isApproved() || $adjustment->isRejected())
                        <tr>
                            <td class="text-muted">Processed By:</td>
                            <td>{{ $adjustment->approvedByUser->Name ?? '-' }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Reason & Notes -->
        <div class="row mb-4">
            <div class="col-12">
                <h6 class="text-muted mb-3">Reason & Notes</h6>
                <div class="card bg-light border-0 p-3">
                    <div class="mb-2">
                        <strong>Reason:</strong>
                        <p class="mb-2">{{ $adjustment->Reason }}</p>
                    </div>
                    @if($adjustment->Notes)
                        <div class="mb-2">
                            <strong>Additional Notes:</strong>
                            <p class="mb-2">{{ $adjustment->Notes }}</p>
                        </div>
                    @endif
                    @if($adjustment->ApprovalReason)
                        <div>
                            <strong>{{ $adjustment->isApproved() ? 'Approval' : 'Rejection' }} Comments:</strong>
                            <p class="mb-0">{{ $adjustment->ApprovalReason }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Approval Action Buttons --}}
        @if($adjustment->isPending())
            <div class="mt-4 d-flex justify-content-end gap-3">
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#actionRejectModal" data-action="reject">
                    <i class="fas fa-times-circle me-1"></i> Reject
                </button>
                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#actionApproveModal" data-action="approve">
                    <i class="fas fa-check-circle me-1"></i> Approve
                </button>
            </div>
        @endif

    </div>

    {{-- Approval Modals --}}
    @if($adjustment->isPending())
        {{-- Approve Modal --}}
        <div class="modal fade" id="actionApproveModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('creditadjustment.approve', $adjustment->Id) }}">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="action_type" value="approve">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-success" id="actionModalLabel">Confirm Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Are you sure you want to approve this credit adjustment for <strong>{{ $adjustment->customer->ThirdPartyName }}</strong>?</p>
                            <div class="alert alert-info py-2">
                                <strong>This will:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Update the customer's credit limit to KES {{ number_format($adjustment->NewCreditLimit, 2) }}</li>
                                    <li>Create a movement record for audit trail</li>
                                    <li>Make this adjustment non-editable</li>
                                </ul>
                            </div>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Approval Comments</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required placeholder="Enter approval comments..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-success" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">Approve</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="actionRejectModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('creditadjustment.approve', $adjustment->Id) }}">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="action_type" value="reject">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-danger" id="actionModalLabel">Confirm Rejection</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Are you sure you want to reject this credit adjustment for <strong>{{ $adjustment->customer->ThirdPartyName }}</strong>?</p>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Rejection Reason</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required placeholder="Enter rejection reason..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">Reject</button>
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


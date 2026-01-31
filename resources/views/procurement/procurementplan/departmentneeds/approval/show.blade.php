@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'View Procurement Need')

@section('content')
<div class="card shadow rounded-4 p-4">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h3 class="mb-4 text-primary">Needs Details</h3>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Item Name</label>
            <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                {{ $need->item->ItemName ?? 'N/A' }}
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Category</label>
            <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                {{ $need->item->category->Name ?? 'N/A' }}
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Quantity Needed</label>
            <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                {{ $need->RequestedQty }}
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Unit of Measure</label>
            <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                {{ $need->item->uom->Name ?? 'N/A' }}
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Est. Unit Cost</label>
            <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                {{ number_format($need->EstimatedUnitCost, 2, '.', ',') }}
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Expected Delivery Date</label>
            <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                {{ Carbon::parse($need->RequestedDate)->format('d/m/Y') }}
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Submitted On</label>
            <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                {{ Carbon::parse($need->CreatedOn)->format('d/m/Y') }}
            </div>
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold">Justification</label>
            <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                {{ $need->Justification ?? 'N/A' }}
            </div>
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold">Requested By</label>
            <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                {{ $need->creator->Name ?? 'N/A' }}
            </div>
        </div>
    </div>

    @if(!$canApprove && !empty($cantApproveReason))
    <div class="alert alert-warning mt-4 d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>
            <strong>Action Required:</strong> {{ $cantApproveReason }}
        </div>
    </div>
    @endif

    @if(!empty($workflowStatus['hasWorkflow']))
    <div class="card mt-4 border-info">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0 text-white">Workflow Status</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Current Stage:</strong><br>
                    {{ $workflowStatus['currentStage']['name'] ?? 'N/A' }} (Order: {{ $workflowStatus['currentStage']['order'] ?? '-' }})
                </div>
                <div class="col-md-4">
                    <strong>Approvers:</strong><br>
                    <span class="badge bg-success">{{ $workflowStatus['totalCompleted'] }} Approved</span>
                    <span class="badge bg-warning text-dark">{{ $workflowStatus['totalPending'] }} Pending</span>
                </div>
                <div class="col-md-4">
                    <strong>Remaining:</strong><br>
                    {{ max(0, ($workflowStatus['currentStage']['count'] ?? 1) - $workflowStatus['totalCompleted']) }} more approval(s) needed for this stage.
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="rejectForm" method="POST" action="{{ route('department-need-approval.destroy', ['department_need' => $need->Id]) }}">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejectReason">Reason for Rejection</label>
                        <textarea name="reject_reason" id="rejectReason" class="form-control" // Changed from 'Department_needs_reject_reason'
                            required minlength="15" maxlength="2000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    @if($canApprove)
    <!-- Approve Button: Only shown if user can approve (maker-checker check) -->
    <form action="{{ route('department-need-approval.update', ['department_need' => $need->Id]) }}" method="POST" class="d-inline">
        @csrf
        @method('PUT')
        <button type="submit" class="btn btn-success">Approve</button>
    </form>

    <!-- Reject Button: Also conditional for maker-checker -->
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
    @else
    <!-- Disabled buttons for unauthorized users -->
    <button type="button" class="btn btn-success" disabled title="You cannot approve this item">Approve</button>
    <button type="button" class="btn btn-danger" disabled title="You cannot reject this item">Reject</button>
    @endif

    <a href="{{ route('department-need-approval.index') }}" class="btn btn-secondary">
        Back to List
    </a>
</div>
@endsection
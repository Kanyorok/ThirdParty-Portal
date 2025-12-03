@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Requisition Approval')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h3>Requisition Approval</h3>
        </div>

        <div class="card-body">
            <!-- Requisition Summary Section -->
            <div class="row mb-4"></div>
            <div class="col-md-6">
                <h5>Requisition Information</h5>
                <p><strong>Requisition Number:</strong> {{ $requisitionInfo->RequisitionNo ?? 'N/A' }}</p>
                <p>
                    <strong>Date:</strong> {{ isset($requisitionInfo->CreatedOn) ? Carbon::parse($requisitionInfo->CreatedOn)->format('d/m/Y') : '' }}
                </p>
                <p><strong>Branch:</strong> {{ $requisitionInfo->BranchID ?? 'N/A' }}</p>
                <p><strong>Department:</strong> {{ $requisitionInfo->DepartmentID ?? 'N/A' }}</p>
            </div>

            <div class="col-md-6">
                <h5>Additional Details</h5>
                <p><strong>Procurement Plan:</strong> {{ $requisitionInfo->PlanTitle ?? 'N/A' }}</p>
                <p><strong>Status:</strong> {{$requisitionInfo->Status}}</p>
                <p><strong>Requested By:</strong> {{ $requisitionInfo->CreatedBy ?? 'N/A' }}</p>
                <p><strong>Remarks:</strong> {{ $requisitionInfo->Remarks ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Item Type</th>
                        <th>Item Name</th>
                        <th>Need ID</th>
                        <th>UOM</th>
                        <th>Quantity</th>
                        <th>Pricing</th>
                        <th>Urgency</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @foreach($requisitionlineInfo as $index => $item)
                    @php
                    $qty = isset($item->Quantity) ? (float)$item->Quantity : 0;
                    $unitPrice = isset($item->ExpectedPrice) ? (float)$item->ExpectedPrice : 0;
                    $lineTotal = $qty * $unitPrice;
                    $totalAmount += $lineTotal;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->Type ?? 'N/A' }}</td>
                        <td>{{ $item->ItemName ?? 'N/A' }}</td>
                        <td>{{ $item->NeedRef ?? 'N/A' }}</td>
                        <td>{{ $item->UOM }}</td>
                        <td>{{ $item->Quantity }}</td>
                        <td>{{ number_format($unitPrice, 2) }}</td>
                        <td>{{ ucfirst($item->Urgency) }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="7" class="text-end"><b>Total Amount</b></td>
                        <td style="text-align: right;">
                            <b>{{ number_format($totalAmount, 2) }}</b>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Notes Section -->
        @if($requisitionInfo->Remarks)
        <div class="mb-4">
            <h5>Additional Notes</h5>
            <div class="alert alert-info">
                {{ $requisitionInfo->Remarks }}
            </div>
        </div>
        @endif

        <!-- Approval Actions -->
        @if($requisitionInfo->Status == 'pending' || $requisitionInfo->Status == 'Pending')

        @if(!$canApprove)
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            You are not authorized to approve this requisition at the current stage.
        </div>
        @endif

        <div class="row mt-4">
            <div class="col-md-12">
                <form action="{{ route('requisition.approve', $requisitionInfo->Id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    <input type="hidden" name="document_type" value="purchase_requisition">
                    <input type="hidden" name="order_total"
                        value="{{ $requisitionInfo->ExpectedPrice ?? 'N/A' }}">
                    <input type="hidden" name="order_id" value="{{$requisitionInfo->Id}}">
                    <input type="hidden" name="action" value="approve">

                    <div class="form-group mb-2">
                        <label for="approve_comments">Comments (Optional)</label>
                        <textarea class="form-control" id="approve_comments" name="comments" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg" {{ $canApprove ? '' : 'disabled' }}>
                        <i class="fas fa-check"></i> Approve
                    </button>
                </form>

                <button type="button" class="btn btn-danger btn-lg ms-2" data-bs-toggle="modal"
                    data-bs-target="#rejectModal" {{ $canApprove ? '' : 'disabled' }}>
                    <i class="fas fa-times"></i> Reject
                </button>

                <button type="button" class="btn btn-warning btn-lg ml-2" data-bs-toggle="modal"
                    data-bs-target="#statusModal">
                    <i class="fas fa-info-circle"></i> Approval Status
                </button>

            </div>
        </div>
        @else
        <div
            class="alert alert-{{ $requisitionInfo->Status == 'approved' || $requisitionInfo->Status == 'Approved' ? 'success' : 'danger' }}">
            This requisition has already been {{ $requisitionInfo->Status }}.
        </div>
        @endif
    </div>
</div>
</div>

<!-- Approval Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
    aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="statusModalLabel">Approval Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if($approvalStatus['hasWorkflow'])
                <h6>Current Stage: {{ $approvalStatus['currentStage']['name'] ?? 'N/A' }}</h6>
                <hr>

                <h6>Pending Approvers</h6>
                @if(count($approvalStatus['pendingApprovers']) > 0)
                <ul class="list-group mb-3">
                    @foreach($approvalStatus['pendingApprovers'] as $user)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $user->Name }}
                        <span class="badge bg-warning text-dark">Pending</span>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted">No pending approvers for this stage.</p>
                @endif

                <h6>Completed Approvals</h6>
                @if(count($approvalStatus['completedApprovals']) > 0)
                <ul class="list-group">
                    @foreach($approvalStatus['completedApprovals'] as $approval)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $approval->Name }}</span>
                            <span class="badge bg-success">Approved</span>
                        </div>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($approval->CreatedOn)->format('d/m/Y H:i') }}</small>
                        @if($approval->Notes)
                        <br><small class="text-info">Note: {{ $approval->Notes }}</small>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted">No approvals yet.</p>
                @endif
                @else
                <p class="text-muted">No workflow configuration found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel"
    aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Requisition</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('requisition.approve', $requisitionInfo->Id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason">Reason for Rejection</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3"
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <input type="hidden" name="document_type" value="purchase_requisition">
                    <input type="hidden" name="order_total" value="{{ $requisitionInfo->ExpectedPrice ?? '0' }}">
                    <input type="hidden" name="order_id" value="{{ $requisitionInfo->Id }}">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-danger">Submit Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .table th {
        white-space: nowrap;
    }

    .badge {
        font-size: 0.9em;
    }

    .modal .list-group-item {
        font-size: 0.95rem;
    }
</style>
@endsection
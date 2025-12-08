@extends('layouts.app')
@section('title', 'Tender Item Details')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📄 Tender Item Details – {{$tender->TenderNo}}</h4>
        
        <div class="alert alert-info" role="alert" style="background:#eef6ff;border:1px solid #cfe2ff;color:#084298;">
            <i class="fa fa-info-circle me-2"></i>
            <span title="Open: all suppliers can bid. Restricted: only invited based on selected item category. Use 'Add to Grid' to add items.">
                <strong>Guidance:</strong> Tender Initiation supports two types: Open (all suppliers can bid) and Restricted (only invited suppliers based on the selected item category). Add items to the tender by clicking Add to Grid.
            </span>
        </div>
     {{-- Status Display Section --}}
@if($tender->ApprovalStatus === null || $tender->Status === \App\Enums\TenderStatusEnum::Draft)
    {{-- DRAFT STATE: Show info that tender needs to be submitted --}}
    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Draft Tender:</strong> This tender has been created but not yet submitted for approval.
        @if($isSubmitter)
            <br><small>You can submit this tender for approval using the button below.</small>
        @endif
    </div>
    
    {{-- Submit Button (Only for the creator) --}}
    @if($isSubmitter && $tender->Status === \App\Enums\TenderStatusEnum::Draft)
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-paper-plane me-2"></i>Ready to Submit?
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Once you submit this tender for approval, you will no longer be able to edit it until it is approved or rejected.</p>
                
                <form action="{{ route('initiatetender.submit', $tender->Id) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to submit this tender for approval? You will not be able to edit it until it is reviewed.');">
                    @csrf
                    @method('POST')
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Submit for Approval
                    </button>
                    <a href="{{ route('initiatetender.edit', $tender->Id) }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-edit me-2"></i>Edit Tender First
                    </a>
                </form>
            </div>
        </div>
    @endif
    
@elseif($tender->ApprovalStatus == \App\Enums\TenderApprovalStatusEnum::PENDING)
    {{-- PENDING APPROVAL STATE --}}
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-clock me-2"></i>
        <strong>Pending Approval:</strong> This tender is awaiting approval from authorized personnel.
        @if($isSubmitter)
            <br><small class="text-muted">You submitted this tender and cannot approve it yourself.</small>
        @endif
    </div>
    
    {{-- Approval/Rejection Buttons (Only shown if user can approve and tender is pending) --}}
    @if($showApprovalButtons)
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-gavel me-2"></i>Approval Required
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-3">This tender requires your approval. Please review all details carefully before proceeding.</p>
                
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <button type="button" 
                                class="btn btn-success w-100" 
                                data-bs-toggle="modal" 
                                data-bs-target="#approveModal">
                            <i class="fas fa-check me-2"></i>Approve Tender
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" 
                                class="btn btn-danger w-100" 
                                data-bs-toggle="modal" 
                                data-bs-target="#rejectModal">
                            <i class="fas fa-times me-2"></i>Reject Tender
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
@elseif($tender->ApprovalStatus == \App\Enums\TenderApprovalStatusEnum::APPROVED)
    {{-- APPROVED STATE --}}
    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Approved:</strong> This tender has been approved and published.
    </div>
    
@elseif($tender->ApprovalStatus == \App\Enums\TenderApprovalStatusEnum::REJECTED)
    {{-- REJECTED STATE --}}
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-times-circle me-2"></i>
        <strong>Rejected:</strong> This tender was rejected. 
        @if($isSubmitter)
            <br>Please review the workflow history for details, make necessary corrections, and resubmit.
            <br><a href="{{ route('initiatetender.edit', $tender->Id) }}" class="btn btn-sm btn-outline-primary mt-2">
                <i class="fas fa-edit me-1"></i>Edit Tender
            </a>
        @else
            Please review the workflow history for details.
        @endif
    </div>
@endif

{{-- Workflow History Link --}}
@if($tender->ApprovalStatus !== null)
    <div class="mb-3">
        <a href="{{ route('initiatetender.workflow-history', $tender->Id) }}" 
           class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-history me-1"></i>View Workflow History
        </a>
    </div>
@endif

        <!-- Tender Summary Info -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tender Title</label>
                        <input type="text" class="form-control" value="{{$tender->Title}}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tender Type</label>
                        <input type="text" class="form-control" value="{{$tender->TenderType?->name}} Tender" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" value="{{$tenderCategory->TenderCategory ?? 'N/A'}}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" 
                               value="{{$tender->Status?->displayName() ?? 'N/A'}}" 
                               readonly>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="form-label">Submission Deadline</label>
                        <input type="text" class="form-control" 
                               value="{{ $tender->SubmissionDeadline ? $tender->SubmissionDeadline->format('d/m/Y H:i') : 'N/A' }}" 
                               readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Opening Date</label>
                        <input type="text" class="form-control" 
                               value="{{ $tender->OpeningDate ? $tender->OpeningDate->format('d/m/Y H:i') : 'N/A' }}" 
                               readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Currency</label>
                        <input type="text" class="form-control" 
                               value="{{$currency->Code ?? 'N/A'}} ({{$currency->Symbol ?? ''}})" 
                               readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scope of Work & Instructions -->
        @if($tender->ScopeOfWork || $tender->Instructions)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-alt"></i> Tender Details</h5>
            </div>
            <div class="card-body">
                @if($tender->ScopeOfWork)
                <div class="mb-3">
                    <strong>Scope of Work:</strong>
                    <p class="mt-2">{{ $tender->ScopeOfWork }}</p>
                </div>
                @endif
                @if($tender->Instructions)
                <div>
                    <strong>Instructions to Bidders:</strong>
                    <p class="mt-2">{{ $tender->Instructions }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Procurement Plan Reference Section -->
        @if($tender->ProcurementPlanId && isset($procurementPlan) && $procurementPlan)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Procurement Plan Reference</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Plan Reference:</strong><br>
                        <span class="text-primary">{{ $procurementPlan->ReferenceNumber ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-8">
                        <strong>Plan Title:</strong><br>
                        {{ $procurementPlan->Title ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Items from Procurement Plan -->
        @if(isset($planItems) && $planItems && $planItems->isNotEmpty())
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-list-alt"></i> Items from Procurement Plan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item Name</th>
                                <th>Plan Reference</th>
                                <th>Need ID</th>
                                <th>Total Planned Qty</th>
                                <th>Qty in Tender</th>
                                <th>Unit Price</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($planItems as $index => $planItem)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $planItem['item']->ItemName ?? 'N/A' }}</td>
                                <td><span class="badge bg-info">{{ $planItem['planReference'] ?? 'N/A' }}</span></td>
                                <td><span class="badge bg-secondary">{{ $planItem['needId'] ?? 'N/A' }}</span></td>
                                <td>{{ number_format($planItem['plannedQty'] ?? 0, 2) }}</td>
                                <td><strong>{{ number_format($planItem['qtyToTender'] ?? 0, 2) }}</strong></td>
                                <td>{{ $currency->Symbol ?? '' }} {{ number_format($planItem['unitPrice'] ?? 0, 2) }}</td>
                                <td><strong>{{ $currency->Symbol ?? '' }} {{ number_format($planItem['totalPrice'] ?? 0, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="7" class="text-end">Plan Items Subtotal:</th>
                                <th>{{ $currency->Symbol ?? '' }} {{ number_format($planItems->sum('totalPrice'), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Manual Items -->
        @if(isset($manualItems) && $manualItems && $manualItems->isNotEmpty())
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-hand-pointer"></i> Manually Added Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($manualItems as $index => $manualItem)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $manualItem['item']->ItemName ?? $manualItem['description'] ?? 'N/A' }}</td>
                                <td><strong>{{ number_format($manualItem['qtyToTender'] ?? 0, 2) }}</strong></td>
                                <td>{{ $currency->Symbol ?? '' }} {{ number_format($manualItem['unitPrice'] ?? 0, 2) }}</td>
                                <td><strong>{{ $currency->Symbol ?? '' }} {{ number_format($manualItem['totalPrice'] ?? 0, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Manual Items Subtotal:</th>
                                <th>{{ $currency->Symbol ?? '' }} {{ number_format($manualItems->sum('totalPrice'), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Legacy Items Table (fallback if planItems/manualItems not available) -->
        @if((!isset($planItems) || $planItems->isEmpty()) && (!isset($manualItems) || $manualItems->isEmpty()))
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">📦 Items in this Tender</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item Description</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Estimated Unit Cost</th>
                                <th>Total Estimated Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{ $item->item?->ItemName ?? 'N/A' }}</td>
                                    <td>{{ $item->category?->Name ?? 'N/A' }}</td>
                                    <td>{{$item->QtyToTender}}</td>
                                    <td>{{ $currency->Symbol ?? 'KES' }} {{ number_format($item->item->price?->ActualPrice ?? 0, 2) }}</td>
                                    <td>{{ $currency->Symbol ?? 'KES' }} {{ number_format(($item->QtyToTender ?? 0) * ($item->item->price?->ActualPrice ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold text-end">
                            <tr>
                                <td colspan="5">Total Estimated Cost:</td>
                                <td>{{ $currency->Symbol ?? 'KES' }} {{ number_format($totalEstimatedCost, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Total Estimated Cost Summary -->
        <div class="card shadow-sm mb-3">
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-8 text-end">
                        <h4>Total Estimated Cost:</h4>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-primary"><strong>{{ $currency->Symbol ?? 'KES' }} {{ number_format($totalEstimatedCost ?? 0, 2) }}</strong></h4>
                    </div>
                </div>
            </div>
        </div>

        @if ($tender->TenderType?->name == 'Restricted')
            <!-- Selected Suppliers Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">🏷️ Selected Suppliers (Restricted Tender)</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Supplier Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($suppliers as $item)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$item->supplier->thirdParty->ThirdPartyName ?? 'N/A'}}</td>
                                        <td>{{$item->supplier->thirdParty->Email ?? 'N/A'}}</td>
                                        <td>{{$item->supplier->thirdParty->Phone ?? 'N/A'}}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No suppliers selected</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Attached Documents --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">📎 Attached Documents</h5>
                <div class="p-3 border rounded bg-light">
                    @forelse($documents as $document)
                        <div class="mb-2">
                            {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                        </div>
                    @empty
                        <p class="text-muted mb-0">No documents attached.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="text-end mb-3">
            <!-- Back Button -->
            <a href="{{ route('initiatetender.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to Tender List
            </a>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('initiatetender.approve') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tender_id" value="{{ $tender->Id }}">
                    
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="approveModalLabel">
                            <i class="fas fa-check-circle me-2"></i>Approve Tender
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You are about to approve tender <strong>{{ $tender->TenderNo }}</strong>. 
                            This action will publish the tender and send notifications to suppliers.
                        </div>
                        
                        <div class="mb-3">
                            <label for="approve_reason" class="form-label">
                                Approval Comments <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" 
                                      id="approve_reason" 
                                      name="reason" 
                                      rows="4" 
                                      required 
                                      placeholder="Enter your approval comments...">Tender reviewed and approved.</textarea>
                            <div class="form-text">
                                Please provide comments explaining your approval decision.
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="confirmApprove" 
                                   required>
                            <label class="form-check-label" for="confirmApprove">
                                I confirm that I have reviewed all tender details and approve this tender
                            </label>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Approve Tender
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('initiatetender.reject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tender_id" value="{{ $tender->Id }}">
                    
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="rejectModalLabel">
                            <i class="fas fa-times-circle me-2"></i>Reject Tender
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            You are about to reject tender <strong>{{ $tender->TenderNo }}</strong>. 
                            The tender will be returned to draft status for corrections.
                        </div>
                        
                        <div class="mb-3">
                            <label for="reject_reason" class="form-label">
                                Rejection Reason <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" 
                                      id="reject_reason" 
                                      name="reason" 
                                      rows="4" 
                                      required 
                                      placeholder="Please provide detailed reasons for rejection..."></textarea>
                            <div class="form-text">
                                Be specific about what needs to be corrected. This will help the submitter make necessary changes.
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="confirmReject" 
                                   required>
                            <label class="form-check-label" for="confirmReject">
                                I confirm that I want to reject this tender
                            </label>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i>Reject Tender
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('snippets.actions.preview-files')
@endsection
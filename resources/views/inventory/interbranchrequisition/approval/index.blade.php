@extends('layouts.app')
@section('title', 'Approve Inter-Branch Requisition')
@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="container mt-4">
    <h3>Inter-Branch Requisition Approval</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('interbranchrequisitionapproval.index') }}" id="requisition-selection-form">
        <div class="mb-3">
            <label for="ReqId" class="form-label">Select Pending Requisition <span class="text-danger">*</span></label>
            <select name="ReqId" id="ReqId" class="form-select" onchange="this.form.submit()" required>
                <option value="">-- Choose Requisition To Approve --</option>
                @foreach($pendingRequisitions as $requisitionOption)
                    <option
                        value="{{ $requisitionOption->Id }}" {{ old('ReqId', request()->ReqId) == $requisitionOption->Id ? 'selected' : '' }}>
                        {{ $requisitionOption->ReqNo }} ({{ $requisitionOption->fromBranch?->Name ?? '?' }}
                        → {{ $requisitionOption->toBranch?->Name ?? '?' }})
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <div id="requisition-details" class="mt-4">
        @if(isset($requisition) && $requisition)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Requisition Details</h4>
                <span
                    class="badge bg-primary">Logged in as: {{ Auth::user()->role() ? Auth::user()->role()->name : 'Unknown User' }}</span>
            </div>
            <!-- Requisition Summary -->
            <div class="row mb-4 bg-light p-3 border rounded">
                <div class="col-md-4"><strong>Requisition No.:</strong> {{ $requisition->ReqNo ?? 'N/A' }}</div>
                <div class="col-md-4">
                    <strong>Date:</strong> {{ $requisition->CreatedOn ? $requisition->CreatedOn->format('Y-m-d') : 'N/A' }}
                </div>
                <div class="col-md-4"><strong>From Branch:</strong> {{ $requisition->fromBranch->Name ?? '-' }}</div>
                <div class="col-md-4"><strong>To Branch:</strong> {{ $requisition->toBranch->Name ?? '-' }}</div>
                <div class="col-md-4"><strong>Status:</strong>
                    @php
                        $statusEnum = App\Enums\Inventory\InterBranchRequisitionEnum::tryFrom($requisition->Status);
                    @endphp
                    @if($statusEnum)
                        <span
                            class="badge bg-{{ $statusEnum->badgeColor() }}{{ $statusEnum->badgeColor() === 'warning' ? ' text-dark' : ' text-light' }}">{{ $statusEnum->label() }}</span>
                    @else
                        <span class="badge bg-secondary">{{ $requisition->Status }}</span>
                    @endif
                </div>
                <div class="col-md-4"><strong>Requested By:</strong> {{ $requisition->creator->Name?? '-' }}</div>
            </div>

            <!-- Requisition Items Table -->
            <div class="mb-4">
                <h5>Requested Items</h5>
                @if($requisition->items && $requisition->items->isNotEmpty())
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Requested Qty</th>
                            <th>Approved Qty <span class="text-danger">*</span></th>
                            <th>Remarks</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($requisition->items as $index => $requisitionItem)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $requisitionItem->item?->ItemName ?? 'N/A' }}</td>
                                <td>{{ $requisitionItem->RequestedQty }}</td>
                                <td>
                                    <input type="number" min="0" max="{{ $requisitionItem->RequestedQty }}" 
                                           name="approved_qty[{{ $requisitionItem->Id }}]"
                                           class="form-control approved-qty-input"
                                           data-item-id="{{ $requisitionItem->Id }}"
                                           value="{{ old('approved_qty.' . $requisitionItem->Id, $requisitionItem->RequestedQty) }}"
                                           form="approval-form" required>
                                    <small class="text-muted">Max: {{ $requisitionItem->RequestedQty }}</small>
                                    <div class="invalid-feedback" id="error-{{ $requisitionItem->Id }}" style="display: none;">
                                        <!-- Error message will appear here -->
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="item_remarks[{{ $requisitionItem->Id }}]"
                                           class="form-control"
                                           value="{{ old('item_remarks.' . $requisitionItem->Id, $requisitionItem->Remarks) }}"
                                           placeholder="Optional remarks" form="approval-form">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-warning">No items available for this requisition.</div>
                @endif
            </div>

            <!-- Approval Form -->
            <div class="card p-4 shadow-sm border rounded">
                <h5>Approval Decision</h5>
                <form method="POST" action="{{ route('interbranchrequisitionapproval.submit') }}" id="approval-form">
                    @csrf
                    <input type="hidden" name="ReqId" value="{{ $requisition->Id }}">

                    <div class="mb-3">
                        <label class="form-label">Notes <span class="text-danger">*</span></label>
                        <textarea name="comments" class="form-control" rows="4" required>{{ old('comments') }}</textarea>
                        <small class="text-muted">Required. Explain your decision to approve or reject.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action <span class="text-danger">*</span></label>
                        <select name="action" class="form-select" id="action-select" required>
                            <option value="">-- Choose Action --</option>
                            <option value="APPROVED" {{ old('action') == 'APPROVED' ? 'selected' : '' }}>Approve</option>
                            <option value="REJECTED" {{ old('action') == 'REJECTED' ? 'selected' : '' }}>Reject</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('interbranchrequisitionapproval.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit Decision</button>
                    </div>
                </form>
            </div>
        @else
            <div class="alert alert-info">Please select a requisition to view details and approve.</div>
        @endif
    </div>
</div>

@push('styles')
<style>
    /* Style for inline error display */
    .quantity-error {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
        display: block;
    }
    .has-error {
        border-color: #dc3545 !important;
    }
    .has-error:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }
</style>
@endpush

@push('scripts')
<script>
    // Client-side validation for the approval form
    document.addEventListener('DOMContentLoaded', function() {
        const approvalForm = document.getElementById('approval-form');
        const actionSelect = document.getElementById('action-select');
        const approvedQtyInputs = document.querySelectorAll('.approved-qty-input');
        
        // Function to show inline error
        function showInlineError(input, message) {
            const errorDiv = document.getElementById(`error-${input.dataset.itemId}`);
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
                errorDiv.className = 'quantity-error';
                input.classList.add('has-error');
                input.classList.add('is-invalid');
            }
        }
        
        // Function to hide inline error
        function hideInlineError(input) {
            const errorDiv = document.getElementById(`error-${input.dataset.itemId}`);
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
                input.classList.remove('has-error');
                input.classList.remove('is-invalid');
            }
        }
        
        // Function to clear all errors
        function clearAllErrors() {
            approvedQtyInputs.forEach(input => {
                hideInlineError(input);
            });
        }
        
        // Function to validate a single input
        function validateInput(input) {
            const action = actionSelect.value;
            const requestedQty = parseFloat(input.max) || 0;
            const approvedQty = parseFloat(input.value) || 0;
            
            // Clear previous error
            hideInlineError(input);
            
            // Validation only applies when action is APPROVED
            if (action === 'APPROVED') {
                if (approvedQty < 0) {
                    showInlineError(input, 'Approved quantity cannot be negative.');
                    return false;
                }
                
                if (approvedQty > requestedQty) {
                    showInlineError(input, 'Approved quantity cannot exceed requested quantity.');
                    return false;
                }
                
                if (isNaN(approvedQty)) {
                    showInlineError(input, 'Please enter a valid number for approved quantity.');
                    return false;
                }
                
                if (approvedQty === 0) {
                    showInlineError(input, 'Approved quantity must be greater than zero.');
                    return false;
                }
            }
            
            return true;
        }
        
        // Function to validate all inputs
        function validateAllInputs() {
            let allValid = true;
            
            approvedQtyInputs.forEach(input => {
                if (!validateInput(input)) {
                    allValid = false;
                }
            });
            
            return allValid;
        }
        
        // Real-time validation on blur (when user leaves the field)
        approvedQtyInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (actionSelect.value === 'APPROVED') {
                    validateInput(this);
                }
            });
            
            // Also validate on input change for immediate feedback
            input.addEventListener('input', function() {
                if (actionSelect.value === 'APPROVED') {
                    const approvedQty = parseFloat(this.value) || 0;
                    if (approvedQty > 0) {
                        hideInlineError(this);
                    }
                }
            });
        });
        
        // Handle action change
        actionSelect.addEventListener('change', function() {
            if (this.value === 'REJECTED') {
                // Clear all errors when switching to reject
                clearAllErrors();
            } else if (this.value === 'APPROVED') {
                // Validate all inputs when switching to approve
                validateAllInputs();
            }
        });
        
        if (approvalForm) {
            approvalForm.addEventListener('submit', function(e) {
                const actionSelect = this.querySelector('select[name="action"]');
                const commentsTextarea = this.querySelector('textarea[name="comments"]');
                
                // Validate action selection
                if (!actionSelect.value) {
                    e.preventDefault();
                    alert('Please select an action (Approve or Reject).');
                    actionSelect.focus();
                    return false;
                }
                
                // Validate comments
                if (!commentsTextarea.value.trim()) {
                    e.preventDefault();
                    alert('Please enter notes for your decision.');
                    commentsTextarea.focus();
                    return false;
                }
                
                // Validate approved quantities if approving
                if (actionSelect.value === 'APPROVED') {
                    // Clear all errors first
                    clearAllErrors();
                    
                    // Validate all inputs
                    let allValid = true;
                    approvedQtyInputs.forEach(input => {
                        if (!validateInput(input)) {
                            allValid = false;
                        }
                    });
                    
                    if (!allValid) {
                        e.preventDefault();
                        
                        // Scroll to first error
                        const firstErrorInput = document.querySelector('.has-error');
                        if (firstErrorInput) {
                            firstErrorInput.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            firstErrorInput.focus();
                        }
                        
                        return false;
                    }
                }
                
                // Confirmation message
                const actionText = actionSelect.value === 'APPROVED' ? 'approve' : 'reject';
                if (!confirm(`Are you sure you want to ${actionText} this requisition? This action cannot be undone.`)) {
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        }
        
        // Initial validation if action is already set to APPROVED (e.g., from form submission with errors)
        if (actionSelect.value === 'APPROVED') {
            // Small delay to ensure DOM is fully rendered
            setTimeout(() => {
                validateAllInputs();
            }, 100);
        }
    });
</script>
@endpush
@endsection
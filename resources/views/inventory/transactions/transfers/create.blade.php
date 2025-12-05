@php use App\Models\Core\Branch; @endphp
@extends('layouts.app')

@section('title', 'Create Transfer')

@section('content')
    <div class="container bg-white shadow rounded p-4">
        <h4 class="mb-4">Create Transaction Transfer</h4>

        {{-- Branch Information Banner --}}
        @php
            $currentBranch = auth()->user()->branch ?? null;
        @endphp
        <div class="alert alert-primary mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-building me-2"></i>
                <div>
                    <strong>Your Branch: {{ $currentBranch ? $currentBranch->Name : 'Unknown' }}</strong>
                    <div class="small mt-1">
                        You can only create transfers from requisitions where your branch is the <strong>From Branch</strong>.
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 1: Select requisition type & number --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Select Requisition</h6>
            </div>
            <div class="card-body">
                <form class="mb-0">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="requisition_type" class="form-label">Requisition Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="requisition_type" required>
                                <option value="">Select Requisition Type</option>
                                <option value="interbranch">InterBranch Requisition</option>
                                <option value="procurement">Procurement Plan Requisition</option>
                            </select>
                            <div class="form-text">
                                <small>
                                    @if($currentBranch && $currentBranch->IsHQ)
                                        <span class="text-success">✓ Procurement transfers are available for HQ branch.</span>
                                    @else
                                        <span class="text-muted">Procurement transfers are only available for HQ branch.</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="requisition_id" class="form-label">Requisition Number <span class="text-danger">*</span></label>
                            <select class="form-select" id="requisition_id" required disabled>
                                <option value="">Select Requisition</option>
                            </select>
                            <div class="form-text">
                                <small class="text-info" id="requisition-info-text">
                                    Select a requisition type first
                                </small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Step 2: Transfer form --}}
        <div id="transferDetails" style="display: none">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Transfer Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transactionstransfers.store') }}" id="transferForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="transferDate" class="form-label">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control"
                                       id="transferDate"
                                       name="TransferDate"
                                       required
                                       value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                       min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                            </div>

                            <div class="col-md-3">
                                <label for="fromBranch" class="form-label">From Branch</label>
                                <input type="text" class="form-control" id="fromBranch" readonly>
                                <input type="hidden" name="FromBranch" id="FromBranch">
                            </div>

                            <div class="col-md-3">
                                <label for="toBranch" class="form-label">To Branch <span class="text-danger">*</span></label>

                                {{-- For Interbranch (hidden field) --}}
                                <input type="hidden" id="toBranchHidden" name="ToBranch">
                                <input type="text" class="form-control" id="toBranchText" readonly style="display: none;">

                                {{-- For Procurement (dropdown) --}}
                                <select class="form-select" id="toBranchSelect" style="display: none;">
                                    <option value="">-- Select Branch --</option>
                                    @foreach (Branch::all() as $branch)
                                        <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="TransferredBy" class="form-label">Transferred By <span class="text-danger">*</span></label>
                                <select name="TransferredBy" class="form-select" id="TransferredBy" required>
                                    <option value="">-- Select User --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->Id }}">{{ $user->Name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <input type="hidden" name="RequisitionId" id="RequisitionId">
                            <input type="hidden" name="RequisitionType" id="RequisitionType">
                        </div>

                        {{-- Items Section --}}
                        <div id="itemsSection">
                            <div class="mb-3">
                                <h5>Requisition Items</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle" id="itemsTable">
                                        <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Item Name</th>
                                            <th>Item Code</th>
                                            <th>Unit Cost</th>
                                            <th>UOM</th>
                                            <th>Approved Qty</th>
                                            <th>Dispatched Qty <span class="text-danger">*</span></th>
                                            <th>Remarks</th>
                                        </tr>
                                        </thead>
                                        <tbody id="itemsBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Error Alert --}}
                        <div id="ajax-error" class="alert alert-danger d-none"></div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-save me-1"></i> Submit Transfer
                            </button>
                            <a href="{{ route('transactionstransfers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const requisitionTypeSelect = document.getElementById('requisition_type');
            const requisitionIdSelect = document.getElementById('requisition_id');
            const transferDetails = document.getElementById('transferDetails');
            const itemsBody = document.getElementById('itemsBody');
            const transferForm = document.getElementById('transferForm');
            const fromBranchText = document.getElementById('fromBranch');
            const fromBranchHidden = document.getElementById('FromBranch');
            const toBranchText = document.getElementById('toBranchText');
            const toBranchSelect = document.getElementById('toBranchSelect');
            const toBranchHidden = document.getElementById('toBranchHidden');
            const requisitionTypeHidden = document.getElementById('RequisitionType');
            const requisitionIdHidden = document.getElementById('RequisitionId');
            const requisitionInfoText = document.getElementById('requisition-info-text');
            const submitBtn = document.getElementById('submitBtn');

            let selectedType = '';

            const requisitionsBaseUrl = "{{ url(route('requisitions.by-type', ['type' => 'PLACEHOLDER'])) }}";
            const requisitionDetailsBaseUrl = "{{ url(route('requisitions.details', ['id' => 'PLACEHOLDER'])) }}";

            // Load requisitions when type changes
            requisitionTypeSelect.addEventListener('change', function () {
                selectedType = this.value;
                requisitionTypeHidden.value = selectedType;
                requisitionIdSelect.innerHTML = '<option value="">Loading...</option>';
                requisitionIdSelect.disabled = true;
                
                // Update info text
                if (selectedType === 'interbranch') {
                    requisitionInfoText.innerHTML = '<i class="fas fa-info-circle me-1"></i> Showing interbranch requisitions where your branch is the <strong>From Branch</strong>';
                    requisitionInfoText.className = 'text-info';
                } else if (selectedType === 'procurement') {
                    @if($currentBranch && $currentBranch->IsHQ)
                        requisitionInfoText.innerHTML = '<i class="fas fa-info-circle me-1"></i> Showing procurement requisitions from HQ';
                        requisitionInfoText.className = 'text-info';
                    @else
                        requisitionInfoText.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> <strong>Procurement transfers are only available when logged into Headquarters.</strong>';
                        requisitionInfoText.className = 'text-danger';
                        requisitionIdSelect.innerHTML = '<option value="">Not available for non-HQ branches</option>';
                        requisitionIdSelect.disabled = true;
                        return;
                    @endif
                } else {
                    requisitionInfoText.innerHTML = 'Select a requisition type first';
                    requisitionInfoText.className = 'text-muted';
                    return;
                }

                // Reset form
                requisitionIdHidden.value = '';
                itemsBody.innerHTML = '';
                fromBranchText.value = '';
                fromBranchHidden.value = '';
                toBranchText.value = '';
                toBranchSelect.value = '';
                toBranchHidden.value = '';
                transferDetails.style.display = 'none';
                toBranchText.style.display = 'none';
                toBranchSelect.style.display = 'none';

                const url = requisitionsBaseUrl.replace('PLACEHOLDER', selectedType);

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        requisitionIdSelect.innerHTML = '<option value="">Select Requisition</option>';
                        requisitionIdSelect.disabled = false;
                        
                        if (data.length === 0) {
                            if (selectedType === 'interbranch') {
                                requisitionIdSelect.innerHTML = '<option value="">No inter-branch requisitions found for your branch</option>';
                                requisitionInfoText.innerHTML = '<i class="fas fa-info-circle me-1"></i> No inter-branch requisitions found where your branch is the <strong>From Branch</strong>';
                            } else {
                                requisitionIdSelect.innerHTML = '<option value="">No procurement requisitions available</option>';
                                requisitionInfoText.innerHTML = '<i class="fas fa-info-circle me-1"></i> No procurement requisitions available';
                            }
                            return;
                        }

                        // Update info text with count
                        requisitionInfoText.innerHTML = `<i class="fas fa-info-circle me-1"></i> Showing ${selectedType} requisitions where ${selectedType === 'interbranch' ? 'your branch is the <strong>From Branch</strong>' : 'from HQ'} - ${data.length} requisition(s) available`;

                        data.forEach(req => {
                            const text = selectedType === 'interbranch' ? req.ReqNo : req.GRNID;
                            const value = selectedType === 'interbranch' ? req.Id : req.id;
                            
                            // Add branch info for display
                            let displayText = text;
                            if (selectedType === 'interbranch' && req.toBranch) {
                                displayText += ` → ${req.toBranch.Name || 'N/A'}`;
                            }
                            
                            requisitionIdSelect.innerHTML += `<option value="${value}">${displayText}</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching requisitions:', error);
                        requisitionIdSelect.innerHTML = '<option value="">Failed to load requisitions</option>';
                        requisitionInfoText.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Failed to load requisitions';
                        requisitionInfoText.className = 'text-danger';
                    });
            });

            // Load requisition details when number changes
            requisitionIdSelect.addEventListener('change', function () {
                const id = this.value;

                // Reset details
                itemsBody.innerHTML = '';
                fromBranchText.value = '';
                fromBranchHidden.value = '';
                toBranchText.value = '';
                toBranchSelect.value = '';
                toBranchHidden.value = '';
                transferDetails.style.display = 'none';
                toBranchText.style.display = 'none';
                toBranchSelect.style.display = 'none';

                if (!id || !selectedType) {
                    requisitionIdHidden.value = '';
                    return;
                }

                requisitionIdHidden.value = id;
                const detailsUrl = requisitionDetailsBaseUrl.replace('PLACEHOLDER', id) + `?type=${selectedType}`;

                // Show loading state
                itemsBody.innerHTML = '<tr><td colspan="8" class="text-center"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading items...</td></tr>';

                fetch(detailsUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to load requisition details');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (selectedType === 'procurement') {
                            // Procurement: From = HQ, To = dropdown
                            fromBranchText.value = 'Headquarters';
                            fromBranchHidden.value = '{{ Branch::where("IsHQ", 1)->value("Id") ?? "" }}';

                            // Show dropdown for ToBranch and hide others
                            toBranchSelect.style.display = 'block';
                            toBranchText.style.display = 'none';

                            // Pre-select if data available
                            if (data.to_branch && data.to_branch.Id) {
                                toBranchSelect.value = data.to_branch.Id;
                            }

                        } else {
                            // Interbranch: From + To from requisition
                            fromBranchText.value = data.from_branch?.Name || 'N/A';
                            fromBranchHidden.value = data.from_branch?.Id || '';

                            toBranchText.value = data.to_branch?.Name || 'N/A';
                            toBranchHidden.value = data.to_branch?.Id || '';

                            // Show text input for display and hide dropdown
                            toBranchText.style.display = 'block';
                            toBranchSelect.style.display = 'none';
                        }

                        // Populate items table
                        itemsBody.innerHTML = '';
                        if (data.items && data.items.length > 0) {
                            data.items.forEach((item, index) => {
                                const dispatchedQty = item.DispatchedQty ?? item.ApprovedQty;
                                itemsBody.innerHTML += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>
                                        ${item.ItemName || 'N/A'}
                                        <input type="hidden" name="items[${index}][item]" value="${item.Item}">
                                    </td>
                                    <td>${item.ItemCode || 'N/A'}</td>
                                    <td>
                                        <input type="number" class="form-control" name="items[${index}][unit_cost]" value="${item.UnitCost || 0}" step="0.01" min="0" readonly>
                                        <input type="hidden" name="items[${index}][unit_id]" value="${item.PriceID || ''}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" value="${item.UOMCode || 'N/A'}" readonly>
                                        <input type="hidden" name="items[${index}][uom]" value="${item.UOM || ''}">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="items[${index}][approved_qty]" value="${item.ApprovedQty || 0}" min="0" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="items[${index}][dispatched_qty]"
                                            value="${dispatchedQty || 0}"
                                            min="0"
                                            max="${item.ApprovedQty || 0}"
                                            required
                                            oninput="validateQuantity(this, ${item.ApprovedQty || 0})">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="items[${index}][remarks]" maxlength="255" placeholder="Optional remarks">
                                    </td>
                                </tr>
                                `;
                            });
                        } else {
                            itemsBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No items found for this requisition</td></tr>';
                        }

                        transferDetails.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error loading requisition details:', error);
                        showError('Failed to load requisition details: ' + error.message);
                        requisitionIdHidden.value = '';
                        itemsBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Failed to load requisition items</td></tr>';
                    });
            });

            // Form submission handler
            transferForm.addEventListener('submit', function (e) {
                e.preventDefault();
                
                // Update button state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';
                submitBtn.disabled = true;

                // Prepare form data based on requisition type
                const formData = new FormData(this);

                // For procurement, we need to manually add the ToBranch value from dropdown
                if (selectedType === 'procurement') {
                    if (!toBranchSelect.value) {
                        showError('Please select a To Branch for procurement transfer');
                        toBranchSelect.focus();
                        resetSubmitButton();
                        return;
                    }
                    // Remove any existing ToBranch value and add the selected one
                    formData.delete('ToBranch');
                    formData.append('ToBranch', toBranchSelect.value);
                } else {
                    // For interbranch, validate hidden field
                    if (!toBranchHidden.value) {
                        showError('Invalid To Branch configuration for interbranch transfer');
                        resetSubmitButton();
                        return;
                    }
                }

                // Validate dispatched quantities
                const dispatchedInputs = document.querySelectorAll('input[name*="dispatched_qty"]');
                let hasInvalidQuantity = false;

                dispatchedInputs.forEach(input => {
                    const max = parseFloat(input.getAttribute('max'));
                    const value = parseFloat(input.value);

                    if (value > max) {
                        showError(`Dispatched quantity cannot exceed approved quantity (${max})`);
                        input.focus();
                        hasInvalidQuantity = true;
                        return;
                    }

                    if (value <= 0) {
                        showError('Dispatched quantity must be greater than 0');
                        input.focus();
                        hasInvalidQuantity = true;
                        return;
                    }
                });

                if (hasInvalidQuantity) {
                    resetSubmitButton();
                    return;
                }

                // Submit using fetch to handle the FormData properly
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (response.ok) {
                        window.location.href = "{{ route('transactionstransfers.index') }}";
                    } else {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Submission failed');
                        });
                    }
                })
                .catch(error => {
                    showError('Submission failed: ' + error.message);
                    resetSubmitButton();
                });
            });

            // Helper function to reset submit button
            function resetSubmitButton() {
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Submit Transfer';
                submitBtn.disabled = false;
            }

            // Helper function to show errors
            function showError(message) {
                const errorDiv = document.getElementById('ajax-error');
                errorDiv.textContent = message;
                errorDiv.classList.remove('d-none');

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    errorDiv.classList.add('d-none');
                }, 5000);
            }

            // Helper function to validate quantity in real-time
            window.validateQuantity = function (input, maxQty) {
                const value = parseFloat(input.value);
                if (value > maxQty) {
                    input.setCustomValidity(`Quantity cannot exceed ${maxQty}`);
                } else if (value <= 0) {
                    input.setCustomValidity('Quantity must be greater than 0');
                } else {
                    input.setCustomValidity('');
                }
            };

            // Initialize based on current branch
            @if($currentBranch && !$currentBranch->IsHQ)
                // Disable procurement option for non-HQ users
                const procurementOption = requisitionTypeSelect.querySelector('option[value="procurement"]');
                if (procurementOption) {
                    procurementOption.disabled = true;
                    procurementOption.textContent += ' (HQ only)';
                }
            @endif
        });
    </script>
@endpush

<style>
    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }

    .form-label {
        font-weight: 500;
    }

    #itemsTable input[readonly] {
        background-color: #f8f9fa;
    }

    .text-danger {
        font-weight: bold;
    }

    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
    }

    .form-text small {
        font-size: 0.85em;
    }
</style>
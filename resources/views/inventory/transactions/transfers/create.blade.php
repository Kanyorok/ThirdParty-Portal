@php use App\Models\Core\Branch; @endphp
@extends('layouts.app')

@section('title', 'Create Transfer')

@section('content')
    <div class="container bg-white shadow rounded p-4">
        <h4 class="mb-4">Create Transaction Transfer</h4>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->has('workflow'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Workflow Configuration Required:</strong>
                {{ $errors->first('workflow') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $currentBranch = auth()->user()->branch ?? null;
            $isHQ = $currentBranch && $currentBranch->IsHQ;
        @endphp
        <div class="alert alert-primary mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-building me-2"></i>
                <div>
                    <strong>Your Branch: {{ $currentBranch ? $currentBranch->Name : 'Unknown' }}</strong>
                    <div class="small mt-1">
                        @if($isHQ)
                            <span class="text-success">✓ You are logged in as Headquarters. Transfers will use FIFO automatically.</span>
                        @else
                            <span class="text-warning">⚠ You are logged in as a non-HQ branch. You must select GRN batches for transfer.</span>
                        @endif
                    </div>
                    <div class="small mt-2">
                        <i class="fas fa-info-circle text-info me-1"></i>
                        <strong>GRN Tracking Required:</strong> You can only transfer items that have GRN ledger entries at your branch.
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-cogs me-2 mt-1"></i>
                <div>
                    <strong>Workflow Configuration Required</strong>
                    <div class="small mt-2">
                        <i class="fas fa-info-circle text-info me-1"></i>
                        <strong>Approval Process:</strong> Transfers require approval based on configured workflow rules.
                        Ensure that approval groups and workflow configurations are properly set up for your organization.
                    </div>

                </div>
            </div>
        </div>

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
                                <option value="procurement" @if(!$isHQ) disabled @endif>Procurement Plan Requisition</option>
                            </select>
                            <div class="form-text">
                                <small>
                                    @if($isHQ)
                                        <span class="text-success">✓ Procurement transfers are available for HQ branch.</span>
                                    @else
                                        <span class="text-danger">✗ Procurement transfers are only available when logged into Headquarters.</span>
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

        <div id="transferDetails" style="display: none">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Transfer Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transactionstransfers.store') }}" id="transferForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-3">
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

                                <input type="hidden" id="toBranchHidden" name="ToBranch">
                                <input type="text" class="form-control" id="toBranchText" readonly style="display: none;">

                                <select class="form-select" id="toBranchSelect" style="display: none;">
                                    <option value="">-- Select Branch --</option>
                                    @foreach (Branch::where('IsHQ', 0)->get() as $branch)
                                        <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="TransferredBy" class="form-label">Transferred By <span class="text-danger">*</span></label>
                                
                                <input type="hidden" name="TransferredBy" id="TransferredBy" 
                                    value="{{ $currentUser->Id ?? auth()->id() }}">
                                
                                <input type="text" class="form-control" id="TransferredByDisplay" 
                                    value="{{ $currentUser->Name ?? auth()->user()->Name }}" readonly>
                                
                                <small class="text-muted">Current user (non-editable)</small>
                                
                                @error('TransferredBy')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <input type="hidden" name="RequisitionId" id="RequisitionId">
                            <input type="hidden" name="RequisitionType" id="RequisitionType">
                        </div>

                        <div id="itemsSection">
                            <div class="mb-3">
                                <h5>Requisition Items</h5>
                                <div id="grnBatchInfo" class="alert alert-info mb-3" style="display: none;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <span id="grnBatchInfoText"></span>
                                </div>
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
                                            @if(!$isHQ)
                                                <th>GRN Batches <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAllBatchSelection()" title="Select all batches"><i class="fas fa-list"></i> Select</button></th>
                                            @endif
                                            <th>Remarks</th>
                                        </tr>
                                        </thead>
                                        <tbody id="itemsBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if(!$isHQ)
                        <div class="modal fade" id="grnBatchModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-layer-group me-2"></i>Select GRN Batches
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-warning mb-3">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>GRN Batch Selection Required:</strong> You must allocate the full quantity from available GRN batches.
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <strong>Item:</strong> <span id="modalItemName" class="fw-bold"></span><br>
                                                <strong>Required Quantity:</strong> <span id="modalRequiredQty" class="badge bg-primary"></span>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAllSelections()">
                                                    <i class="fas fa-eraser"></i> Clear All
                                                </button>
                                            </div>
                                            <input type="hidden" id="modalItemId">
                                            <input type="hidden" id="modalItemIndex">
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="grnBatchTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="50">
                                                            <input type="checkbox" id="selectAllBatches" class="form-check-input" onchange="toggleAllBatchSelection()">
                                                        </th>
                                                        <th>GRN ID <i class="fas fa-info-circle text-info" title="Original GRN ID from procurement"></i></th>
                                                        <th>Source</th>
                                                        <th>Received Date</th>
                                                        <th>Unit Price</th>
                                                        <th>Available Qty</th>
                                                        <th width="150">Allocate Qty</th>
                                                        <th>Total Value</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="grnBatchBody"></tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="5" class="text-end"><strong>Total Allocated:</strong></td>
                                                        <td><span id="totalAllocatedQty" class="badge bg-success fs-6">0</span></td>
                                                        <td colspan="4">
                                                            <div id="allocationStatus" class="fw-bold"></div>
                                                            <div id="allocationMessage" class="small text-muted mt-1"></div>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        
                                        <div class="alert alert-light border mt-3">
                                            <h6 class="mb-2"><i class="fas fa-lightbulb text-warning me-1"></i> How GRN Tracking Works:</h6>
                                            <ul class="mb-0 small">
                                                <li><span class="badge bg-success">PROC</span> = Original batch from procurement</li>
                                                <li><span class="badge bg-info">TRF</span> = Batch received from transfer</li>
                                                <li>Each batch maintains its original GRN ID throughout the transfer chain</li>
                                                <li>You can only transfer what you have received with GRN tracking</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="saveBatchSelection()" id="saveBatchBtn">
                                            <i class="fas fa-save me-1"></i> Save Selection
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div id="ajax-error" class="alert alert-danger d-none"></div>

                        <div id="ajax-success" class="alert alert-success d-none"></div>

                        <div id="grnWarning" class="alert alert-warning d-none">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>GRN Tracking Required:</strong> All items must have GRN ledger entries at your branch. 
                            Items without GRN batches cannot be transferred.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-paper-plane me-1"></i> Submit Transfer for Approval
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

@push('styles')
<style>
    .batch-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .batch-row:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    .batch-row.selected {
        background-color: rgba(40, 167, 69, 0.1);
        border-left: 3px solid #28a745;
    }
    .allocated-badge {
        font-size: 0.75em;
        margin-left: 5px;
    }
    .batch-badge {
        font-size: 0.7em;
        padding: 2px 6px;
        margin-left: 5px;
    }
    .quantity-input:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    .status-available { background-color: #28a745; }
    .status-low { background-color: #ffc107; }
    .status-critical { background-color: #dc3545; }
    
    /* Modal custom styles */
    #grnBatchModal .modal-content {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    #grnBatchModal .modal-header {
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }
    #grnBatchModal .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }
    #grnBatchModal .table tbody td {
        vertical-align: middle;
    }
    #grnBatchModal .table tfoot td {
        background-color: #f8f9fa;
        border-top: 2px solid #dee2e6;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>
@endpush

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
            const grnBatchInfo = document.getElementById('grnBatchInfo');
            const grnBatchInfoText = document.getElementById('grnBatchInfoText');
            const grnWarning = document.getElementById('grnWarning');

            let selectedType = '';
            let currentBatches = {};
            let isHQ = {{ $isHQ ? 'true' : 'false' }};

            if (isHQ) {
                grnBatchInfoText.textContent = 'You are logged in as Headquarters. Transfers will use FIFO (First-In-First-Out) automatically from available GRN batches.';
                grnBatchInfo.style.display = 'block';
            } else {
                grnBatchInfoText.textContent = 'You must select GRN batches for each item. Items without GRN ledger entries cannot be transferred.';
                grnBatchInfo.style.display = 'block';
            }

            const requisitionsBaseUrl = "{{ url(route('requisitions.by-type', ['type' => 'PLACEHOLDER'])) }}";
            const requisitionDetailsBaseUrl = "{{ url(route('requisitions.details', ['id' => 'PLACEHOLDER'])) }}";

            requisitionTypeSelect.addEventListener('change', function () {
                selectedType = this.value;
                requisitionTypeHidden.value = selectedType;
                requisitionIdSelect.innerHTML = '<option value="">Loading...</option>';
                requisitionIdSelect.disabled = true;
                
                if (selectedType === 'interbranch') {
                    requisitionInfoText.innerHTML = '<i class="fas fa-info-circle me-1"></i> Showing interbranch requisitions where your branch is the <strong>From Branch</strong>';
                    requisitionInfoText.className = 'text-info';
                } else if (selectedType === 'procurement') {
                    @if($isHQ)
                        requisitionInfoText.innerHTML = '<i class="fas fa-info-circle me-1"></i> Showing procurement requisitions from HQ';
                        requisitionInfoText.className = 'text-info';
                    @else
                        requisitionInfoText.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> <strong class="text-danger">Procurement transfers are only available when logged into Headquarters.</strong>';
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
                grnWarning.classList.add('d-none');

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

                        requisitionInfoText.innerHTML = `<i class="fas fa-info-circle me-1"></i> Showing ${selectedType} requisitions where ${selectedType === 'interbranch' ? 'your branch is the <strong>From Branch</strong>' : 'from HQ'} - ${data.length} requisition(s) available`;

                        data.forEach(req => {
                            const text = selectedType === 'interbranch' ? req.ReqNo : req.GRNID;
                            const value = selectedType === 'interbranch' ? req.Id : req.id;
                            
                            let displayText = text;
                            if (selectedType === 'interbranch' && req.toBranch) {
                                displayText += ` → ${req.toBranch.Name || 'N/A'}`;
                            } else if (selectedType === 'procurement' && req.TransferTo) {
                                displayText += ` → ${req.TransferTo || 'N/A'}`;
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

            requisitionIdSelect.addEventListener('change', function () {
                const id = this.value;

                itemsBody.innerHTML = '';
                fromBranchText.value = '';
                fromBranchHidden.value = '';
                toBranchText.value = '';
                toBranchSelect.value = '';
                toBranchHidden.value = '';
                transferDetails.style.display = 'none';
                toBranchText.style.display = 'none';
                toBranchSelect.style.display = 'none';
                grnWarning.classList.add('d-none');

                if (!id || !selectedType) {
                    requisitionIdHidden.value = '';
                    return;
                }

                requisitionIdHidden.value = id;
                const detailsUrl = requisitionDetailsBaseUrl.replace('PLACEHOLDER', id) + `?type=${selectedType}`;

                itemsBody.innerHTML = '<tr><td colspan="' + (isHQ ? '8' : '9') + '" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading items and checking GRN availability...</td></tr>';

                fetch(detailsUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to load requisition details');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (selectedType === 'procurement') {
                            fromBranchText.value = 'Headquarters';
                            fromBranchHidden.value = '{{ Branch::where("IsHQ", 1)->value("Id") ?? "" }}';

                            toBranchSelect.style.display = 'block';
                            toBranchText.style.display = 'none';

                            if (data.to_branch && data.to_branch.Id) {
                                toBranchSelect.value = data.to_branch.Id;
                            }

                        } else {
                            fromBranchText.value = data.from_branch?.Name || 'N/A';
                            fromBranchHidden.value = data.from_branch?.Id || '';

                            toBranchText.value = data.to_branch?.Name || 'N/A';
                            toBranchHidden.value = data.to_branch?.Id || '';

                            toBranchText.style.display = 'block';
                            toBranchSelect.style.display = 'none';
                        }

                        itemsBody.innerHTML = '';
                        let hasItemsWithoutGRN = false;
                        
                        if (data.items && data.items.length > 0) {
                            data.items.forEach((item, index) => {
                                const dispatchedQty = item.DispatchedQty ?? item.ApprovedQty;
                                
                                let batchColumn = '';
                                if (!isHQ) {
                                    batchColumn = `
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="showGRNBatches(${index}, '${item.Item}', '${item.ItemName}', ${dispatchedQty})" id="batchBtn${index}">
                                                <i class="fas fa-layer-group"></i> Select GRN
                                            </button>
                                            <div id="batchSummary${index}" class="small text-muted mt-1"></div>
                                            <input type="hidden" name="items[${index}][batch_allocation]" id="batchAllocation${index}">
                                        </td>
                                    `;
                                }
                                
                                itemsBody.innerHTML += `
                                <tr id="itemRow${index}">
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
                                        <input type="number" class="form-control quantity-input" name="items[${index}][dispatched_qty]"
                                            id="dispatchedQty${index}"
                                            value="${dispatchedQty || 0}"
                                            min="0"
                                            max="${item.ApprovedQty || 0}"
                                            required
                                            oninput="validateQuantity(this, ${item.ApprovedQty || 0}, ${index})"
                                            onfocus="checkGRNAvailability(${index}, '${item.Item}')">
                                    </td>
                                    ${batchColumn}
                                    <td>
                                        <input type="text" class="form-control" name="items[${index}][remarks]" maxlength="255" placeholder="Optional remarks">
                                    </td>
                                </tr>
                                `;
                            });
                            
                            checkAllItemsGRNAvailability(data.items);
                        } else {
                            itemsBody.innerHTML = '<tr><td colspan="' + (isHQ ? '8' : '9') + '" class="text-center text-muted py-4">No items found for this requisition</td></tr>';
                        }

                        transferDetails.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error loading requisition details:', error);
                        requisitionIdHidden.value = '';
                        itemsBody.innerHTML = '<tr><td colspan="' + (isHQ ? '8' : '9') + '" class="text-center text-danger py-4">Failed to load requisition items</td></tr>';
                    });
            });

            transferForm.addEventListener('submit', function (e) {
                const dispatchedInputs = document.querySelectorAll('input[name*="dispatched_qty"]');
                let hasInvalidQuantity = false;

                dispatchedInputs.forEach(input => {
                    const max = parseFloat(input.getAttribute('max'));
                    const value = parseFloat(input.value);

                    if (value > max) {
                        alert(`Dispatched quantity cannot exceed approved quantity (${max})`);
                        input.focus();
                        hasInvalidQuantity = true;
                        e.preventDefault();
                        return;
                    }

                    if (value <= 0) {
                        alert('Dispatched quantity must be greater than 0');
                        input.focus();
                        hasInvalidQuantity = true;
                        e.preventDefault();
                        return;
                    }
                });

                if (hasInvalidQuantity) {
                    return;
                }

                if (!isHQ) {
                    const allocationInputs = document.querySelectorAll('input[name*="batch_allocation"]');
                    let hasInvalidAllocation = false;
                    let missingAllocation = false;
                    
                    allocationInputs.forEach((input, index) => {
                        const dispatchedQty = parseFloat(document.getElementById(`dispatchedQty${index}`).value);
                        
                        if (!input.value) {
                            alert(`Item ${index + 1}: GRN batch selection is required`);
                            missingAllocation = true;
                            e.preventDefault();
                            return;
                        }
                        
                        const allocation = JSON.parse(input.value || '[]');
                        const allocatedQty = allocation.reduce((sum, batch) => sum + parseFloat(batch.quantity), 0);
                        
                        if (Math.abs(allocatedQty - dispatchedQty) > 0.001) {
                            alert(`Item ${index + 1}: Allocated quantity (${allocatedQty}) must equal dispatched quantity (${dispatchedQty})`);
                            hasInvalidAllocation = true;
                            e.preventDefault();
                            return;
                        }
                    });
                    
                    if (missingAllocation || hasInvalidAllocation) {
                        return;
                    }
                }

                if (selectedType === 'procurement') {
                    const toBranchSelect = document.getElementById('toBranchSelect');
                    if (!toBranchSelect || !toBranchSelect.value) {
                        alert('Please select a To Branch for procurement transfer');
                        toBranchSelect.focus();
                        e.preventDefault();
                        return;
                    }
                }
            });

            @if($currentBranch && !$currentBranch->IsHQ)
                const procurementOption = requisitionTypeSelect.querySelector('option[value="procurement"]');
                if (procurementOption) {
                    procurementOption.disabled = true;
                }
            @endif
        });

        function checkGRNAvailability(index, itemId) {
            const fromBranch = document.getElementById('FromBranch').value;
            const url = "{{ route('transaction-transfers.grn-batches') }}?item_id=" + itemId + "&branch_id=" + fromBranch;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const batchBtn = document.getElementById('batchBtn' + index);
                    const dispatchedInput = document.getElementById('dispatchedQty' + index);
                    
                    if (data.batches && data.batches.length > 0) {
                        batchBtn.disabled = false;
                        batchBtn.classList.remove('btn-secondary');
                        batchBtn.classList.add('btn-outline-info');
                        dispatchedInput.disabled = false;
                    } else {
                        batchBtn.disabled = true;
                        batchBtn.classList.remove('btn-outline-info');
                        batchBtn.classList.add('btn-secondary');
                        dispatchedInput.disabled = true;
                        dispatchedInput.value = 0;
                        
                        showError(`Item has no GRN ledger entries at your branch. Cannot transfer without GRN tracking.`);
                    }
                })
                .catch(error => {
                    console.error('Error checking GRN availability:', error);
                });
        }

        function checkAllItemsGRNAvailability(items) {
            let allItemsHaveGRN = true;
            
            items.forEach((item, index) => {
                const fromBranch = document.getElementById('FromBranch').value;
                const url = "{{ route('transaction-transfers.grn-batches') }}?item_id=" + item.Item + "&branch_id=" + fromBranch;
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        const batchBtn = document.getElementById('batchBtn' + index);
                        const dispatchedInput = document.getElementById('dispatchedQty' + index);
                        const itemRow = document.getElementById('itemRow' + index);
                        
                        if (data.batches && data.batches.length > 0) {
                            batchBtn.disabled = false;
                            batchBtn.classList.remove('btn-secondary');
                            batchBtn.classList.add('btn-outline-info');
                            dispatchedInput.disabled = false;
                            itemRow.classList.remove('table-warning');
                        } else {
                            batchBtn.disabled = true;
                            batchBtn.classList.remove('btn-outline-info');
                            batchBtn.classList.add('btn-secondary');
                            dispatchedInput.disabled = true;
                            dispatchedInput.value = 0;
                            itemRow.classList.add('table-warning');
                            allItemsHaveGRN = false;
                        }
                        
                        if (!allItemsHaveGRN) {
                            document.getElementById('grnWarning').classList.remove('d-none');
                        }
                    })
                    .catch(error => {
                        console.error('Error checking GRN availability for item:', item.Item, error);
                    });
            });
        }

        window.validateQuantity = function (input, maxQty, index) {
            const value = parseFloat(input.value);
            if (value > maxQty) {
                input.setCustomValidity(`Quantity cannot exceed ${maxQty}`);
                input.reportValidity();
                return false;
            } else if (value <= 0) {
                input.setCustomValidity('Quantity must be greater than 0');
                input.reportValidity();
                return false;
            } else {
                input.setCustomValidity('');
                
                if (!isHQ) {
                    const allocationInput = document.getElementById('batchAllocation' + index);
                    if (allocationInput && allocationInput.value) {
                        const allocation = JSON.parse(allocationInput.value);
                        const allocatedQty = allocation.reduce((sum, batch) => sum + parseFloat(batch.quantity), 0);
                        if (allocatedQty !== value) {
                            showError(`Allocated quantity (${allocatedQty}) does not match dispatched quantity (${value}). Please update GRN batch selection.`);
                            return false;
                        }
                    }
                }
                return true;
            }
        };

        let batchData = {};
        let currentModalIndex = null;

        function showGRNBatches(index, itemId, itemName, requiredQty) {
            currentModalIndex = index;
            const modal = new bootstrap.Modal(document.getElementById('grnBatchModal'));
            document.getElementById('modalItemName').textContent = itemName;
            document.getElementById('modalRequiredQty').textContent = requiredQty;
            document.getElementById('modalItemId').value = itemId;
            document.getElementById('modalItemIndex').value = index;
            
            // Clear previous data
            document.getElementById('grnBatchBody').innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading GRN batches...</td></tr>';
            document.getElementById('totalAllocatedQty').textContent = '0';
            document.getElementById('allocationStatus').innerHTML = '';
            document.getElementById('allocationMessage').innerHTML = '';
            document.getElementById('saveBatchBtn').disabled = true;
            
            const fromBranch = document.getElementById('FromBranch').value;
            const url = "{{ route('transaction-transfers.grn-batches') }}?item_id=" + itemId + "&branch_id=" + fromBranch;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('grnBatchBody');
                    tbody.innerHTML = '';
                    
                    if (data.batches && data.batches.length > 0) {
                        batchData = {};
                        
                        data.batches.forEach((batch, i) => {
                            const tr = document.createElement('tr');
                            tr.className = 'batch-row';
                            tr.id = `batchRow${i}`;
                            tr.innerHTML = `
                                <td>
                                    <input type="checkbox" class="form-check-input batch-checkbox" data-index="${i}" onchange="toggleBatch(${i})">
                                </td>
                                <td>
                                    <strong>${batch.grn_id}</strong>
                                    ${batch.source_type === 'transfer' ? 
                                        `<span class="badge bg-info batch-badge" title="Received via transfer">TRF</span>` : 
                                        `<span class="badge bg-success batch-badge" title="Original procurement">PROC</span>`
                                    }
                                </td>
                                <td>
                                    ${batch.source_type === 'transfer' ? 
                                        `<span class="text-muted small">Via transfer</span>` : 
                                        `<span class="text-success small">Original</span>`
                                    }
                                </td>
                                <td>${batch.received_date}</td>
                                <td class="text-end">${batch.unit_price.toFixed(2)}</td>
                                <td class="text-end">
                                    <span class="fw-bold">${batch.remaining_qty}</span>
                                    <div class="progress mt-1" style="height: 5px;">
                                        <div class="progress-bar ${batch.remaining_qty > 10 ? 'bg-success' : batch.remaining_qty > 0 ? 'bg-warning' : 'bg-danger'}" 
                                             style="width: ${Math.min(100, (batch.remaining_qty / batch.remaining_qty) * 100)}%" 
                                             role="progressbar"></div>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm quantity-input allocate-qty" 
                                           data-index="${i}" 
                                           min="0" 
                                           max="${batch.remaining_qty}" 
                                           value="0" 
                                           oninput="updateAllocation(${i})" 
                                           disabled
                                           placeholder="0">
                                </td>
                                <td class="text-end">
                                    <span class="batch-value fw-bold" data-index="${i}">0.00</span>
                                </td>
                                <td>
                                    <span class="status-indicator status-${batch.remaining_qty > 10 ? 'available' : batch.remaining_qty > 0 ? 'low' : 'critical'}"></span>
                                    ${batch.remaining_qty > 10 ? 'Available' : batch.remaining_qty > 0 ? 'Low Stock' : 'Out of Stock'}
                                </td>
                            `;
                            tbody.appendChild(tr);
                            
                            batchData[i] = batch;
                        });
                        
                        const transferCount = data.batches.filter(b => b.source_type === 'transfer').length;
                        const procurementCount = data.batches.filter(b => b.source_type !== 'transfer').length;
                        
                        let infoText = `Found ${data.batches.length} GRN batch(es): `;
                        if (procurementCount > 0) infoText += `<span class="badge bg-success">${procurementCount} from procurement</span> `;
                        if (transferCount > 0) infoText += `<span class="badge bg-info">${transferCount} from transfers</span>`;
                        
                        const allocationMessage = document.getElementById('allocationMessage');
                        allocationMessage.innerHTML = infoText;
                        
                        const existingAllocation = document.getElementById('batchAllocation' + index).value;
                        if (existingAllocation) {
                            try {
                                const allocation = JSON.parse(existingAllocation);
                                allocation.forEach(alloc => {
                                    const batchIndex = Object.values(batchData).findIndex(b => b.ledger_id == alloc.ledger_id);
                                    if (batchIndex !== -1) {
                                        const checkbox = document.querySelector(`.batch-checkbox[data-index="${batchIndex}"]`);
                                        const qtyInput = document.querySelector(`.allocate-qty[data-index="${batchIndex}"]`);
                                        if (checkbox && qtyInput) {
                                            checkbox.checked = true;
                                            qtyInput.disabled = false;
                                            qtyInput.value = alloc.quantity;
                                            updateAllocation(batchIndex);
                                            document.getElementById(`batchRow${batchIndex}`).classList.add('selected');
                                        }
                                    }
                                });
                                updateAllocationSummary();
                            } catch (e) {
                                console.error('Error parsing existing allocation:', e);
                            }
                        }
                        
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="alert alert-danger mb-0">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                        <h5 class="alert-heading">No GRN Batches Found!</h5>
                                        <p class="mb-2">
                                            This item has no GRN ledger entries at your branch.<br>
                                            <strong>You cannot transfer items without GRN tracking.</strong>
                                        </p>
                                        <hr>
                                        <p class="small mb-0">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            <strong>Solution:</strong> Ensure items were properly received with GRN tracking before attempting to transfer.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        `;
                        document.getElementById('saveBatchBtn').disabled = true;
                    }
                    
                    updateAllocationSummary();
                })
                .catch(error => {
                    console.error('Error loading GRN batches:', error);
                    document.getElementById('grnBatchBody').innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Failed to load GRN batches. Please try again.</td></tr>';
                });
            
            modal.show();
        }

        function toggleBatch(index) {
            const checkbox = document.querySelector(`.batch-checkbox[data-index="${index}"]`);
            const qtyInput = document.querySelector(`.allocate-qty[data-index="${index}"]`);
            const batchRow = document.getElementById(`batchRow${index}`);
            
            if (checkbox.checked) {
                qtyInput.disabled = false;
                qtyInput.focus();
                batchRow.classList.add('selected');
            } else {
                qtyInput.disabled = true;
                qtyInput.value = 0;
                batchRow.classList.remove('selected');
                updateAllocation(index);
            }
            updateAllocationSummary();
        }

        function toggleAllBatchSelection() {
            const checkboxes = document.querySelectorAll('.batch-checkbox');
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            
            checkboxes.forEach((checkbox, index) => {
                const batchRow = document.getElementById(`batchRow${index}`);
                if (batchRow) {
                    if (anyChecked) {
                        checkbox.checked = false;
                        const qtyInput = document.querySelector(`.allocate-qty[data-index="${index}"]`);
                        if (qtyInput) {
                            qtyInput.disabled = true;
                            qtyInput.value = 0;
                        }
                        batchRow.classList.remove('selected');
                    } else {
                        checkbox.checked = true;
                        const qtyInput = document.querySelector(`.allocate-qty[data-index="${index}"]`);
                        if (qtyInput) {
                            qtyInput.disabled = false;
                            qtyInput.value = 0;
                        }
                        batchRow.classList.add('selected');
                    }
                }
            });
            updateAllocationSummary();
        }

        function clearAllSelections() {
            document.querySelectorAll('.batch-checkbox').forEach(checkbox => {
                checkbox.checked = false;
                const index = checkbox.getAttribute('data-index');
                const qtyInput = document.querySelector(`.allocate-qty[data-index="${index}"]`);
                const batchRow = document.getElementById(`batchRow${index}`);
                
                if (qtyInput) {
                    qtyInput.disabled = true;
                    qtyInput.value = 0;
                }
                if (batchRow) {
                    batchRow.classList.remove('selected');
                }
            });
            updateAllocationSummary();
        }

        function updateAllocation(index) {
            const qtyInput = document.querySelector(`.allocate-qty[data-index="${index}"]`);
            const valueSpan = document.querySelector(`.batch-value[data-index="${index}"]`);
            const batch = batchData[index];
            
            if (!batch || !qtyInput || !valueSpan) return;
            
            let quantity = parseFloat(qtyInput.value) || 0;
            const maxQty = parseFloat(qtyInput.getAttribute('max'));
            
            if (quantity > maxQty) {
                quantity = maxQty;
                qtyInput.value = maxQty;
            }
            
            if (quantity < 0) {
                quantity = 0;
                qtyInput.value = 0;
            }
            
            quantity = Math.round(quantity * 10000) / 10000;
            qtyInput.value = quantity;
            
            const value = quantity * batch.unit_price;
            valueSpan.textContent = value.toFixed(2);
            
            updateAllocationSummary();
        }

        function updateAllocationSummary() {
            const requiredQty = parseFloat(document.getElementById('modalRequiredQty').textContent);
            let totalAllocated = 0;
            let totalValue = 0;
            
            document.querySelectorAll('.allocate-qty:not(:disabled)').forEach(input => {
                const qty = parseFloat(input.value) || 0;
                const index = input.getAttribute('data-index');
                const batch = batchData[index];
                
                if (batch) {
                    totalAllocated += qty;
                    totalValue += qty * batch.unit_price;
                }
            });
            
            totalAllocated = Math.round(totalAllocated * 10000) / 10000;
            
            document.getElementById('totalAllocatedQty').textContent = totalAllocated.toFixed(4);
            document.getElementById('totalAllocatedQty').className = totalAllocated === requiredQty ? 
                'badge bg-success fs-6' : 
                totalAllocated < requiredQty ? 'badge bg-warning fs-6' : 'badge bg-danger fs-6';
            
            const statusDiv = document.getElementById('allocationStatus');
            const saveBtn = document.getElementById('saveBatchBtn');
            
            if (totalAllocated === requiredQty) {
                statusDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Allocation complete</span>';
                statusDiv.className = 'text-success fw-bold';
                saveBtn.disabled = false;
            } else if (totalAllocated < requiredQty) {
                const shortBy = requiredQty - totalAllocated;
                statusDiv.innerHTML = `<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Short by ${shortBy.toFixed(4)}</span>`;
                statusDiv.className = 'text-warning fw-bold';
                saveBtn.disabled = true;
            } else {
                const overBy = totalAllocated - requiredQty;
                statusDiv.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle"></i> Over by ${overBy.toFixed(4)}</span>`;
                statusDiv.className = 'text-danger fw-bold';
                saveBtn.disabled = true;
            }
        }

        function saveBatchSelection() {
            const index = document.getElementById('modalItemIndex').value;
            const requiredQty = parseFloat(document.getElementById('modalRequiredQty').textContent);
            let totalAllocated = 0;
            const allocation = [];
            
            document.querySelectorAll('.batch-checkbox:checked').forEach(checkbox => {
                const batchIndex = checkbox.getAttribute('data-index');
                const qtyInput = document.querySelector(`.allocate-qty[data-index="${batchIndex}"]`);
                let quantity = parseFloat(qtyInput.value) || 0;
                
                quantity = Math.round(quantity * 10000) / 10000;
                
                if (quantity > 0) {
                    const batch = batchData[batchIndex];
                    allocation.push({
                        ledger_id: batch.ledger_id,
                        grn_id: batch.grn_id,
                        goods_receipt_id: batch.goods_receipt_id,
                        unit_price: batch.unit_price,
                        quantity: quantity,
                        parent_ledger_id: batch.parent_ledger_id,
                        source_type: batch.source_type
                    });
                    totalAllocated += quantity;
                }
            });
            
            totalAllocated = Math.round(totalAllocated * 10000) / 10000;
            
            if (Math.abs(totalAllocated - requiredQty) > 0.0001) { // Allow tiny floating point differences
                alert(`Allocated quantity (${totalAllocated.toFixed(4)}) must equal required quantity (${requiredQty.toFixed(4)})`);
                return;
            }
            
            const allocationInput = document.getElementById('batchAllocation' + index);
            allocationInput.value = JSON.stringify(allocation);
            
            const summaryDiv = document.getElementById('batchSummary' + index);
            if (allocation.length > 0) {
                const summary = allocation.map(a => 
                    `${a.grn_id}${a.source_type === 'transfer' ? ' (TRF)' : ''}: ${a.quantity}`
                ).join(', ');
                const sourceCount = {
                    proc: allocation.filter(a => a.source_type !== 'transfer').length,
                    trf: allocation.filter(a => a.source_type === 'transfer').length
                };
                
                let sourceText = '';
                if (sourceCount.proc > 0) sourceText += `${sourceCount.proc} procurement batch(es)`;
                if (sourceCount.trf > 0) {
                    if (sourceText) sourceText += ', ';
                    sourceText += `${sourceCount.trf} transfer batch(es)`;
                }
                
                summaryDiv.innerHTML = `
                    <div class="text-success">
                        <i class="fas fa-check-circle"></i> ${allocation.length} batch(es) selected
                    </div>
                    <div class="small text-muted mt-1">
                        ${sourceText}<br>
                        <span class="text-dark">${summary}</span>
                    </div>
                `;
                
                const dispatchedInput = document.getElementById('dispatchedQty' + index);
                if (dispatchedInput) {
                    dispatchedInput.disabled = false;
                    dispatchedInput.value = requiredQty;
                }
            } else {
                summaryDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> No batches selected</span>';
            }
            
            bootstrap.Modal.getInstance(document.getElementById('grnBatchModal')).hide();
            
            showError('GRN batch selection saved successfully.');
        }
    </script>
@endpush

<style>
    .quantity-input:disabled {
        background-color: #e9ecef;
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .table-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    .table-warning td {
        border-color: rgba(255, 193, 7, 0.3) !important;
    }
    
    .progress {
        background-color: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .progress-bar {
        transition: width 0.3s ease;
    }
    
    #grnBatchModal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    @media (max-width: 992px) {
        #grnBatchModal .modal-dialog {
            margin: 0.5rem;
            max-width: 95%;
        }
        
        #grnBatchModal .table {
            font-size: 0.8rem;
        }
        
        #grnBatchModal .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
    }
</style>
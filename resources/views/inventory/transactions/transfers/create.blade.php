@extends('layouts.app')

@section('title', 'Create Transfer')

@section('content')
    @php
        use App\Models\Core\Branch;
        $currentBranch = auth()->user()->branch ?? null;
        $isHQ = $currentBranch && $currentBranch->IsHQ;
        $currentUser = $currentUser ?? auth()->user();
    @endphp

    <input type="hidden" name="is_hq" id="is_hq" value="{{ $isHQ ? '1' : '0' }}">

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
                                <option value="procurement" @if(!$isHQ) disabled @endif>Purchase Requisition ( Stock Issue )</option>
                            </select>
                            <div class="form-text">
                                <small>
                                    @if($isHQ)
                                        <span class="text-success">✓ Procurement transfers are available for HQ branch.<br>
                                        <b>Note:</b> Purchase Requisitions are the requisitions raised by branches on the procurement module</span>
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

                        <input type="hidden" name="RequisitionId" id="RequisitionId">
                        <input type="hidden" name="RequisitionType" id="RequisitionType">
                        <input type="hidden" name="FromBranch" id="FromBranch">

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="transferDate" class="form-label">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="transferDate" name="TransferDate"
                                       value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                       min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-3">
                                <label for="fromBranch" class="form-label">From Branch</label>
                                <input type="text" class="form-control" id="fromBranch" readonly>
                            </div>

                            <div class="col-md-3">
                                <label for="toBranch" class="form-label">To Branch <span class="text-danger">*</span></label>
                                <input type="hidden" id="toBranchHidden" name="ToBranch">
                                <input type="text" class="form-control" id="toBranchText" readonly style="display: none;">
                                <input type="text" class="form-control" id="toBranchProcText" readonly style="display: none;">
                                <select class="form-select" id="toBranchSelect" style="display: none;">
                                    <option value="">-- Select Branch --</option>
                                    @foreach (Branch::where('IsHQ', 0)->get() as $branch)
                                        <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="TransferredBy" class="form-label">Transferred By <span class="text-danger">*</span></label>
                                <input type="hidden" name="TransferredBy" id="TransferredBy" value="{{ $currentUser->Id ?? auth()->id() }}">
                                <input type="text" class="form-control" id="TransferredByDisplay"
                                       value="{{ $currentUser->Name ?? auth()->user()->Name }}" readonly>
                                <small class="text-muted">Current user (non-editable)</small>
                            </div>
                        </div>

                        <div id="itemsSection">
                            <div class="mb-3">
                                <h5>Requisition Items</h5>
                                <div id="grnBatchInfo" class="alert alert-info mb-3" style="display: none;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <span id="grnBatchInfoText"></span>
                                </div>
                                <div id="grnWarning" class="alert alert-warning d-none">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>GRN Tracking Required:</strong> All items must have GRN ledger entries at your branch.
                                    Items without GRN batches cannot be transferred.
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle" id="itemsTable">
                                        <thead class="table-light">
                                            <tr id="itemsHeaderRow"></tr>
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
    .quantity-input.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    .badge.bg-info, .badge.bg-success, .badge.bg-danger {
        font-size: 0.85rem;
        padding: 0.4rem 0.6rem;
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
@endpush

@push('scripts')
<script>
    let batchData = {};
    let currentModalIndex = null;

    document.addEventListener('DOMContentLoaded', function () {
        const requisitionTypeSelect = document.getElementById('requisition_type');
        const requisitionIdSelect = document.getElementById('requisition_id');
        const transferDetails = document.getElementById('transferDetails');
        const itemsBody = document.getElementById('itemsBody');
        const fromBranchText = document.getElementById('fromBranch');
        const fromBranchHidden = document.getElementById('FromBranch');
        const toBranchText = document.getElementById('toBranchText');
        const toBranchProcText = document.getElementById('toBranchProcText');
        const toBranchSelect = document.getElementById('toBranchSelect');
        const toBranchHidden = document.getElementById('toBranchHidden');
        const requisitionTypeHidden = document.getElementById('RequisitionType');
        const requisitionIdHidden = document.getElementById('RequisitionId');
        const requisitionInfoText = document.getElementById('requisition-info-text');
        const grnBatchInfo = document.getElementById('grnBatchInfo');
        const grnBatchInfoText = document.getElementById('grnBatchInfoText');
        const isHQ = document.getElementById('is_hq').value === '1';

        let selectedType = '';
        const requisitionsBaseUrl = "{{ url(route('requisitions.by-type', ['type' => 'PLACEHOLDER'])) }}";
        const requisitionDetailsBaseUrl = "{{ url(route('requisitions.details', ['id' => 'PLACEHOLDER'])) }}";
        if (isHQ) {
            grnBatchInfoText.textContent = 'You are logged in as Headquarters. Transfers will use FIFO (First-In-First-Out) automatically from available GRN batches.';
            grnBatchInfo.style.display = 'block';
        } else {
            grnBatchInfoText.textContent = 'You must select GRN batches for each item. Items without GRN ledger entries cannot be transferred.';
            grnBatchInfo.style.display = 'block';
        }
        requisitionTypeSelect.addEventListener('change', function () {
            selectedType = this.value;
            requisitionTypeHidden.value = selectedType;
            requisitionIdSelect.innerHTML = '<option value="">Loading...</option>';
            requisitionIdSelect.disabled = true;
            requisitionIdHidden.value = '';
            itemsBody.innerHTML = '';
            fromBranchText.value = '';
            fromBranchHidden.value = '';
            toBranchText.value = '';
            toBranchProcText.value = '';
            toBranchSelect.value = '';
            toBranchHidden.value = '';
            transferDetails.style.display = 'none';
            toBranchText.style.display = 'none';
            toBranchProcText.style.display = 'none';
            toBranchSelect.style.display = 'none';

            if (selectedType === 'interbranch') {
                requisitionInfoText.innerHTML = '<i class="fas fa-info-circle me-1"></i> Showing interbranch requisitions where your branch is the <strong>From Branch</strong>';
                requisitionInfoText.className = 'text-info';
            } else if (selectedType === 'procurement') {
                if (isHQ) {
                    requisitionInfoText.innerHTML = '<i class="fas fa-info-circle me-1"></i> Showing approved requisitions from all branches';
                    requisitionInfoText.className = 'text-info';
                } else {
                    requisitionInfoText.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> <strong class="text-danger">Procurement transfers are only available when logged into Headquarters.</strong>';
                    requisitionInfoText.className = 'text-danger';
                    requisitionIdSelect.innerHTML = '<option value="">Not available for non-HQ branches</option>';
                    requisitionIdSelect.disabled = true;
                    return;
                }
            } else {
                requisitionInfoText.innerHTML = 'Select a requisition type first';
                requisitionInfoText.className = 'text-muted';
                return;
            }

            const url = requisitionsBaseUrl.replace('PLACEHOLDER', selectedType);
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
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
                            requisitionIdSelect.innerHTML = '<option value="">No approved procurement requisitions available</option>';
                            requisitionInfoText.innerHTML = '<i class="fas fa-info-circle me-1"></i> No approved requisitions found';
                        }
                        return;
                    }

                    data.forEach(req => {
                        let displayText, value;
                        if (selectedType === 'interbranch') {
                            displayText = `${req.ReqNo || req.RequisitionNo} → ${req.to_branch?.Name || 'N/A'}`;
                            value = req.Id;
                        } else {
                            displayText = `${req.RequisitionNo} → ${req.branch?.Name || 'N/A'}`;
                            value = req.Id;
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
            if (!id || !selectedType) {
                requisitionIdHidden.value = '';
                return;
            }

            requisitionIdHidden.value = id;
            const detailsUrl = requisitionDetailsBaseUrl.replace('PLACEHOLDER', id) + `?type=${selectedType}`;

            const colSpan = selectedType === 'interbranch' ? (isHQ ? 8 : 9) : 11;
            itemsBody.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading requisition details...</td></tr>`;

            fetch(detailsUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load requisition details');
                    return response.json();
                })
                .then(data => {
                    fromBranchText.value = data.from_branch?.Name || 'N/A';
                    fromBranchHidden.value = data.from_branch?.Id || '';
                    
                    if (selectedType === 'procurement') {
                        if (data.to_branch) {
                            toBranchProcText.value = data.to_branch.Name || 'N/A';
                            toBranchHidden.value = data.to_branch.Id;
                            toBranchProcText.style.display = 'block';
                            toBranchText.style.display = 'none';
                            toBranchSelect.style.display = 'none';
                        }
                    } else {
                        toBranchText.value = data.to_branch?.Name || 'N/A';
                        toBranchHidden.value = data.to_branch?.Id || '';
                        toBranchText.style.display = 'block';
                        toBranchSelect.style.display = 'none';
                        toBranchProcText.style.display = 'none';
                    }

                    itemsBody.innerHTML = '';
                    if (data.items && data.items.length > 0) {
                        adjustTableHeader(selectedType);
                        data.items.forEach((item, index) => {
                            if (selectedType === 'interbranch') {
                                renderInterbranchRow(item, index);
                                if (!isHQ) checkGRNAvailability(index, item.Item);
                            } else {
                                renderProcurementRow(item, index);
                            }
                        });
                    } else {
                        const colSpan = selectedType === 'interbranch' ? (isHQ ? 8 : 9) : 11;
                        itemsBody.innerHTML = `<tr><td colspan="${colSpan}" class="text-center text-muted py-4">No items found for this requisition</td></tr>`;
                    }

                    transferDetails.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading requisition details:', error);
                    requisitionIdHidden.value = '';
                    itemsBody.innerHTML = `<tr><td colspan="12" class="text-center text-danger py-4">Failed to load requisition items</td></tr>`;
                });
        });

        function adjustTableHeader(type) {
            const headerRow = document.getElementById('itemsHeaderRow');
            headerRow.innerHTML = `<th>#</th>
                <th>Item Name</th>
                <th>Item Code</th>
                <th>Unit Cost</th>
                <th>UOM</th>
                <th>Approved Qty</th>`;

            if (type === 'interbranch') {
                headerRow.innerHTML += `<th>Dispatched Qty <span class="text-danger">*</span></th>`;
                if (!isHQ) headerRow.innerHTML += `<th>GRN Batches</th>`;
                headerRow.innerHTML += `<th>Remarks</th>`;
            } else {
                headerRow.innerHTML += `<th>Remaining Qty</th>
                    <th>Available Stock (HQ)</th>
                    <th>Dispatched Qty <span class="text-danger">*</span></th>
                    <th>Remarks</th>
                    <th>Action</th>`;
            }
        }

        function renderInterbranchRow(item, index) {
            const dispatchedQty = item.DispatchedQty ?? item.ApprovedQty;
            let batchColumn = '';
            if (!isHQ) {
                batchColumn = `<td>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="showGRNBatches(${index}, '${item.Item}', '${item.ItemName}', ${dispatchedQty})" id="batchBtn${index}">
                        <i class="fas fa-layer-group"></i> Select GRN
                    </button>
                    <div id="batchSummary${index}" class="small text-muted mt-1"></div>
                    <input type="hidden" name="items[${index}][batch_allocation]" id="batchAllocation${index}">
                </td>`;
            }

            const row = `<tr id="itemRow${index}">
                <td>${index + 1}</td>
                <td>${item.ItemName || 'N/A'}<input type="hidden" name="items[${index}][item]" value="${item.Item}"></td>
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
                        id="dispatchedQty${index}" value="${dispatchedQty || 0}" min="0" step="1" max="${item.ApprovedQty || 0}" required
                        oninput="validateQuantity(this, ${item.ApprovedQty || 0}, ${index})">
                </td>
                ${batchColumn}
                <td><input type="text" class="form-control" name="items[${index}][remarks]" maxlength="255" placeholder="Optional remarks"></td>
            </tr>`;
            itemsBody.insertAdjacentHTML('beforeend', row);
        }

        function renderProcurementRow(item, index) {
            const maxQty = Math.min(item.RemainingQty, item.AvailableStock);
            const row = `<tr id="itemRow${index}" data-item-index="${index}">
                <td>${index + 1}</td>
                <td>${item.ItemName || 'N/A'}<input type="hidden" name="items[${index}][item]" value="${item.Item}"></td>
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
                    <span class="badge bg-info">${item.RemainingQty}</span>
                    <input type="hidden" name="items[${index}][remaining_qty]" value="${item.RemainingQty}">
                </td>
                <td>
                    <span class="badge ${item.AvailableStock > 0 ? 'bg-success' : 'bg-danger'}">${item.AvailableStock}</span>
                    <input type="hidden" name="items[${index}][available_stock]" value="${item.AvailableStock}">
                </td>
                <td>
                    <input type="number" class="form-control quantity-input" name="items[${index}][dispatched_qty]"
                        id="dispatchedQty${index}" value="0" min="0" max="${maxQty}" required
                        oninput="validateProcurementQty(this, ${item.RemainingQty}, ${item.AvailableStock}, ${index})">
                    <div id="qtyError${index}" class="invalid-feedback d-none"></div>
                </td>
                <td><input type="text" class="form-control" name="items[${index}][remarks]" maxlength="255" placeholder="Optional remarks"></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row" onclick="removeProcurementRow(this, ${index})">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </td>
            </tr>`;
            itemsBody.insertAdjacentHTML('beforeend', row);
        }

        document.getElementById('transferForm').addEventListener('submit', function (e) {
            if (selectedType === 'interbranch') {
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
                if (hasInvalidQuantity) return;

                if (!isHQ) {
                    const allocationInputs = document.querySelectorAll('input[name*="batch_allocation"]');
                    let missingAllocation = false;
                    allocationInputs.forEach((input, index) => {
                        const dispatchedQty = parseFloat(document.getElementById(`dispatchedQty${index}`).value);
                        if (!input.value) {
                            alert(`Item ${index + 1}: GRN batch selection is required`);
                            missingAllocation = true;
                            e.preventDefault();
                            return;
                        }
                        try {
                            const allocation = JSON.parse(input.value || '[]');
                            const allocatedQty = allocation.reduce((sum, batch) => sum + parseFloat(batch.quantity), 0);
                            if (Math.abs(allocatedQty - dispatchedQty) > 0.001) {
                                alert(`Item ${index + 1}: Allocated quantity (${allocatedQty}) must equal dispatched quantity (${dispatchedQty})`);
                                e.preventDefault();
                                return;
                            }
                        } catch (parseError) {
                            alert(`Item ${index + 1}: Invalid batch allocation data`);
                            e.preventDefault();
                            return;
                        }
                    });
                    if (missingAllocation) return;
                }
            } else if (selectedType === 'procurement') {
                const rows = document.querySelectorAll('#itemsBody tr');
                if (rows.length === 0) {
                    alert('At least one item must be transferred.');
                    e.preventDefault();
                    return;
                }

                let hasError = false;
                rows.forEach((row, idx) => {
                    const dispatchedInput = row.querySelector('input[name*="dispatched_qty"]');
                    if (!dispatchedInput) return;
                    const dispatched = parseFloat(dispatchedInput.value) || 0;
                    const remaining = parseFloat(row.querySelector('input[name*="remaining_qty"]')?.value) || 0;
                    const available = parseFloat(row.querySelector('input[name*="available_stock"]')?.value) || 0;

                    if (dispatched <= 0) {
                        showFieldError(dispatchedInput, 'Quantity must be greater than 0');
                        hasError = true;
                    } else if (dispatched > remaining) {
                        showFieldError(dispatchedInput, `Cannot exceed remaining quantity (${remaining})`);
                        hasError = true;
                    } else if (dispatched > available) {
                        showFieldError(dispatchedInput, `Not enough stock at HQ (available: ${available})`);
                        hasError = true;
                    }
                });

                if (hasError) {
                    e.preventDefault();
                }
            }

            const btn = e.target.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';
            }
        });

        function showFieldError(input, message) {
            input.classList.add('is-invalid');
            let errorDiv = input.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                input.parentNode.appendChild(errorDiv);
            }
            errorDiv.textContent = message;
            errorDiv.classList.remove('d-none');
        }

        window.removeProcurementRow = function (btn) {
            const row = btn.closest('tr');
            row.remove();
            reindexRows();
        };

        function reindexRows() {
            const rows = document.querySelectorAll('#itemsBody tr');
            rows.forEach((row, newIndex) => {
                row.id = `itemRow${newIndex}`;
                row.cells[0].textContent = newIndex + 1;
                row.querySelectorAll('input, select, button').forEach(el => {
                    ['name', 'id', 'onclick', 'oninput', 'onfocus'].forEach(attr => {
                        if (el.hasAttribute(attr)) {
                            let val = el.getAttribute(attr);
                            if (val) {
                                const newVal = val.replace(/\[\d+\]/, `[${newIndex}]`);
                                el.setAttribute(attr, newVal);
                            }
                        }
                    });
                });
            });
        }
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
                    if (batchBtn) {
                        batchBtn.disabled = false;
                        batchBtn.classList.remove('btn-secondary');
                        batchBtn.classList.add('btn-outline-info');
                    }
                    if (dispatchedInput) dispatchedInput.disabled = false;
                } else {
                    if (batchBtn) {
                        batchBtn.disabled = true;
                        batchBtn.classList.remove('btn-outline-info');
                        batchBtn.classList.add('btn-secondary');
                    }
                    if (dispatchedInput) {
                        dispatchedInput.disabled = true;
                        dispatchedInput.value = 0;
                    }
                    showError('Item has no GRN ledger entries at your branch. Cannot transfer without GRN tracking.');
                }
            })
            .catch(error => console.error('Error checking GRN availability:', error));
    }

    window.validateQuantity = function (input, maxQty, index) {
        const isHQ = document.getElementById('is_hq').value === '1';
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
                    try {
                        const allocation = JSON.parse(allocationInput.value);
                        const allocatedQty = allocation.reduce((sum, batch) => sum + parseFloat(batch.quantity), 0);
                        if (Math.abs(allocatedQty - value) > 0.001) {
                            showError(`Allocated quantity (${allocatedQty}) does not match dispatched quantity (${value}). Please update GRN batch selection.`);
                            return false;
                        }
                    } catch (e) { console.error('Error parsing allocation:', e); }
                }
            }
            return true;
        }
    };

    window.validateProcurementQty = function (input, remaining, available, index) {
        const val = parseFloat(input.value) || 0;
        const errorDiv = document.getElementById('qtyError' + index);
        const max = Math.min(remaining, available);
        if (val > max) {
            input.setCustomValidity(`Max ${max}`);
            input.classList.add('is-invalid');
            if (errorDiv) {
                errorDiv.textContent = `Cannot exceed ${max}`;
                errorDiv.classList.remove('d-none');
            }
            return false;
        } else if (val <= 0) {
            input.setCustomValidity('Must be > 0');
            input.classList.add('is-invalid');
            if (errorDiv) {
                errorDiv.textContent = 'Quantity must be greater than 0';
                errorDiv.classList.remove('d-none');
            }
            return false;
        } else {
            input.setCustomValidity('');
            input.classList.remove('is-invalid');
            if (errorDiv) errorDiv.classList.add('d-none');
            return true;
        }
    };

    function showGRNBatches(index, itemId, itemName, requiredQty) {
        if (document.getElementById('is_hq').value === '1') {
            showError('HQ users do not need to select GRN batches. FIFO will be used automatically.');
            return;
        }
        currentModalIndex = index;
        const modal = new bootstrap.Modal(document.getElementById('grnBatchModal'));
        document.getElementById('modalItemName').textContent = itemName;
        document.getElementById('modalRequiredQty').textContent = requiredQty;
        document.getElementById('modalItemId').value = itemId;
        document.getElementById('modalItemIndex').value = index;

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
                            <td><input type="checkbox" class="form-check-input batch-checkbox" data-index="${i}" onchange="toggleBatch(${i})"></td>
                            <td>
                                <strong>${batch.grn_id}</strong>
                                ${batch.source_type === 'transfer' ? '<span class="badge bg-info batch-badge">TRF</span>' : '<span class="badge bg-success batch-badge">PROC</span>'}
                            </td>
                            <td>${batch.source_type === 'transfer' ? 'Via transfer' : 'Original'}</td>
                            <td>${batch.received_date}</td>
                            <td class="text-end">${batch.unit_price.toFixed(2)}</td>
                            <td class="text-end">
                                <span class="fw-bold">${batch.remaining_qty}</span>
                                <div class="progress mt-1" style="height:5px;">
                                    <div class="progress-bar ${batch.remaining_qty > 10 ? 'bg-success' : batch.remaining_qty > 0 ? 'bg-warning' : 'bg-danger'}" style="width: ${Math.min(100, (batch.remaining_qty / batch.remaining_qty) * 100)}%"></div>
                                </div>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm quantity-input allocate-qty"
                                       data-index="${i}" min="0" max="${batch.remaining_qty}" value="0" oninput="updateAllocation(${i})" disabled>
                            </td>
                            <td class="text-end"><span class="batch-value fw-bold" data-index="${i}">0.00</span></td>
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
                    document.getElementById('allocationMessage').innerHTML = infoText;

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
                        } catch (e) { console.error('Error parsing existing allocation:', e); }
                    }
                } else {
                    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-danger">No GRN batches available for this item at your branch.</td></tr>`;
                    document.getElementById('saveBatchBtn').disabled = true;
                }
                updateAllocationSummary();
            })
            .catch(error => {
                console.error('Error loading GRN batches:', error);
                document.getElementById('grnBatchBody').innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Failed to load GRN batches.</td></tr>';
            });
        modal.show();
    }

    function toggleBatch(index) {
        const checkbox = document.querySelector(`.batch-checkbox[data-index="${index}"]`);
        const qtyInput = document.querySelector(`.allocate-qty[data-index="${index}"]`);
        const batchRow = document.getElementById(`batchRow${index}`);
        if (checkbox?.checked) {
            if (qtyInput) qtyInput.disabled = false;
            if (batchRow) batchRow.classList.add('selected');
        } else {
            if (qtyInput) {
                qtyInput.disabled = true;
                qtyInput.value = 0;
            }
            if (batchRow) batchRow.classList.remove('selected');
            updateAllocation(index);
        }
        updateAllocationSummary();
    }

    function toggleAllBatchSelection() {
        if (document.getElementById('is_hq').value === '1') return;
        const checkboxes = document.querySelectorAll('.batch-checkbox');
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        checkboxes.forEach((checkbox, index) => {
            const batchRow = document.getElementById(`batchRow${index}`);
            if (anyChecked) {
                checkbox.checked = false;
                const qtyInput = document.querySelector(`.allocate-qty[data-index="${index}"]`);
                if (qtyInput) {
                    qtyInput.disabled = true;
                    qtyInput.value = 0;
                }
                if (batchRow) batchRow.classList.remove('selected');
            } else {
                checkbox.checked = true;
                const qtyInput = document.querySelector(`.allocate-qty[data-index="${index}"]`);
                if (qtyInput) {
                    qtyInput.disabled = false;
                    qtyInput.value = 0;
                }
                if (batchRow) batchRow.classList.add('selected');
            }
        });
        updateAllocationSummary();
    }

    function clearAllSelections() {
        if (document.getElementById('is_hq').value === '1') return;
        document.querySelectorAll('.batch-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            const index = checkbox.getAttribute('data-index');
            const qtyInput = document.querySelector(`.allocate-qty[data-index="${index}"]`);
            const batchRow = document.getElementById(`batchRow${index}`);
            if (qtyInput) {
                qtyInput.disabled = true;
                qtyInput.value = 0;
            }
            if (batchRow) batchRow.classList.remove('selected');
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
        if (quantity > maxQty) quantity = maxQty;
        if (quantity < 0) quantity = 0;
        quantity = Math.round(quantity * 10000) / 10000;
        qtyInput.value = quantity;
        const value = quantity * batch.unit_price;
        valueSpan.textContent = value.toFixed(2);
        updateAllocationSummary();
    }

    function updateAllocationSummary() {
        const requiredQty = parseFloat(document.getElementById('modalRequiredQty').textContent);
        let totalAllocated = 0;
        document.querySelectorAll('.allocate-qty:not(:disabled)').forEach(input => {
            totalAllocated += parseFloat(input.value) || 0;
        });
        totalAllocated = Math.round(totalAllocated * 10000) / 10000;
        document.getElementById('totalAllocatedQty').textContent = totalAllocated.toFixed(4);
        document.getElementById('totalAllocatedQty').className = totalAllocated === requiredQty ?
            'badge bg-success fs-6' : totalAllocated < requiredQty ? 'badge bg-warning fs-6' : 'badge bg-danger fs-6';

        const statusDiv = document.getElementById('allocationStatus');
        const saveBtn = document.getElementById('saveBatchBtn');
        if (totalAllocated === requiredQty) {
            statusDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Allocation complete</span>';
            statusDiv.className = 'text-success fw-bold';
            saveBtn.disabled = false;
        } else if (totalAllocated < requiredQty) {
            statusDiv.innerHTML = `<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Short by ${(requiredQty - totalAllocated).toFixed(4)}</span>`;
            statusDiv.className = 'text-warning fw-bold';
            saveBtn.disabled = true;
        } else {
            statusDiv.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle"></i> Over by ${(totalAllocated - requiredQty).toFixed(4)}</span>`;
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
        if (Math.abs(totalAllocated - requiredQty) > 0.0001) {
            alert(`Allocated quantity (${totalAllocated.toFixed(4)}) must equal required quantity (${requiredQty.toFixed(4)})`);
            return;
        }

        const allocationInput = document.getElementById('batchAllocation' + index);
        allocationInput.value = JSON.stringify(allocation);

        const summaryDiv = document.getElementById('batchSummary' + index);
        if (allocation.length > 0) {
            const proc = allocation.filter(a => a.source_type !== 'transfer').length;
            const trf = allocation.filter(a => a.source_type === 'transfer').length;
            let sourceHtml = '';
            if (proc > 0) sourceHtml += `<span class="badge bg-success">${proc} PROC</span> `;
            if (trf > 0) sourceHtml += `<span class="badge bg-info">${trf} TRF</span> `;
            summaryDiv.innerHTML = `
                <span class="text-success"><i class="fas fa-check-circle"></i> ${allocation.length} batch(es) selected</span>
                <div class="small mt-1">${sourceHtml}</div>
                <div class="small text-muted">Total: ${totalAllocated}</div>
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

    function showError(message) {
        const errorDiv = document.getElementById('ajax-error');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.remove('d-none');
            setTimeout(() => errorDiv.classList.add('d-none'), 5000);
        } else {
            alert(message);
        }
    }
</script>
@endpush
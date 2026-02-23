@extends('layouts.app')
@section('title', 'Add GRN – Stock / Asset Update')
@section('content')

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Add GRN – Goods Received (Stock / Asset Update)</h4>
        <a href="{{ route('procurementreceipts.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to GRNs
        </a>
    </div>

    <!-- GRN & PO Details -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-info-circle"></i> GRN Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">GRN No. <span class="text-danger">*</span></label>
                    <input type="text" id="grnNo" class="form-control" readonly placeholder="Auto-generated">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">PO No. <span class="text-danger">*</span></label>
                    <select id="poSelect" name="poSelectDisplay" class="form-select" onchange="populatePODetails()">
                        <option value="">-- Select PO --</option>
                        @foreach($Orders as $po)
                            <option value="{{ $po->OrderNo }}"
                                    data-id="{{ $po->Id }}"
                                    data-OrderNo="{{ $po->OrderNo }}"
                                    data-remarks="{{ $po->ExtOrdNum }}"
                                    data-account-id="{{ $po->AccountID }}"
                                    data-branch-id="{{ $po->BranchID }}"
                                    data-lines='@json($po->OrderLines)'>
                                {{ $po->OrderNo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">PO Reference No:</label>
                    <div class="form-control bg-light" id="poDesc">--</div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('procurementreceipts.store') }}" id="grnForm" onsubmit="return handleFormSubmit()">
        @csrf
        <input type="hidden" name="GRNID"      id="grnNoInput">
        <input type="hidden" name="POID"       id="poIDInput">
        <input type="hidden" name="SupplierID" id="supplierIdInput">

        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-boxes"></i> Line Items</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Item No</th>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>UOM</th>
                                <th>PO Qty</th>
                                <th>Received So Far</th>
                                <th>Remaining</th>
                                <th>Receive Now</th>
                                <th>Unit Price</th>
                                <th>Transfer To</th>
                                <th>Tag Required?</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr id="emptyRow">
                                <td colspan="12" class="text-center text-muted py-3">
                                    Select a Purchase Order above to load line items.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3 text-end">
            <a href="{{ route('procurementreceipts.index') }}" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary" id="saveBtn" disabled>
                <i class="fas fa-save"></i> Save Receipt
            </button>
        </div>
    </form>
</div>

<script>
const branches = @json($branches);

// ── Auto-generate GRN number on page load ─────────────────────────────────
(function initGRN() {
    const now    = new Date();
    const month  = String(now.getMonth() + 1).padStart(2, '0');
    const day    = String(now.getDate()).padStart(2, '0');
    const random = Math.floor(Math.random() * 900 + 100);
    const grnNo  = `GRN-${now.getFullYear()}${month}${day}-${random}`;

    document.getElementById('grnNo').value      = grnNo;
    document.getElementById('grnNoInput').value = grnNo;
})();

// ── Form submit guard ─────────────────────────────────────────────────────
function handleFormSubmit() {
    const poId = document.getElementById('poIDInput').value;
    if (!poId) {
        alert('Please select a Purchase Order first.');
        return false;
    }
    const grnId = document.getElementById('grnNoInput').value;
    if (!grnId) {
        alert('GRN number is missing. Please refresh the page and try again.');
        return false;
    }
    return true;
}

// ── Populate items when PO is chosen ─────────────────────────────────────
function populatePODetails() {
    const select         = document.getElementById('poSelect');
    const selectedOption = select.options[select.selectedIndex];

    if (!selectedOption || !selectedOption.value) {
        document.getElementById('itemsBody').innerHTML =
            '<tr><td colspan="12" class="text-center text-muted py-3">Select a Purchase Order above to load line items.</td></tr>';
        document.getElementById('saveBtn').disabled = true;
        return;
    }

    const poId       = selectedOption.getAttribute('data-id');
    const remarks    = selectedOption.getAttribute('data-remarks');
    const accountId  = selectedOption.getAttribute('data-account-id');
    const poBranchId = selectedOption.getAttribute('data-branch-id');
    const lines      = JSON.parse(selectedOption.getAttribute('data-lines') || '[]');

    document.getElementById('poIDInput').value      = poId;
    document.getElementById('supplierIdInput').value = accountId;
    document.getElementById('poDesc').textContent   = remarks || '--';

    const itemsBody = document.getElementById('itemsBody');
    itemsBody.innerHTML = '';

    if (lines.length === 0) {
        itemsBody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-3">No remaining items for this PO.</td></tr>';
        document.getElementById('saveBtn').disabled = true;
        return;
    }

    // Build branch dropdown options
    let branchOptions = '<option value="">-- Select Branch --</option>';
    branches.forEach(branch => {
        const selected = (poBranchId && branch.Id == poBranchId) ? 'selected' : '';
        branchOptions += `<option value="${branch.Id}" ${selected}>${branch.Name}</option>`;
    });

    lines.forEach((item, index) => {
        const receivedSoFar = item.fReceivedSoFar || 0;
        const remainingQty  = item.fRemainingQty  ?? item.fQuantity;

        itemsBody.insertAdjacentHTML('beforeend', `
            <tr>
                <td><input type="text" class="form-control form-control-sm"
                           name="items[${index}][ItemNo]" value="${item.iStockCodeID}" readonly></td>
                <td>${item.ItemName ?? ''}</td>
                <td>${item.ItemDescription ?? ''}</td>
                <td>${item.Category ?? ''}</td>
                <td>${item.UOM ?? ''}</td>
                <td class="text-center">${item.fQuantity}</td>
                <td class="text-center text-muted">${receivedSoFar}</td>
                <td class="text-center fw-bold text-primary">${remainingQty}</td>
                <td><input type="number" class="form-control form-control-sm"
                           name="items[${index}][ReceivedQTY]" value="${remainingQty}"
                           min="0.01" max="${remainingQty}" step="0.01" required></td>
                <td>
                    <input type="hidden" name="items[${index}][POQTY]" value="${item.fQuantity}">
                    <input type="number" class="form-control form-control-sm"
                           name="items[${index}][UnitPrice]" value="${item.fUnitPriceExcl ?? 0}" step="0.01" min="0">
                </td>
                <td>
                    <select class="form-select form-select-sm" name="items[${index}][TransferTo]">
                        ${branchOptions}
                    </select>
                </td>
                <td class="text-center">
                    <input type="hidden"   name="items[${index}][TagRequired]" value="0">
                    <input type="checkbox" name="items[${index}][TagRequired]" value="1">
                </td>
            </tr>
        `);
    });

    document.getElementById('saveBtn').disabled = false;
}
</script>
@endsection

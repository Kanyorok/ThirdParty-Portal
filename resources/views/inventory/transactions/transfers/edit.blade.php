@extends('layouts.app')

@section('title', 'Edit Transfer')

@section('content')
<div class="container bg-white shadow rounded p-4">
    <h4 class="mb-4">Edit Transfer - {{ $transferitem->TransferID }}</h4>

    <form method="POST" action="{{ route('transactionstransfers.update', $transferitem->Id) }}" id="transferForm">
        @csrf
        @method('PUT')

        <input type="hidden" name="RequisitionType" value="{{ $transferitem->RequisitionType }}">
        <input type="hidden" name="RequisitionId" value="{{ $transferitem->RequisitionId }}">
        <input type="hidden" name="FromBranch" value="{{ $transferitem->FromBranch }}">

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="TransferDate" class="form-label">Transfer Date</label>
                <input type="date" name="TransferDate" class="form-control"
                       value="{{ old('TransferDate', $transferitem->TransferDate) }}" required>
            </div>

            <div class="col-md-4">
                <label for="TransferredBy" class="form-label">Transferred By</label>
                <select name="TransferredBy" id="TransferredBy" class="form-select select2" required>
                    <option value="">-- Select User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->Id }}"
                            {{ old('TransferredBy', $transferitem->TransferredBy) == $user->Id ? 'selected' : '' }}>
                            {{ $user->Name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">From Branch</label>
                <input type="text" class="form-control" value="{{ $transferitem->fromBranch->Name ?? 'N/A' }}" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="ToBranch" class="form-label">To Branch</label>
                @if($transferitem->RequisitionType === 'procurement')
                    {{-- For Procurement: Editable dropdown --}}
                    <select name="ToBranch" id="ToBranch" class="form-select" required>
                        <option value="">-- Select Branch --</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->Id }}"
                                {{ old('ToBranch', $transferitem->ToBranch) == $branch->Id ? 'selected' : '' }}>
                                {{ $branch->Name }}
                            </option>
                        @endforeach
                    </select>
                    @error('ToBranch')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                @else
                    {{-- For Interbranch: Readonly --}}
                    <input type="text" class="form-control" value="{{ $transferitem->toBranch->Name ?? 'N/A' }}" readonly>
                    <input type="hidden" name="ToBranch" value="{{ $transferitem->ToBranch }}">
                @endif
            </div>

            <div class="col-md-6">
                @if($transferitem->RequisitionType === 'procurement')
                    {{-- Empty column for alignment --}}
                @else
                    <label class="form-label">Requisition Type</label>
                    <input type="text" class="form-control" value="Interbranch Transfer" readonly>
                @endif
            </div>
        </div>

        <h5 class="mb-3">Transferred Items</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Item Code</th>
                        <th>Approved Qty</th>
                        <th>Dispatched Qty</th>
                        <th>UOM</th>
                        <th>Unit Cost</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($transferitem->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <input type="hidden" name="items[{{ $index }}][item]" value="{{ $item->Item }}">
                            <input type="text" class="form-control" value="{{ $item->item->ItemName ?? 'N/A' }}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control" value="{{ $item->item->ItemCode ?? 'N/A' }}" readonly>
                        </td>
                        <td>
                            <input type="number" name="items[{{ $index }}][approved_qty]" class="form-control"
                                   value="{{ old("items.$index.approved_qty", $item->ApprovedQty) }}" min="0" step="0.01" required>
                        </td>
                        <td>
                            <input type="number" name="items[{{ $index }}][dispatched_qty]" class="form-control"
                                   value="{{ old("items.$index.dispatched_qty", $item->DispatchedQty) }}" min="0" step="0.01" required>
                        </td>
                        <td>
                            <input type="text" class="form-control" value="{{ $item->uom->Code ?? 'N/A' }}" readonly>
                            <input type="hidden" name="items[{{ $index }}][uom]" value="{{ $item->UOM }}">
                        </td>
                        <td>
                            <input type="number" name="items[{{ $index }}][unit_cost]" class="form-control"
                                   value="{{ old("items.$index.unit_cost", $item->UnitCost ?? $item->item->price->ActualPrice ?? 0) }}" 
                                   min="0" step="0.01" readonly>
                            <input type="hidden" name="items[{{ $index }}][unit_id]" value="{{ $item->ItemPrice }}">
                        </td>
                        <td>
                            <input type="text" name="items[{{ $index }}][remarks]" class="form-control"
                                   value="{{ old("items.$index.remarks", $item->Remarks) }}" maxlength="255">
                        </td>
                        <td>
                            @if($transferitem->Status === 'pe') {{-- Only allow removal if pending --}}
                                <button type="button" class="btn btn-danger btn-sm removeRow">Remove</button>
                            @else
                                <span class="text-muted">Locked</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Status Information --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Current Status</label>
                <input type="text" class="form-control" value="{{ $transferitem->Status }}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Transfer ID</label>
                <input type="text" class="form-control" value="{{ $transferitem->TransferID }}" readonly>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success"
                onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">
                <i class="fas fa-save me-1"></i> Update Transfer
            </button>
            <a href="{{ route('transactionstransfers.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
            
            @if($transferitem->Status === 'pe') {{-- Only show delete if pending --}}
                <button type="button" class="btn btn-danger ms-auto" onclick="confirmDelete()">
                    <i class="fas fa-trash me-1"></i> Delete Transfer
                </button>
            @endif
        </div>
    </form>

    {{-- Delete Form --}}
    @if($transferitem->Status === 'pe')
    <form id="deleteForm" action="{{ route('transactionstransfers.destroy', $transferitem->Id) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Remove item row (only for pending transfers)
    document.querySelector('#itemsTable').addEventListener('click', function(e) {
        if (e.target.classList.contains('removeRow')) {
            if (confirm('Are you sure you want to remove this item?')) {
                e.target.closest('tr').remove();
            }
        }
    });

    // Quantity validation
    const dispatchedInputs = document.querySelectorAll('input[name*="dispatched_qty"]');
    dispatchedInputs.forEach(input => {
        input.addEventListener('change', function() {
            const approvedQty = this.closest('tr').querySelector('input[name*="approved_qty"]').value;
            if (parseFloat(this.value) > parseFloat(approvedQty)) {
                alert('Dispatched quantity cannot exceed approved quantity');
                this.value = approvedQty;
            }
        });
    });
});

function confirmDelete() {
    if (confirm('Are you sure you want to delete this transfer? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}

// Add new item row functionality (if needed)
function addNewItem() {
    const table = document.querySelector('#itemsTable tbody');
    const rowCount = table.rows.length;
    
    const newRow = `
        <tr>
            <td>${rowCount + 1}</td>
            <td>
                <select name="items[${rowCount}][item]" class="form-select" required>
                    <option value="">-- Select Item --</option>
                    @foreach($itemsMasterList as $item)
                        <option value="{{ $item->Id }}">{{ $item->ItemName }} ({{ $item->ItemCode }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" class="form-control item-code" readonly>
            </td>
            <td>
                <input type="number" name="items[${rowCount}][approved_qty]" class="form-control" min="0" step="0.01" required>
            </td>
            <td>
                <input type="number" name="items[${rowCount}][dispatched_qty]" class="form-control" min="0" step="0.01" required>
            </td>
            <td>
                <input type="text" class="form-control uom-code" readonly>
                <input type="hidden" name="items[${rowCount}][uom]" class="uom-id">
            </td>
            <td>
                <input type="number" name="items[${rowCount}][unit_cost]" class="form-control unit-cost" min="0" step="0.01" readonly>
                <input type="hidden" name="items[${rowCount}][unit_id]" class="unit-id">
            </td>
            <td>
                <input type="text" name="items[${rowCount}][remarks]" class="form-control" maxlength="255">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm removeRow">Remove</button>
            </td>
        </tr>
    `;
    
    table.insertAdjacentHTML('beforeend', newRow);
    
    // Add event listener for item selection
    const newItemSelect = table.lastElementChild.querySelector('select[name*="item"]');
    newItemSelect.addEventListener('change', function() {
        const itemId = this.value;
        if (itemId) {
            // Fetch item details and populate fields
            fetchItemDetails(itemId, this.closest('tr'));
        }
    });
}

// Function to fetch item details (you'll need to implement this endpoint)
function fetchItemDetails(itemId, row) {
    // You'll need to create an API endpoint to fetch item details
    fetch(`/api/items/${itemId}/details`)
        .then(response => response.json())
        .then(data => {
            row.querySelector('.item-code').value = data.itemCode || '';
            row.querySelector('.uom-code').value = data.uomCode || '';
            row.querySelector('.uom-id').value = data.uomId || '';
            row.querySelector('.unit-cost').value = data.unitCost || 0;
            row.querySelector('.unit-id').value = data.priceId || '';
        })
        .catch(error => {
            console.error('Error fetching item details:', error);
        });
}
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
</style>
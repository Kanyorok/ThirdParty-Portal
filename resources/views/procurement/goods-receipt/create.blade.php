@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle"></i> Create Goods Receipt Note (GRN)
                    </h5>
                    <a href="{{ route('goods-receipt.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to GRNs
                    </a>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('goods-receipt.store') }}" method="POST" id="grnCreateForm">
                        @csrf

                        <!-- Step 1: GRN Details -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Step 1: GRN Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="grn_id" class="form-label">GRN Number <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="grn_id" name="grn_id" 
                                                   value="{{ old('grn_id', 'GRN-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="received_date" class="form-label">Received Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="received_date" name="received_date" 
                                                   value="{{ old('received_date', date('Y-m-d')) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="store_id" class="form-label">Store <span class="text-danger">*</span></label>
                                            <select class="form-select" id="store_id" name="store_id" required>
                                                <option value="">Select Store</option>
                                                <option value="STORE-001" {{ old('store_id') == 'STORE-001' ? 'selected' : '' }}>Main Store</option>
                                                <option value="STORE-002" {{ old('store_id') == 'STORE-002' ? 'selected' : '' }}>Warehouse A</option>
                                                <option value="STORE-003" {{ old('store_id') == 'STORE-003' ? 'selected' : '' }}>Warehouse B</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="delivery_note_ref" class="form-label">Delivery Note Reference</label>
                                            <input type="text" class="form-control" id="delivery_note_ref" name="delivery_note_ref" 
                                                   value="{{ old('delivery_note_ref') }}" placeholder="Optional">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Purchase Order Selection -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-file-invoice"></i> Step 2: Select Purchase Order</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="po_select" class="form-label">Purchase Order <span class="text-danger">*</span></label>
                                            <select class="form-select" id="po_select" name="po_id" required>
                                                <option value="">Select Purchase Order</option>
                                                @foreach($availablePOs as $po)
                                                    <option value="{{ $po->Id }}" {{ old('po_id') == $po->Id ? 'selected' : '' }}>
                                                        {{ $po->OrderNo }} - {{ $po->supplier->thirdParty->TradingName ?? 'Unknown Supplier' }}
                                                        ({{ $po->remaining_lines->count() }} items)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="supplier_info" class="mt-4" style="display: none;">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6>Supplier Information</h6>
                                                    <p class="mb-1"><strong>Name:</strong> <span id="supplier_name"></span></p>
                                                    <input type="hidden" id="supplier_id" name="supplier_id">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-outline-primary" id="load_po_items">
                                            <i class="fas fa-search"></i> Load PO Items
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Items to Receive -->
                        <div class="card mb-4" id="items_section" style="display: none;">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-boxes"></i> Step 3: Items to Receive</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" id="items_table">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">
                                                    <input type="checkbox" id="select_all" class="form-check-input">
                                                </th>
                                                <th width="20%">Item</th>
                                                <th width="10%">Type</th>
                                                <th width="8%">Ordered</th>
                                                <th width="8%">Received</th>
                                                <th width="8%">Remaining</th>
                                                <th width="10%">Receive Qty</th>
                                                <th width="8%">Unit Price</th>
                                                <th width="10%">Batch/Serial</th>
                                                <th width="8%">Expiry</th>
                                                <th width="5%">QC</th>
                                            </tr>
                                        </thead>
                                        <tbody id="items_tbody">
                                            <!-- Dynamic rows will be populated here -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-secondary" onclick="calculateTotals()">
                                            <i class="fas fa-calculator"></i> Calculate Totals
                                        </button>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <div class="card bg-light">
                                            <div class="card-body py-2">
                                                <strong>Total Items: <span id="total_items">0</span></strong><br>
                                                <strong>Total Value: KES <span id="total_value">0.00</span></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <a href="{{ route('goods-receipt.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" id="submit_btn" disabled>
                                    <i class="fas fa-save"></i> Create GRN
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const poSelect = document.getElementById('po_select');
    const loadItemsBtn = document.getElementById('load_po_items');
    const itemsSection = document.getElementById('items_section');
    const submitBtn = document.getElementById('submit_btn');
    
    // Load PO details when PO is selected and load button is clicked
    loadItemsBtn.addEventListener('click', function() {
        const poId = poSelect.value;
        if (!poId) {
            alert('Please select a Purchase Order first.');
            return;
        }

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

        fetch(`{{ url('/procurement/goods-receipt/api/po-details') }}/${poId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateSupplierInfo(data.data);
                    populateItemsTable(data.data.lines);
                    itemsSection.style.display = 'block';
                    submitBtn.disabled = false;
                } else {
                    alert('Failed to load PO details: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while loading PO details.');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-search"></i> Load PO Items';
            });
    });

    // Select all checkbox functionality
    document.getElementById('select_all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#items_tbody input[type="checkbox"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
});

function populateSupplierInfo(data) {
    document.getElementById('supplier_name').textContent = data.supplier.name;
    document.getElementById('supplier_id').value = data.supplier.id;
    document.getElementById('supplier_info').style.display = 'block';
}

function populateItemsTable(lines) {
    const tbody = document.getElementById('items_tbody');
    tbody.innerHTML = '';

    lines.forEach(line => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="checkbox" name="items[${line.id}][selected]" value="1" class="form-check-input" checked>
                <input type="hidden" name="items[${line.id}][item_id]" value="${line.item_id}">
                <input type="hidden" name="items[${line.id}][order_line_id]" value="${line.id}">
                <input type="hidden" name="items[${line.id}][ordered_qty]" value="${line.remaining_qty}">
            </td>
            <td>
                <strong>${line.item_name}</strong>
                <br><small class="text-muted">${line.item_description}</small>
            </td>
            <td>
                <span class="badge bg-${getItemTypeBadgeClass(line.item_type)}">${line.item_type_display}</span>
            </td>
            <td>${line.ordered_qty}</td>
            <td>${line.received_qty}</td>
            <td><strong>${line.remaining_qty}</strong></td>
            <td>
                <input type="number" name="items[${line.id}][received_qty]" 
                       class="form-control form-control-sm receive-qty" 
                       min="0" max="${line.remaining_qty}" step="0.01" 
                       value="${line.remaining_qty}" required>
            </td>
            <td>
                <input type="number" name="items[${line.id}][unit_price]" 
                       class="form-control form-control-sm unit-price" 
                       min="0" step="0.01" value="${line.unit_price}" required>
            </td>
            <td>
                <input type="text" name="items[${line.id}][batch_number]" 
                       class="form-control form-control-sm" placeholder="Optional">
            </td>
            <td>
                <input type="date" name="items[${line.id}][expiry_date]" 
                       class="form-control form-control-sm">
            </td>
            <td>
                <input type="checkbox" name="items[${line.id}][requires_quality_check]" 
                       class="form-check-input" ${line.item_type === 'stock' ? 'checked' : ''}>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Add event listeners for calculation
    document.querySelectorAll('.receive-qty, .unit-price').forEach(input => {
        input.addEventListener('input', calculateTotals);
    });

    calculateTotals();
}

function getItemTypeBadgeClass(itemType) {
    const classes = {
        'stock': 'success',
        'asset': 'warning', 
        'service': 'info'
    };
    return classes[itemType] || 'secondary';
}

function calculateTotals() {
    const checkedRows = document.querySelectorAll('#items_tbody input[type="checkbox"]:checked');
    let totalItems = 0;
    let totalValue = 0;

    checkedRows.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const qtyInput = row.querySelector('.receive-qty');
        const priceInput = row.querySelector('.unit-price');
        
        if (qtyInput && priceInput) {
            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            
            if (qty > 0) {
                totalItems++;
                totalValue += qty * price;
            }
        }
    });

    document.getElementById('total_items').textContent = totalItems;
    document.getElementById('total_value').textContent = totalValue.toLocaleString('en-US', {minimumFractionDigits: 2});
}
</script>
@endsection

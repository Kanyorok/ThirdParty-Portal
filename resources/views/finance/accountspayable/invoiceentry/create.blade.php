@extends('layouts.app')
@section('title', 'Create Payables Invoice')

@section('content')
<div class="card shadow-lg p-4 rounded-4 border-0">
    <h4 class="fw-bold text-primary mb-4">🧾 Create Payables Invoice</h4>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('invoiceentry.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Invoice Number <span class="text-danger">*</span></label>
                <input type="text" name="InvoiceNumber" class="form-control form-control-lg" placeholder="e.g. INV-1001" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Vendor <span class="text-danger">*</span></label>
                <select id="VendorSelect" name="ThirdPartyID" class="form-select form-select-lg" required>
                    <option disabled selected value="">-- Select Vendor --</option>
                    @foreach($suppliers as $item)
                        <option value="{{ $item->Id }}">{{ $item->SupplierName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                <select name="CurrencyID" class="form-select form-select-lg" required>
                    <option selected disabled value="">-- Select Currency --</option>
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->Id }}">{{ $currency->Code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Exchange Rate</label>
                <input type="number" step="0.0001" name="ExchangeRate" class="form-control form-control-lg" value="1.0000" required>
            </div>

            <div class="col-md-6">
                <label for="POReference" class="form-label fw-semibold">PO Reference <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select id="POReference" class="form-select form-select-lg" name="POReference" required>
                        <option selected disabled value="">-- Select PO --</option>
                        @foreach($orders as $po)
                            <option value="{{ $po->Id }}">{{ $po->OrderNo }}</option>
                        @endforeach
                    </select>
                    <button id="ViewPOButton" type="button" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-eye"></i> View PO
                    </button>
                </div>
                <small class="text-muted">A Vendor must first be selected.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">GRN Reference <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select id="GRNReference" name="GRNReference" class="form-select form-select-lg" required>
                        <option selected disabled value="">-- Select GRN --</option>
                        @foreach($grns as $grn)
                            <option value="{{ $grn->id }}">{{ $grn->GRNID }}</option>
                        @endforeach
                    </select>
                    <button id="ViewGRNButton" type="button" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-eye"></i> View GRN
                    </button>
                </div>
                <small class="text-muted">A PO must first be selected.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                <input type="date" name="InvoiceDate" class="form-control form-control-lg" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Invoice Amount <span class="text-danger">*</span></label>
                <input min="0.00" type="number" step="0.01" name="InvoiceAmount" class="form-control form-control-lg" placeholder="e.g. 1000.00" required>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Invoice Description</label>
                <textarea name="Description" class="form-control form-control-lg" rows="3" placeholder="Enter invoice description"></textarea>
            </div>

            <div class="col-12">
                <h5 class="fw-bold mt-4 mb-2">📤 Upload EDI File (Optional)</h5>
                <input type="file" name="file" class="form-control form-control-lg" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.png,.jpg,.jpeg">
                <small class="text-muted">Allowed types: PDF, Word, Excel, CSV, JPG, PNG</small>
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="{{ route('invoiceentry.index') }}" class="btn btn-outline-secondary btn-lg">Back</a>
            <button type="submit" class="btn btn-success btn-lg px-4"
                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='💾 Saving...'; this.form.submit();}">
                💾 Save Invoice
            </button>
        </div>

        {{-- View PO Modal --}}
        <div class="modal fade" id="viewPO" tabindex="-1" aria-labelledby="viewPOTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    <div class="modal-header bg-gradient bg-primary text-white rounded-top-4">
                        <h5 class="modal-title fw-semibold" id="viewPOTitle">Purchase Order Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body bg-light" id="poDetailsContent"></div>
                    <div class="modal-footer bg-white">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- View GRN Modal --}}
        <div class="modal fade" id="viewGRN" tabindex="-1" aria-labelledby="viewGRNTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    <div class="modal-header bg-gradient bg-secondary text-white rounded-top-4">
                        <h5 class="modal-title fw-semibold" id="viewGRNTitle">GRN Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body bg-light" id="grnDetailsContent"></div>
                    <div class="modal-footer bg-white">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
const vendor = document.getElementById('VendorSelect');
const pos = document.getElementById('POReference');
const grns = document.getElementById('GRNReference');
const viewPOButton = document.getElementById('ViewPOButton');
const viewPOModal = document.getElementById('viewPO');
const viewGRNButton = document.getElementById('ViewGRNButton');
const viewGRNModal = document.getElementById('viewGRN');

// Initial disable
[pos, grns, viewPOButton, viewGRNButton].forEach(el => el.disabled = true);

// Vendor change
vendor.addEventListener('change', async () => {
    const vendorId = vendor.value;
    pos.innerHTML = '<option>Loading...</option>';
    if (!vendorId) return;

    const response = await fetch(`/finance/finance/pos/${vendorId}`);
    const data = await response.json();
    pos.innerHTML = data.length
        ? '<option disabled selected>-- Select PO --</option>' + data.map(po => `<option value="${po.OrderNo}">${po.OrderNo}</option>`).join('')
        : '<option disabled>No PO found</option>';
    pos.disabled = !data.length;
});

// PO change
pos.addEventListener('change', async () => {
    const poId = pos.value;
    grns.innerHTML = '<option>Loading...</option>';
    if (!poId) return;
    const response = await fetch(`/finance/finance/grns/${poId}`);
    const data = await response.json();

    grns.innerHTML = data.length
        ? '<option disabled selected>-- Select GRN --</option>' + data.map(g => `<option value="${g.GRNID}">${g.GRNID}</option>`).join('')
        : '<option disabled>No GRN found</option>';
    grns.disabled = !data.length;
    [viewPOButton, viewGRNButton].forEach(btn => btn.disabled = false);
});

// View PO Modal
viewPOButton.addEventListener('click', async () => {
    const poId = pos.value;
    if (!poId) return;
    const content = document.getElementById('poDetailsContent');
    content.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Loading...</p></div>`;

    const response = await fetch(`/finance/finance/viewpo/${poId}`);
    const data = await response.json();

    // Remove duplicate items by ItemName
    const uniqueItems = Object.values(data.items.reduce((acc, item) => {
        if (!acc[item.ItemName]) acc[item.ItemName] = item;
        return acc;
    }, {}));

    const total = uniqueItems.reduce((sum, item) => sum + item.Quantity * item.UnitCost, 0).toFixed(2);

    content.innerHTML = `
        <div class="mb-3">
            <div class="d-flex justify-content-between flex-wrap">
                <div><strong>PO Number:</strong> ${data.OrderNo}</div>
                <div><strong>Vendor:</strong> ${data.SupplierName}</div>
            </div>
            <div><strong>Date:</strong> ${data.OrderDate}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white rounded">
                <thead class="table-primary text-dark">
                    <tr>
                        <th>#</th>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${uniqueItems.map((item, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${item.ItemName}</td>
                            <td class="text-muted">${item.Description || '-'}</td>
                            <td class="text-center">${item.Quantity}</td>
                            <td class="text-end">KSh ${item.UnitCost.toLocaleString()}</td>
                            <td class="text-end fw-semibold">KSh ${(item.UnitCost * item.Quantity).toLocaleString()}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div class="text-end mt-3 fw-bold fs-5">Grand Total: KSh ${total}</div>
    `;
    new bootstrap.Modal(viewPOModal).show();
});

// View GRN Modal
viewGRNButton.addEventListener('click', async () => {
    const grnId = grns.value;
    if (!grnId) return;
    const content = document.getElementById('grnDetailsContent');
    content.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-secondary"></div><p>Loading...</p></div>`;

    const response = await fetch(`/finance/finance/viewgrn/${grnId}`);
    const data = await response.json();

    content.innerHTML = `
        <div class="mb-3">
            <div><strong>GRN:</strong> ${data.GRNID}</div>
            <div><strong>Vendor:</strong> ${data.SupplierName}</div>
            <div><strong>PO:</strong> ${data.POID}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover bg-white rounded">
                <thead class="table-secondary text-dark">
                    <tr>
                        <th>Item</th>
                        <th>Ordered Qty</th>
                        <th>Received Qty</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.items.map(item => `
                        <tr>
                            <td>${item.ItemName}</td>
                            <td>${item.OrderedQty}</td>
                            <td>${item.ReceivedQty}</td>
                        </tr>`).join('')}
                </tbody>
            </table>
        </div>
    `;
    new bootstrap.Modal(viewGRNModal).show();
});
</script>
@endsection

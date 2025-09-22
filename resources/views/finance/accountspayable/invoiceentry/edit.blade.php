@extends('layouts.app')
@section('title', 'Edit Payables Invoice')

@section('content')
    <div class="card shadow p-1 rounded-4">
        <div class="card-body">
            <p class="text-muted">
                Update the details of the Payables Invoice record below.
                Modify the necessary fields and save changes to ensure accurate documentation.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('invoiceentry.update', $invoice->Id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" name="InvoiceNumber" class="form-control"
                               value="{{ old('InvoiceNumber', $invoice->InvoiceNumber) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vendor <span class="text-danger">*</span></label>
                        <select id="VendorSelect" name="SupplierID" class="form-select" required>
                            <option disabled value="">-- Select Vendor --</option>
                            @foreach($suppliers as $item)
                                <option
                                    value="{{ $item->Id }}" {{ $invoice->SupplierID == $item->Id ? 'selected' : '' }}>
                                    {{ $item->SupplierName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Currency <span class="text-danger">*</span></label>
                        <select name="CurrencyID" class="form-select" required>
                            <option disabled value="">-- Select Currency --</option>
                            @foreach($currencies as $currency)
                                <option
                                    value="{{ $currency->Id }}" {{ $invoice->CurrencyID == $currency->Id ? 'selected' : '' }}>
                                    {{ $currency->Code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Exchange Rate</label>
                        <input type="number" step="0.0001" name="ExchangeRate" class="form-control"
                               value="{{ old('ExchangeRate', $invoice->ExchangeRate ?? 1.0000) }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">PO Reference <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <select id="POReference" class="form-select" name="POReference" style="flex: 1;" required>
                                <option disabled value="">-- Select PO --</option>
                                @foreach($orders as $po)
                                    <option
                                        value="{{ $po->Id }}" {{ $invoice->POReference == $po->Id ? 'selected' : '' }}>
                                        {{ $po->OrderNo }}
                                    </option>
                                @endforeach
                            </select>
                            <button id="ViewPOButton" type="button" class="btn btn-outline-primary">View PO</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">GRN Reference <span class="text-danger">*</span></label>
                        <select id="GRNReference" name="GRNReference" class="form-select" required>
                            <option disabled value="">-- Select GRN --</option>
                            @foreach($grns as $grn)
                                <option
                                    value="{{ $grn->Id }}" {{ $invoice->GRNReference == $grn->Id ? 'selected' : '' }}>
                                    {{ $grn->GRNID }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="InvoiceDate" class="form-control"
                               value="{{ old('InvoiceDate', $invoice->InvoiceDate) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Invoice Amount <span class="text-danger">*</span></label>
                        <input min="0.00" type="number" step="0.01" name="InvoiceAmount" class="form-control"
                               value="{{ old('InvoiceAmount', $invoice->InvoiceAmount) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Invoice Description</label>
                    <textarea name="Description" class="form-control"
                              rows="3">{{ old('Description', $invoice->Description) }}</textarea>
                </div>

                <h6 class="fw-bold mb-2">📤 Replace/Upload EDI File (Optional)</h6>
                <div class="mb-3">
                    <input type="file"
                           name="file"
                           class="form-control"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.png,.jpg,.jpeg">
                    <small class="form-text text-muted">
                        Allowed types: PDF, Word, Excel, CSV, JPG, PNG
                    </small>
                    @if($invoice->FilePath)
                        <p class="mt-2">Current file: <a href="{{ asset('storage/'.$invoice->FilePath) }}"
                                                         target="_blank">View File</a></p>
                    @endif
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('invoiceentry.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-long-arrow-alt-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success"
                            onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit();}">
                        <i class="fas fa-save"></i> Update Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const vendor = document.getElementById('VendorSelect');
        const pos = document.getElementById('POReference');
        const grns = document.getElementById('GRNReference');
        const viewPOButton = document.getElementById('ViewPOButton');
        const viewPOModal = document.getElementById('viewPO');

        // Disable PO and GRN selects initially
        pos.disabled = true;
        grns.disabled = true;
        viewPOButton.disabled = true;

        // Handle vendor change
        vendor.addEventListener('change', function () {
            const selectedVendor = vendor.options[vendor.selectedIndex].value;
            pos.disabled = !selectedVendor;

            pos.innerHTML = '<option selected disabled value="">Loading...</option>';

            if (selectedVendor) {
                fetch(`/finance/finance/pos/${selectedVendor}`)
                    .then(response => response.json())
                    .then(data => {
                        //console.log('PO Data',data);

                        if (data.length === 0) {
                            pos.innerHTML = '<option selected disabled>No PO found</option>';
                            pos.disabled = true;
                            return;
                        }

                        pos.innerHTML = '<option selected disabled value="">-- Select PO --</option>';
                        data.forEach(function (po) {
                            pos.innerHTML += `<option value="${po.OrderNo}">${po.OrderNo}</option>`;
                        });
                        pos.disabled = false;
                    });
            }
        });

        // Handle PO change
        pos.addEventListener('change', function () {
            const selectedPO = pos.options[pos.selectedIndex].value;
            viewPOButton.disabled = !selectedPO;

            grns.innerHTML = '<option selected disabled value="">Loading GRN...</option>';

            if (selectedPO) {
                fetch(`/finance/finance/grns/${selectedPO}`)
                    .then(response => response.json())
                    .then(data => {
                        //console.log("GRNDATA", data);

                        if (data.length === 0) {
                            grns.innerHTML = '<option selected disabled>No GRN found</option>';
                            grns.disabled = true;
                            return;
                        }

                        grns.innerHTML = '<option selected disabled value="">-- Select GRN --</option>';
                        data.forEach(function (grn) {
                            grns.innerHTML += `<option value="${grn.GRNID}">${grn.GRNID}</option>`;
                        });
                        viewPOButton.disabled = false;
                        grns.disabled = false;
                    })
            }
        });

        // View PO details in modal
        viewPOButton.addEventListener('click', function () {
            const selectedPO = pos.options[pos.selectedIndex].value;
            if (selectedPO) {
                fetch(`/finance/finance/viewpo/${selectedPO}`)
                    .then(response => response.json())
                    .then(data => {
                        //console.log("PO Details", data);
                        const poDetailsContent = document.getElementById('poDetailsContent');
                        poDetailsContent.innerHTML = '';

                        // Populate modal with PO details
                        poDetailsContent.innerHTML = `
                        <h5>PO Number: ${data.OrderNo}</h5>
                        <p><strong>Vendor:</strong> ${data.SupplierName}</p>
<!--                        <p><strong>Date:</strong> ${data.OrderDate}</p>-->
<!--                        <h6>Items:</h6>-->
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Unit Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.items.map(item => `
                                        <tr>
                                            <td>${item.ItemName}</td>
                                            <td>${item.Quantity}</td>
                                            <td>${item.UnitCost}</td>
                                        </tr>`).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;

                        // Show the modal
                        new bootstrap.Modal(viewPOModal).show();
                    });
            }
        });
    </script>
@endsection

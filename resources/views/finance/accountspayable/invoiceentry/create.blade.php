@extends('layouts.app')
@section('title', 'Create Payables Invoice')

@section('content')
<div class="card shadow p-4 rounded-4">
{{--    <h4 class="mb-3">🧾 Create Payables Invoice</h4>--}}

    @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
    @endif

    <form action="{{route('invoiceentry.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Invoice Number<span class="text-danger">*</span></label>
                <input type="text" name="InvoiceNumber" class="form-control" placeholder="e.g. INV-1001" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Vendor<span class="text-danger">*</span></label>
                <select id="VendorSelect" name="SupplierID" class="form-control" required>
                    <option disabled selected value="">-- Select Vendor --</option>
                    @foreach($suppliers as $item)
                        <option value="{{ $item->Id }}">{{ $item->SupplierName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Currency<span class="text-danger">*</span></label>
                <select name="CurrencyID" class="form-control" required>
                    <option selected disabled value="">-- Select Currency --</option>
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->Id }}">{{ $currency->Code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Exchange Rate</label>
                <input type="number" step="0.0001" name="ExchangeRate" class="form-control" value="1.0000" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="POReference" class="form-label">PO Reference<span class="text-danger">*</span></label>
                <div class="d-flex align-items-center gap-2">
                    <select id="POReference" class="form-select" name="POReference" style="flex: 1;" required>
                    <option selected disabled value="">-- Select PO --</option>
                    @foreach($orders as $po)
                        <option value="{{ $po->Id }}">{{ $po->OrderNo}}</option>
                    @endforeach
                    </select>
                    <button id="ViewPOButton" type="button" class="btn btn-outline-primary">View PO</button>
                </div>
                <small class="form-text text-muted">A Vendor must first be selected.</small>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">GRN Reference<span class="text-danger">*</span></label>
                <select id="GRNReference" name="GRNReference" class="form-control" required>
                    <option selected disabled value="">-- Select GRN --</option>
                    @foreach($grns as $grn)
                        <option value="{{ $grn->id }}">{{ $grn->GRNID }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">A PO must first be selected.</small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Invoice Date<span class="text-danger">*</span></label>
                <input type="date" name="InvoiceDate" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Invoice Amount<span class="text-danger">*</span></label>
                <input min="0.00" type="number" step="0.01" name="InvoiceAmount" class="form-control" placeholder="e.g. 1000.00" required>
            </div>


            <div class="mb-3">
                <label class="form-label">Invoice Description</label>
                <textarea name="Description" class="form-control" rows="3" placeholder="Enter invoice description"></textarea>
            </div>
        </div>

        <h5 class="mb-3">📤 Upload EDI File (Optional)</h5>
        <div class="mb-3">
            <input type="file"
                   name="file"
                   class="form-control"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.png,.jpg,.jpeg">
            <small class="form-text text-muted">
                Allowed types: PDF, Word, Excel, CSV, JPG, PNG
            </small>
        </div>

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{route('invoiceentry.index')}}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='💾Saving...'; this.form.submit();}">💾 Save Invoice</button>
        </div>

        {{--View PO Modal--}}
        <div class="modal fade" id="viewPO" tabindex="-1" aria-labelledby="viewPOTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered  ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewPOTitle">Purchase Order Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="poDetailsContent">
                            <!-- PO details will be dynamically loaded here -->
                            <p>Loading PO details...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
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

        // Disable PO and GRN selects initially
        pos.disabled = true;
        grns.disabled = true;
        viewPOButton.disabled = true;

        // Handle vendor change
        vendor.addEventListener('change', function(){
            const selectedVendor = vendor.options[vendor.selectedIndex].value;
            pos.disabled = !selectedVendor;

            pos.innerHTML = '<option selected disabled value="">Loading...</option>';

            if (selectedVendor){
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
                        data.forEach(function(po){
                            pos.innerHTML += `<option value="${po.OrderNo}">${po.OrderNo}</option>`;
                        });
                        pos.disabled = false;
                    });
            }
        });

        // Handle PO change
        pos.addEventListener('change', function(){
            const selectedPO = pos.options[pos.selectedIndex].value;
            viewPOButton.disabled = !selectedPO;

            grns.innerHTML = '<option selected disabled value="">Loading GRN...</option>';

            if (selectedPO){
                fetch(`/finance/finance/grns/${selectedPO}`)
                    .then(response => response.json())
                    .then(data =>{
                        //console.log("GRNDATA", data);

                        if (data.length === 0) {
                            grns.innerHTML = '<option selected disabled>No GRN found</option>';
                            grns.disabled = true;
                            return;
                        }

                        grns.innerHTML = '<option selected disabled value="">-- Select GRN --</option>';
                        data.forEach(function(grn){
                            grns.innerHTML += `<option value="${grn.GRNID}">${grn.GRNID}</option>`;
                        });
                        viewPOButton.disabled = false;
                        grns.disabled = false;
                    })
            }
        });

        // View PO details in modal
        viewPOButton.addEventListener('click', function() {
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

@extends('layouts.app')
@section('title', 'Create Payables Invoice')
 
@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-3">🧾 Create Payables Invoice</h4>

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
        
        {{--  
                <hr>
        
                <h5 class="mb-3">📦 Line Items</h5>
                <table class="table table-bordered" id="lineItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Tax</th>
                            <th>GL Account</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input name="lines[0][Description]" class="form-control" value="Stationery - Pens"></td>
                            <td><input name="lines[0][Quantity]" type="number" step="0.01" class="form-control" value="10"></td>
                            <td><input name="lines[0][UnitCost]" type="number" step="0.01" class="form-control" value="100"></td>
                            <td>
                                <select name="lines[0][TaxID]" class="form-control">
                                    <option value="">-- None --</option>
                                    <option value="1">VAT 16%</option>
                                    <option value="2">WHT 5%</option>
                                </select>
                            </td>
                            <td>
                                <select name="lines[0][GLAccountID]" class="form-control">
                                    <option value="5001">5001 - Office Supplies</option>
                                    <option value="5002">5002 - Admin Expenses</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">🗑️</button>
                            </td>
                        </tr>
                    </tbody>
                </table> --}}
        
                {{-- <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addRow()">➕ Add Line</button>
                </div>
        --}}
        <hr>
 
        <h5 class="mb-3">📤 Upload EDI File (Optional)</h5>
        <div class="mb-3">
            <input type="file" name="edi_file" class="form-control">
            <small class="form-text text-muted">Supports CSV/Excel import. Parse and map lines in controller.</small>
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

    //disable PO and GRN selects initially
    pos.disabled = true;
    
    vendor.addEventListener('change', function(){
        const selectedVendor = vendor.options[vendor.selectedIndex].value;
        pos.disabled = !selectedVendor;

        pos.innerHTML = '<option selected disabled value="">Loading...</option>'; // Clear PO options

        if (selectedVendor){
            fetch(`/finance/finance/pos/${selectedVendor}`)
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                
                    pos.innerHTML = '<option selected disabled value="">-- Select PO --</option>';
                    data.forEach(function(po){
                        console.log("Data1",po.Id);
                        
                        pos.innerHTML += `<option value="${po.Id}">${po.OrderNo}</option>`;
                    });
                    pos.disabled = false; // Enable PO select if vendor is selected
                });    
        }
    });

    // Disable View PO button initially
    viewPOButton.disabled = true;
    grns.disabled = true;
     
    pos.addEventListener('change', function(){
        const selectedPO = pos.options[pos.selectedIndex].value;
        viewPOButton.disabled = !selectedPO;

        grns.innerHTML = '<option selected disabled value="">Loading GRN...</option>'; // Clear GRN options

        if (selectedPO){
            fetch(`/finance/finance/grns/${selectedPO}`)
                .then(response => response.json())
                .then(data =>{
                        console.log("GRNDATA",data);
                    grns.innerHTML = '<option selected disabled value="">-- Select GRN --</option>';
                    data.forEach(function(grn){
                        
                        grns.innerHTML += `<option value="${grn.GRNID}">${grn.GRNID}</option>`;
                        });
                    viewPOButton.disabled = false; // Enable button if PO is selected
                    grns.disabled = false; // Enable GRN select if PO is selected
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
                    console.log("PO Details", data);
                    const poDetailsContent = document.getElementById('poDetailsContent');
                    poDetailsContent.innerHTML = ''; // Clear previous content

                    // Populate modal with PO details
                    poDetailsContent.innerHTML = `
                        <h5>PO Number: ${data.OrderNo}</h5>
                        <p><strong>Vendor:</strong> ${data.SupplierName}</p>
                        <p><strong>Date:</strong> ${data.OrderDate}</p> 
                        <h6>Items:</h6>
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
                    `;

                    // Show the modal
                    new bootstrap.Modal(viewPOModal).show();
                });
        }
    });
</script>
@endsection
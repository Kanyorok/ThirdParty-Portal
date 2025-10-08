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
                <select id="VendorSelect" name="ThirdPartyID" class="form-control" required>
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
                <div class="d-flex align-items-center gap-2">
                    <select id="GRNReference" name="GRNReference" class="form-select" style="flex:1;" required>
                        <option selected disabled value="">-- Select GRN --</option>
                        @foreach($grns as $grn)
                            <option value="{{ $grn->id }}">{{ $grn->GRNID }}</option>
                        @endforeach
                    </select>
                    <button id="ViewGRNButton" type="button" class="btn btn-outline-secondary">View GRN</button>
                </div>
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
                        <div id="poDetailsContent"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        {{--View GRN Modal--}}
        <div class="modal fade" id="viewGRN" tabindex="-1" aria-labelledby="viewGRNTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered  ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewGRNTitle">GRN Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="grnDetailsContent"></div>
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
        const viewGRNButton = document.getElementById('ViewGRNButton');
        const viewGRNModal = document.getElementById('viewGRN');

        // Disable PO and GRN selects initially
        pos.disabled = true;
        grns.disabled = true;
        viewPOButton.disabled = true;
        if (viewGRNButton) viewGRNButton.disabled = true;

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
                        // Sort POs for consistency and faster selection
                        data.sort((a,b) => (a.OrderNo||'').localeCompare(b.OrderNo||''));
                        const frag = document.createDocumentFragment();
                        data.forEach(function(po){
                            const opt = document.createElement('option');
                            opt.value = po.OrderNo;
                            opt.textContent = po.OrderNo;
                            frag.appendChild(opt);
                        });
                        pos.innerHTML = '<option selected disabled value="">-- Select PO --</option>';
                        pos.appendChild(frag);
                        pos.disabled = false;
                    });
            }
        });

        // Handle PO change
        pos.addEventListener('change', function(){
            const selectedPO = pos.options[pos.selectedIndex].value;
            viewPOButton.disabled = !selectedPO;
            if (viewGRNButton) viewGRNButton.disabled = !selectedPO;

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

                        // Optimize GRN populate
                        data.sort((a,b) => (a.GRNID||'').localeCompare(b.GRNID||''));
                        const gfrag = document.createDocumentFragment();
                        data.forEach(function(grn){
                            const opt = document.createElement('option');
                            opt.value = grn.GRNID;
                            opt.textContent = grn.GRNID;
                            gfrag.appendChild(opt);
                        });
                        grns.innerHTML = '<option selected disabled value="">-- Select GRN --</option>';
                        grns.appendChild(gfrag);
                        viewPOButton.disabled = false;
                        if (viewGRNButton) viewGRNButton.disabled = false;
                        grns.disabled = false;
                    })
            }
        });

        // Helper: show loading spinner content
        function setLoading(targetId, title) {
            const el = document.getElementById(targetId);
            el.innerHTML = `
                <div class="d-flex align-items-center justify-content-center py-4">
                    <div class="spinner-border text-primary me-2" role="status" aria-hidden="true"></div>
                    <span>Loading ${title}...</span>
                </div>`;
        }

        // View PO details in modal
        viewPOButton.addEventListener('click', function() {
            const selectedPO = pos.options[pos.selectedIndex].value;
            if (selectedPO) {
                setLoading('poDetailsContent', 'PO');
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
                                        <th class="text-end">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.items.map(item => `
                                        <tr>
                                            <td>${item.ItemName}</td>
                                            <td>${item.Quantity}</td>
                                            <td>${item.UnitCost}</td>
                                            <td class="text-end">${(parseFloat(item.UnitCost||0) * parseFloat(item.Quantity||0)).toFixed(2)}</td>
                                        </tr>`).join('')}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">PO Subtotal</th>
                                        <th class="text-end">${data.items.reduce((sum, it) => sum + (parseFloat(it.UnitCost||0) * parseFloat(it.Quantity||0)), 0).toFixed(2)}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    `;

                        // Show the modal immediately for perceived performance
                        new bootstrap.Modal(viewPOModal).show();
                    });
            }
        });

        // View GRN details in modal
        if (viewGRNButton) viewGRNButton.addEventListener('click', function() {
            const selectedGRN = grns.options[grns.selectedIndex]?.value;
            if (selectedGRN) {
                setLoading('grnDetailsContent', 'GRN');
                fetch(`/finance/finance/viewgrn/${selectedGRN}`)
                    .then(response => response.json())
                    .then(data => {
                        const grnDetailsContent = document.getElementById('grnDetailsContent');
                        grnDetailsContent.innerHTML = '';

                        grnDetailsContent.innerHTML = `
                        <h5>GRN: ${data.GRNID}</h5>
                        <p><strong>Vendor:</strong> ${data.SupplierName}</p>
                        <p><strong>PO:</strong> ${data.POID}</p>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
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
                                <tfoot>
                                    <tr>
                                        <th class="text-end">Totals:</th>
                                        <th>${data.totals.ordered}</th>
                                        <th>${data.totals.received}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    `;

                        new bootstrap.Modal(viewGRNModal).show();
                    });
            }
        });

        // Auto-select GRN when a single GRN exists for a PO
        pos.addEventListener('change', function(){
            // after GRN fetch completes (handled above)
            setTimeout(() => {
                const opts = grns.querySelectorAll('option');
                const valid = Array.from(opts).filter(o => o.value && o.disabled !== true);
                if (valid.length === 1) {
                    grns.value = valid[0].value;
                    grns.disabled = true; // lock since one-to-one
                } else {
                    grns.disabled = false;
                }
            }, 250);
        });
    </script>
@endsection

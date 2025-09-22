@extends('layouts.app')
@section('title', 'Purchase Order')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }

        /*input,*/
        /*textarea {*/
        /*    background: transparent;*/
        /*    !*border: none; !* optional: removes border too *!*!*/
        /*    outline: none; !* optional: removes outline on focus *!*/
        /*    box-shadow: none; !* optional: removes inner shadows *!*/
        /*}*/

    </style>
@endsection
@section('content')
    {{--    <div class="mb-3"> --}}
    {{--        <h1 class="h3 d-inline align-middle">@yield('title')</h1> --}}
    {{--    </div> --}}

    <div class="container">
        <div class="d-flex justify-content-end align-items-center my-3">
            <a href="{{ route('purchaseOrder.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to Orders
            </a>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>There were some problems with your input:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('purchaseOrder.store') }}" method="post" id="purchaseOrdersForm" novalidate>
            @csrf
            <!-- Source Selector -->
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label fw-bold">Source</label>
                    <div class="d-flex gap-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="SourceType" id="srcDirect" value="DIRECT" checked>
                            <label class="form-check-label" for="srcDirect">Direct</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="SourceType" id="srcRFQ" value="RFQ">
                            <label class="form-check-label" for="srcRFQ">RFQ</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="SourceType" id="srcTender" value="TENDER">
                            <label class="form-check-label" for="srcTender">Tender</label>
                        </div>
                    </div>
                    <input type="hidden" name="SourceId" id="SourceId" />
                </div>
            </div>

            <!-- RFQ Selection -->
            <div class="row mb-4 source-rfq d-none">
                <div class="col-md-4">
                    <label>Reference Number (RFQ) <span class="text-danger">*</span></label>
                    <select class="form-control refNo @error('refNo') is-invalid @enderror" name="refNo" id="refNo">
                        <option selected disabled>Select RFQ</option>
                        @foreach($awardedRfqs as $ar)
                            @php $disabled = in_array($ar->Id, $convertedRFQIds ?? []) ? 'disabled' : ''; @endphp
                            <option value="{{ $ar->RFQNumber }}" data-rfq-id="{{ $ar->Id }}" data-supplier-id="{{ $ar->SupplierId }}" {{ $disabled }}>{{ $ar->RFQNumber }}</option>
                        @endforeach
                        @php $awardedNos = collect($awardedRfqs ?? [])->pluck('RFQNumber')->toArray(); @endphp
                        @foreach($rfqs as $rfq)
                            @if(!in_array($rfq->RFQNumber, $awardedNos))
                                <option value="{{ $rfq->RFQNumber }}">{{ $rfq->RFQNumber }} (no award)</option>
                            @endif
                        @endforeach
                    </select>
                    @error('refNo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label>LPO Number <span class="text-danger">*</span></label>
                    <input type="text" name="LPONo" class="form-control @error('LPONo') is-invalid @enderror" value="{{ old('LPONo', uniqid('LPO-')) }}" readonly required/>
                    @error('LPONo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label>Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control poDate @error('pODate') is-invalid @enderror" name="pODate" value="{{ old('pODate', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required/>
                    @error('pODate')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Tender Selection -->
            <div class="row mb-4 source-tender d-none">
                <div class="col-md-4">
                    <label>Tender No <span class="text-danger">*</span></label>
                    <select class="form-control" id="tenderNo">
                        <option selected disabled>Select Tender</option>
                        @foreach(($awardedTenders ?? []) as $t)
                            @php $disabled = in_array($t->Id, ($convertedTenderIds ?? [])) ? 'disabled' : ''; @endphp
                            <option value="{{ $t->TenderNo }}" data-tender-id="{{ $t->Id }}" data-supplier-id="{{ $t->SupplierId }}" {{ $disabled }}>{{ $t->TenderNo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>LPO Number <span class="text-danger">*</span></label>
                    <input type="text" name="LPONo" class="form-control" value="{{ old('LPONo', uniqid('LPO-')) }}" readonly required/>
                </div>
                <div class="col-md-4">
                    <label>Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control poDate" name="pODate" value="{{ old('pODate', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required/>
                </div>
            </div>

            <!-- Supplier & Details -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label>Supplier <span class="text-danger">*</span></label>
                    <select class="form-control supplier @error('supplier') is-invalid @enderror" id="supplier" name="supplier">
                        <option selected disabled>Select supplier</option>
                    </select>
                    <input type="hidden" id="supplierHidden" />
                    @error('supplier')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label>Address</label>
                    <input type="text" class="form-control" name="address" placeholder="Supplier address" readonly/>
                </div>
            </div>

            <!-- Direct mode helpers -->
            <div class="row mb-3 direct-only d-none">
                <div class="col-md-6">
                    <label>Item Category (optional)</label>
                    <select id="itemCategory" class="form-control">
                        <option value="" selected>-- None --</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Prequalified Suppliers (helper)</label>
                    <select id="preqSupplierHelper" class="form-control">
                        <option value="" selected>-- None --</option>
                    </select>
                    <small class="text-muted">This is a helper. You can still type a different supplier.</small>
                </div>
            </div>

            <!-- Other LPO Details -->
            <div class="row mb-4">
                <div class="col-md-4 mt-2">
                    <label>Priority <span class="text-danger">*</span></label>
                    <select class="form-control priority @error('priority') is-invalid @enderror" name="priority" required>
                        <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Medium" {{ old('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mt-2">
                    <label>Payment Terms <span class="text-danger">*</span></label>
                    <select class="form-control terms @error('terms') is-invalid @enderror" name="terms" id="terms" required>
                        <option selected disabled>Select Payment Term</option>
                        @foreach (($paymentTerms ?? []) as $term)
                            <option value="{{ $term->ID }}" {{ old('terms') == $term->ID ? 'selected' : '' }}>{{ $term->Description }}</option>
                        @endforeach
                    </select>
                    @error('terms')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-outline-primary" id="add-row">
                + Add Item
            </button>
        </div>


            <!-- Line Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered" id="line-items-table">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 3%; min-width: 30px;">#</th>
                        <th style="width: 15%; min-width: 150px;">Item Name <span class="text-danger">*</span></th>
                        <th style="width: 20%; min-width: 200px;">Item Description</th>
                        <th style="width: 5%; min-width: 80px;">Quantity <span class="text-danger">*</span></th>
                        <th style="width: 10%; min-width: 100px;">Unit Price <span class="text-danger">*</span></th>
                        <th style="width: 5%; min-width: 80px;">Tax %</th>
                        <th style="width: 5%; min-width: 80px;">Discount %</th>
                        <th style="width: 15%; min-width: 150px;">Line Total</th>
                    </tr>
                    </thead>
                    <tbody id="item-rows">
                    <tr>
                        <td class="line-no">1.</td>
                        <td class="text-start">
                            <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item" required>
                                <option disabled selected>Select Item Code</option>
                            </select>
                        </td>
                        <td class="text-start">
                            <textarea class="form-control form-control-sm itemDescription" name="itemDescription[]"
                                      id="Description" cols="30"
                                      rows="5" readonly
                                      style="display: flex; align-items: center; justify-content: center; text-align: center; padding: 0; resize: none;"></textarea>
                        </td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm qty quantity"
                                                      name="quantity[]" id="Quantity" step="any" required></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm unit-price "
                                                      name="unitPrice[]" id="Price" step="any" required></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm tax"
                                                      name="tax[]" id="Tax" step="any"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm discount"
                                                      name="discount[]" id="Discount" step="any"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm line-total"
                                                      name="lineTotal[]" id="lineTotal" step="any" readonly></td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Item"><i class="fa fa-trash"></i> Remove</button>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>

            <!-- Optional Note -->
            <div class="mb-4">
                <label>Line Note</label>
                <textarea class="form-control" rows="3" placeholder="Optional message to supplier"></textarea>
            </div>

            <!-- Totals -->
            <div class="row mb-4">
                <div class="col-md-4 offset-md-8">
                    <div class="mb-2">
                        <label>Exclusive Total</label>
                        <input type="text" class="form-control exclusiveTotal" name="exclusiveTotal" readonly/>
                    </div>
                    <div class="mb-2">
                        <label>Tax Amount</label>
                        <input type="text" class="form-control taxAmount" name="taxAmount" readonly/>
                    </div>
                    <div>
                        <label>Inclusive Total</label>
                        <input type="text" class="form-control inclusiveTotal" name="inclusiveTotal" readonly/>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary me-2" id="saveOrder">Save</button>
                {{--            <button class="btn btn-warning me-2">Edit</button> --}}
                {{--            <button class="btn btn-danger">Delete</button> --}}
            </div>
        </form>
    </div>

@endsection
@section('scripts')

    <script>
        // Prepare RFQ responses for JS (for supplier filtering)
        const rfqResponses = @json($rfqResponses);
        const convertedRFQIds = @json($convertedRFQIds);
    </script>

    <script>
        const itemTypeOptions = ``;
    </script>

    <script>

        // Source mode toggling
        function applySourceMode() {
            const mode = $('input[name="SourceType"]:checked').val();
            if (mode === 'RFQ') {
                $('.source-rfq').removeClass('d-none');
                $('.source-tender').addClass('d-none');
                $('#supplier').prop('disabled', true); // auto in RFQ
                $('.direct-only').addClass('d-none');
                // Load awarded RFQs live to ensure latest awards appear
                const $ref = $('#refNo');
                $ref.empty().append('<option selected disabled>Loading awarded RFQs...</option>');
                fetch('/procurement/purchase-order/awarded-rfqs')
                    .then(r => r.json())
                    .then(({success, data}) => {
                        $ref.empty().append('<option selected disabled>Select RFQ</option>');
                        if (!success) return;
                        const converted = (window.convertedRFQIds || []);
                        const awardedNos = new Set();
                        (data || []).forEach(ar => {
                            if (!ar) return;
                            const dis = converted.includes(ar.Id) ? 'disabled' : '';
                            awardedNos.add(ar.RFQNumber);
                            $ref.append(`<option value="${ar.RFQNumber}" data-rfq-id="${ar.Id}" data-supplier-id="${ar.SupplierId}" ${dis}>${ar.RFQNumber}</option>`);
                        });
                        // Also append evaluated-only RFQs that have no award marker
                        (window.rfqResponses || []).forEach(r => {
                            if (!awardedNos.has(r.RFQNumber)) {
                                if ($ref.find(`option[value='${r.RFQNumber}']`).length === 0) {
                                    $ref.append(`<option value="${r.RFQNumber}">${r.RFQNumber} (no award)</option>`);
                                }
                            }
                        });
                    })
                    .catch(() => {
                        // On failure, leave whatever was server-rendered
                        // and do not block the user
                    });
            } else if (mode === 'TENDER') {
                $('.source-rfq').addClass('d-none');
                $('.source-tender').removeClass('d-none');
                $('#supplier').prop('disabled', true);
                $('.direct-only').addClass('d-none');
                const $ref = $('#tenderNo');
                $ref.empty().append('<option selected disabled>Loading awarded tenders...</option>');
                fetch('/procurement/purchase-order/awarded-tenders')
                    .then(r => r.json())
                    .then(({success, data}) => {
                        $ref.empty().append('<option selected disabled>Select Tender</option>');
                        if (!success) return;
                        const converted = (window.convertedTenderIds || []);
                        (data || []).forEach(t => {
                            const dis = converted.includes(t.Id) ? 'disabled' : '';
                            $ref.append(`<option value="${t.TenderNo}" data-tender-id="${t.Id}" data-supplier-id="${t.SupplierId}" ${dis}>${t.TenderNo}</option>`);
                        });
                    })
                    .catch(() => {});
            } else {
                $('.source-rfq').addClass('d-none');
                $('.source-tender').addClass('d-none');
                $('#supplier').prop('disabled', false);
                $('#SourceId').val('');
                $('.direct-only').removeClass('d-none');
            }
        }
        $(document).on('change', 'input[name="SourceType"]', applySourceMode);
        applySourceMode();

        // RFQ selection: auto-fill supplier, set SourceId, prevent duplicates
        $(document).on('change', '#refNo', function () {
            const selectedRFQNo = $(this).val();
            const rfqOption = $(this).find('option:selected');
            const rfqId = parseInt(rfqOption.data('rfq-id'));
            const awardedSupplierId = parseInt(rfqOption.data('supplier-id'));
            if (!isNaN(rfqId)) {
                $('#SourceId').val(rfqId);
            } else {
                // No award record; allow selection but don't set SourceId yet
                $('#SourceId').val('');
            }

            if (!isNaN(rfqId) && convertedRFQIds && convertedRFQIds.includes(rfqId)) {
                alert('This RFQ has already been converted to an LPO.');
                $(this).val('');
                $('#SourceId').val('');
                return;
            }

            const $supplier = $('#supplier');
            $supplier.empty().append('<option selected disabled>Select supplier</option>');

            // If we have an awarded supplier, lock it; otherwise list suppliers in responses
            if (!isNaN(awardedSupplierId)) {
                const awardResp = rfqResponses.find(r => (r.RFQNumber === selectedRFQNo) && (parseInt(r.SupplierId) === awardedSupplierId));
                const displayName = awardResp ? (awardResp.TradingName || awardResp.SupplierName || awardResp.Name) : `Supplier #${awardedSupplierId}`;
                const address = awardResp ? (awardResp.Address || awardResp.TradingAddress || '') : '';
                $supplier.append(`<option value="${awardedSupplierId}" selected data-address="${address}">${displayName}</option>`);
                $supplier.prop('disabled', true);
                $('input[name="address"]').val(address);
                // Ensure a value is posted under required name
                $('<input>').attr({type:'hidden', name:'supplier', value:String(awardedSupplierId)}).appendTo('#purchaseOrdersForm');
            } else {
                // No award: list suppliers from responses for this RFQ
                const suppliers = rfqResponses.filter(r => r.RFQNumber === selectedRFQNo);
                const seen = new Set();
                suppliers.forEach(s => {
                    const key = `${s.SupplierName || s.Name}-${s.SupplierId || s.Id}`;
                    if (seen.has(key)) return;
                    seen.add(key);
                    const addr = s.Address || '';
                    $supplier.append(`<option value="${s.SupplierId || s.Id}" data-address="${addr}">${s.SupplierName || s.Name}</option>`);
                });
                $supplier.prop('disabled', false);
                $('input[name="address"]').val('');

                // Show helper note: no award
                alert('This RFQ has no recorded award. Please select the supplier and items manually.');
            }

            // Fetch awarded items and populate line items
            const itemsUrl = !isNaN(awardedSupplierId) && !isNaN(rfqId)
                ? `/procurement/purchase-order/rfq-items/${rfqId}?supplierId=${awardedSupplierId}`
                : '';
            if (!itemsUrl) return;
            fetch(itemsUrl)
                .then(r => r.json())
                .then(({items}) => {
                if (!items || !items.length) return;
                    const $tbody = $('#item-rows');
                    $tbody.empty();
                    items.forEach((it, idx) => {
                        const rowNo = idx + 1;
                        const html = `
                            <tr>
                                <td class="line-no">${rowNo}.</td>
                                <td class="text-start">
                                    <select class="form-select form-select-sm itemCode" name="itemCode[]" required>
                                        <option value="${it.itemCode}" selected>${it.itemName}</option>
                                    </select>
                                </td>
                                <td class="text-start">
                                    <textarea class="form-control form-control-sm itemDescription" name="itemDescription[]" rows="2" readonly>${it.itemName}</textarea>
                                </td>
                                <td class="text-start"><input type="number" class="form-control form-control-sm qty quantity" name="quantity[]" step="any" value="${it.quantity}" required></td>
                                <td class="text-start"><input type="number" class="form-control form-control-sm unit-price" name="unitPrice[]" step="any" value="${it.unitPrice || 0}" required></td>
                                <td class="text-start"><input type="number" class="form-control form-control-sm tax" name="tax[]" step="any"></td>
                                <td class="text-start"><input type="number" class="form-control form-control-sm discount" name="discount[]" step="any"></td>
                                <td class="text-start"><input type="number" class="form-control form-control-sm line-total" name="lineTotal[]" step="any" readonly></td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Item"><i class="fa fa-trash"></i> Remove</button>
                                </td>
                            </tr>`;
                        $tbody.append(html);
                    });
                })
                .catch(console.error);
        });

        // Tender selection: auto-fill supplier, set SourceId, fetch items
        $(document).on('change', '#tenderNo', function () {
            const tenderNo = $(this).val();
            const opt = $(this).find('option:selected');
            const tenderId = parseInt(opt.data('tender-id'));
            const supplierId = parseInt(opt.data('supplier-id'));
            if (!isNaN(tenderId)) {
                $('#SourceId').val(tenderId);
            } else {
                $('#SourceId').val('');
            }

            const $supplier = $('#supplier');
            $supplier.empty().append('<option selected disabled>Select supplier</option>');

            if (!isNaN(supplierId)) {
                // Try fetch supplier list to resolve display name/address
                fetch('/procurement/purchaseOrder/getSuppliers')
                    .then(r => r.json())
                    .then(({success, data}) => {
                        let name = `Supplier #${supplierId}`;
                        let addr = '';
                        if (success && Array.isArray(data)) {
                            const found = data.find(s => parseInt(s.SupplierId) === supplierId || parseInt(s.Id) === supplierId);
                            if (found) {
                                name = found.SupplierName || found.Name || name;
                                addr = found.Address || '';
                            }
                        }
                        $supplier.append(`<option value="${supplierId}" selected data-address="${addr}">${name}</option>`);
                        $supplier.prop('disabled', true);
                        $('input[name="address"]').val(addr);
                        $('<input>').attr({type:'hidden', name:'supplier', value:String(supplierId)}).appendTo('#purchaseOrdersForm');
                    })
                    .catch(() => {
                        $supplier.append(`<option value="${supplierId}" selected>Supplier #${supplierId}</option>`);
                        $supplier.prop('disabled', true);
                        $('<input>').attr({type:'hidden', name:'supplier', value:String(supplierId)}).appendTo('#purchaseOrdersForm');
                    });
            }

            if (!isNaN(tenderId)) {
                fetch(`/procurement/purchase-order/tender-items/${tenderId}?supplierId=${supplierId || ''}`)
                    .then(r => r.json())
                    .then(({items}) => {
                        if (!items || !items.length) return;
                        const $tbody = $('#item-rows');
                        $tbody.empty();
                        items.forEach((it, idx) => {
                            const rowNo = idx + 1;
                            const html = `
                                <tr>
                                    <td class="line-no">${rowNo}.</td>
                                    <td class="text-start">
                                        <select class="form-select form-select-sm itemCode" name="itemCode[]" required>
                                            <option value="${it.itemCode}" selected>${it.itemName}</option>
                                        </select>
                                    </td>
                                    <td class="text-start">
                                        <textarea class="form-control form-control-sm itemDescription" name="itemDescription[]" rows="2" readonly>${it.itemName}</textarea>
                                    </td>
                                    <td class="text-start"><input type="number" class="form-control form-control-sm qty quantity" name="quantity[]" step="any" value="${it.quantity}" required></td>
                                    <td class="text-start"><input type="number" class="form-control form-control-sm unit-price" name="unitPrice[]" step="any" value="${it.unitPrice || 0}" required></td>
                                    <td class="text-start"><input type="number" class="form-control form-control-sm tax" name="tax[]" step="any"></td>
                                    <td class="text-start"><input type="number" class="form-control form-control-sm discount" name="discount[]" step="any"></td>
                                    <td class="text-start"><input type="number" class="form-control form-control-sm line-total" name="lineTotal[]" step="any" readonly></td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Item"><i class="fa fa-trash"></i> Remove</button>
                                    </td>
                                </tr>`;
                            $tbody.append(html);
                        });
                    })
                    .catch(console.error);
            }
        });

        // Load item categories for direct helper
        function loadItemCategories() {
            fetch('/procurement/supplier-categories')
                .then(r => r.json())
                .then(list => {
                    const $cat = $('#itemCategory');
                    list.forEach(c => $cat.append(`<option value="${c.SupplierCategoryID || c.Id}">${c.Description || c.Name}</option>`))
                })
                .catch(console.error)
        }
        loadItemCategories();

        // Payment terms are server-rendered from t_CodeDetails(CodeID='PaymentTerm')

        // On category change, fetch prequalified suppliers helper
        $(document).on('change', '#itemCategory', function () {
            const catId = $(this).val();
            const $helper = $('#preqSupplierHelper');
            $helper.empty().append('<option value="">-- None --</option>');
            if (!catId) return;
            fetch(`/procurement/purchase-order/prequalified-suppliers/${catId}`)
                .then(r => r.json())
                .then(({success, data}) => {
                    if (!success) return;
                    data.forEach(s => $helper.append(`<option value="${s.SupplierId}" data-address="${s.Address || ''}">${s.SupplierName}</option>`))
                })
                .catch(console.error)
        });

        // When user picks a helper supplier, set main supplier
        $(document).on('change', '#preqSupplierHelper', function () {
            const supplierId = $(this).val();
            const name = $(this).find('option:selected').text();
            const address = $(this).find('option:selected').data('address') || '';
            if (!supplierId) return;
            const $supplier = $('#supplier');
            $supplier.empty().append('<option selected disabled>Select supplier</option>');
            $supplier.append(`<option value="${supplierId}" selected data-address="${address}">${name}</option>`);
            $('input[name="address"]').val(address);
        });

        // Autofill item details (UOM/price) when item code changes (Direct mode)
        $(document).on('change', '#item-rows .itemCode', function () {
            const code = $(this).val();
            const $row = $(this).closest('tr');
            if (!code) return;
            fetch(`/procurement/purchase-order/items/${code}`)
                .then(r => r.json())
                .then(({success, data}) => {
                    if (!success) return;
                    $row.find('.unit-price').val(data.UnitPrice || 0);
                    $row.find('.itemDescription').val(data.Description || '');
                })
                .catch(console.error)
        });

        // Autopopulate address when supplier is selected (no AJAX needed)
        $(document).on('change', '#supplier', function () {
            let address = $(this).find('option:selected').data('address') || '';
            $('input[name="address"]').val(address);
        });

        // $(document).on('change','#supplier',function () {
        //     fetchSuppliers();
        //
        //
        //     alert('eric');
        // });

        $(function () {
            // Removed item type change handler (no longer used)
            $('form#purchaseOrdersForm').submit(async function (e) {
                // alert($('#RequisitionID').val());
                e.preventDefault();
                // Ensure supplier is included when select is disabled
                const supVal = $('#supplier').val();
                if (supVal && $("input[name='supplier']").length === 0) {
                    $('<input>').attr({type:'hidden', name:'supplier', value:String(supVal)}).appendTo('#purchaseOrdersForm');
                }
                // Ensure refNo is provided to backend (use LPONo)
                const lpoNo = $('input[name="LPONo"]').first().val();
                if (lpoNo && $("input[name='refNo']").length === 0) {
                    $('<input>').attr({type:'hidden', name:'refNo', value:String(lpoNo)}).appendTo('#purchaseOrdersForm');
                }
                if (await saveForm($(this), $('#saveOrder'), true, true, true)) {
                    $Modal.modal('hide');
                }

            });


            // Type-based item filtering removed

            ////fetching suppliers


            // Handle item code change using event delegation
            $(document).on('change', '.itemCode', function () {
                let row = $(this).closest('tr');
                let itemId = $(this).val();

                if (itemId !== '') {
                    $.ajax({
                        url: `/procurement/requisitionItem/getItemDetails/${itemId}`,
                        type: 'GET',
                        success: function (response) {
                            if (response.data && response.data.length > 0) {
                                $.each(response.data, function (key, item) {
                                    row.find('.itemDescription').val(item.ItemDescription ||
                                        '');
                                    row.find('.unit-price').val(item.UnitPrice || '');
                                });
                            }
                        },
                        error: function (response) {
                            alert('Failed to load item details');
                            console.log(response);
                        }
                    });
                } else {
                    row.find('.itemDescription').val('');
                    row.find('.unit-price').val('');
                }
            });

            // Validate tax and discount columns for percentage <= 100, highlight errors
            $(document).on('change', '.tax, .discount', function () {
                let $input = $(this);
                let val = parseFloat($input.val()) || 0;
                // Remove previous error
                $input.removeClass('is-invalid');
                $input.next('.invalid-feedback').remove();
                if (val > 100) {
                    $input.addClass('is-invalid');
                    $input.val('');
                    $input.after('<div class="invalid-feedback d-block">Percentage cannot exceed 100%</div>');
                }
            });

            // Handle quantity or price change and calculate line total
            $(document).on('change', '.quantity, .unit-price, .tax, .discount', function () {
                let row = $(this).closest('tr');
                let qty = parseFloat(row.find('.quantity').val()) || 0;
                let price = parseFloat(row.find('.unit-price').val()) || 0;
                let tax = parseFloat(row.find('.tax').val()) || 0;
                let discount = parseFloat(row.find('.discount').val()) || 0;

                // Already validated above, but double-check for safety
                if (tax > 100) tax = 100;
                if (discount > 100) discount = 100;

                let total = qty * price;

                if (tax > 0) {
                    total += total * (tax / 100);
                }
                if (discount > 0) {
                    total -= total * (discount / 100);
                }

                row.find('.line-total').val(total.toFixed(2));
                calculateSummaryTotals();
            });



            function calculateSummaryTotals() {
                let exclusiveTotal = 0;
                let totalTax = 0;

                // Loop through each row to calculate totals
                $('#item-rows tr').each(function () {
                    let row = $(this);
                    let qty = parseFloat(row.find('.quantity').val()) || 0;
                    let price = parseFloat(row.find('.unit-price').val()) || 0;
                    let tax = parseFloat(row.find('.tax').val()) || 0;
                    let discount = parseFloat(row.find('.discount').val()) || 0;

                    // Calculate line total before tax and discount
                    let lineTotalBeforeTax = qty * price;

                    // Apply discount
                    if (discount > 0) {
                        lineTotalBeforeTax -= lineTotalBeforeTax * (discount / 100);
                    }

                    // Calculate tax for this line
                    let lineTax = lineTotalBeforeTax * (tax / 100);

                    // Add to totals
                    exclusiveTotal += lineTotalBeforeTax;
                    totalTax += lineTax;
                });

                // Calculate inclusive total
                let inclusiveTotal = exclusiveTotal + totalTax;

                // Update the summary fields
                $('input[name="exclusiveTotal"]').val(exclusiveTotal.toFixed(2));
                $('input[name="taxAmount"]').val(totalTax.toFixed(2));
                $('input[name="inclusiveTotal"]').val(inclusiveTotal.toFixed(2));
            }


            $(document).ready(function () { calculateSummaryTotals(); });

            function updateLineNumbers() {
                $('#item-rows tr').each(function (index) {
                    $(this).find('.line-no').text((index + 1) + '.');
                });
            }
        });

        let rowCount = 1;

        document.getElementById('add-row').addEventListener('click', function () {
            rowCount++;
            const row = `
        <tr>
            <td class="line-no">${rowCount}.</td>
            <td class="text-start">
                <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item" required>
                    <option disabled selected>Select Item Code</option>
                </select>
            </td>
            <td>
                <textarea class="form-control form-control-sm itemDescription" name="itemDescription[]"
                          id="Description" cols="30"
                          rows="5" readonly style="display: flex; align-items: center; justify-content: center; text-align: center; padding: 0; resize: none;"></textarea>
            </td>
            <td><input type="number" class="form-control form-control-sm qty quantity" name="quantity[]" id="Quantity" required></td>
            <td><input type="number" class="form-control form-control-sm unit-price" name="unitPrice[]" id="Price" required></td>
            <td><input type="number" class="form-control form-control-sm tax" name="tax[]" id="Tax" ></td>
            <td><input type="number" class="form-control form-control-sm discount" name="discount[]" id="Discount" ></td>
            <td><input type="number" class="form-control form-control-sm line-total" name="lineTotal[]"  id="lineTotal" readonly></td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Item"><i class="fa fa-trash"></i> Remove</button>
            </td>
        </tr>`;
            document.getElementById('item-rows').insertAdjacentHTML('beforeend', row);
            updateLineNumbers();
            calculateSummaryTotals();
        });

        // Hide Add Item button for RFQ/Tender modes where items are auto-filled
        function toggleAddItemButton() {
            const mode = $('input[name="SourceType"]:checked').val();
            const $btn = $('#add-row');
            if (mode === 'RFQ' || mode === 'TENDER') {
                $btn.addClass('d-none');
            } else {
                $btn.removeClass('d-none');
            }
        }
        $(document).on('change', 'input[name="SourceType"]', toggleAddItemButton);
        toggleAddItemButton();

        // Remove row handler
        $(document).on('click', '.remove-row', function () {
            // Only remove if more than one row remains
            if ($('#item-rows tr').length > 1) {
                $(this).closest('tr').remove();
                updateLineNumbers();
                calculateSummaryTotals();
            } else {
                // Optionally, clear the row instead of removing if only one left
                let row = $(this).closest('tr');
                row.find('select, input, textarea').val('');
                row.find('.itemDescription').val('');
                row.find('.line-total').val('');
                calculateSummaryTotals();
            }
        });
    </script>
<script>
    $(document).ready(function () {
        $('#rfq-id').on('change', function () {
            const rfqId = $(this).val();
            if (!rfqId) return;

            $.ajax({
                url: `/purchase-order/rfq-items/${rfqId}`,
                method: 'GET',
                success: function (response) {
                    const tbody = $('#item-rows');
                    tbody.empty();

                    response.items.forEach((item, index) => {
                        const row = `
                            <tr>
                                <td>
                                    <input type="hidden" name="itemCode[]" value="${item.itemCode}">
                                    <input type="text" class="form-control" value="${item.itemName}" readonly>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="itemType[]" value="${item.itemType}" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control quantity" name="quantity[]" value="${item.quantity}" min="1" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control unit-price" name="unitPrice[]" value="${item.unitPrice}" min="0" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control tax" name="tax[]" value="" min="0">
                                </td>
                                <td>
                                    <input type="number" class="form-control discount" name="discount[]" value="" min="0">
                                </td>
                                <td>
                                    <input type="number" class="form-control total" name="total[]" value="" readonly>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                                </td>
                            </tr>`;
                        tbody.append(row);
                    });
                },
                error: function () {
                    alert('Failed to load RFQ items.');
                }
            });
        });

        // Remove row handler
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
        });

        // Live calculation of total (qty * unitPrice + tax - discount)
        $(document).on('input', '.quantity, .unit-price, .tax, .discount', function () {
            const row = $(this).closest('tr');
            const qty = parseFloat(row.find('.quantity').val()) || 0;
            const price = parseFloat(row.find('.unit-price').val()) || 0;
            const tax = parseFloat(row.find('.tax').val()) || 0;
            const discount = parseFloat(row.find('.discount').val()) || 0;

            const subtotal = qty * price;
            const total = subtotal + tax - discount;

            row.find('.total').val(total.toFixed(2));
        });
    });
</script>


@endsection

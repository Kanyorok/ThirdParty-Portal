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
                            <input class="form-check-input" type="radio" name="SourceType" id="srcDirect" value="DIRECT" {{ ($sourceType ?? 'RFQ') === 'DIRECT' ? 'checked' : '' }}>
                            <label class="form-check-label" for="srcDirect">Direct</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="SourceType" id="srcRFQ" value="RFQ" {{ ($sourceType ?? 'RFQ') === 'RFQ' ? 'checked' : '' }}>
                            <label class="form-check-label" for="srcRFQ">RFQ</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="SourceType" id="srcTender" value="TENDER" {{ ($sourceType ?? 'RFQ') === 'TENDER' ? 'checked' : '' }}>
                            <label class="form-check-label" for="srcTender">Tender</label>
            </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="SourceType" id="srcContract" value="CONTRACT" {{ ($sourceType ?? 'RFQ') === 'CONTRACT' ? 'checked' : '' }}>
                            <label class="form-check-label" for="srcContract">Contract-based</label>
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
                            <option value="{{ $ar->RFQNumber }}"
                                    data-rfq-id="{{ $ar->Id }}"
                                    data-supplier-id="{{ $ar->ThirdPartyId ?? $ar->SupplierId }}"
                                    data-thirdparty-id="{{ $ar->ThirdPartyId ?? 0 }}"
                                    data-supplier-legacy-id="{{ $ar->SupplierId }}"
                                    data-supplier-name="{{ $ar->SupplierName ?? '' }}"
                                    data-address="{{ $ar->Address ?? '' }}"
                                    {{ $disabled }}>{{ $ar->RFQNumber }}</option>
                            @endforeach
                        {{-- Only awarded RFQs must be listed (no non-awarded options) --}}
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
                            <option value="{{ $t->TenderNo }}"
                                    data-tender-id="{{ $t->Id }}"
                                    data-supplier-id="{{ $t->SupplierId }}"
                                    data-thirdparty-id="{{ $t->ThirdPartyId ?? 0 }}"
                                    data-supplier-name="{{ $t->SupplierName ?? '' }}"
                                    data-address="{{ $t->Address ?? '' }}"
                                    {{ $disabled }}>{{ $t->TenderNo }}</option>
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

            <!-- Contract Selection -->
            <div class="row mb-4 source-contract d-none">
                    <div class="col-md-4">
                    <label>Contract Ref <span class="text-danger">*</span></label>
                    <select class="form-control" id="contractRef">
                        <option selected disabled>Select Contract</option>
                        @foreach(($contracts ?? []) as $c)
                            <option value="{{ $c->ContractRef }}" data-contract-id="{{ $c->Id }}" data-supplier-id="{{ $c->SupplierId }}" data-address="{{ $c->Address }}">{{ $c->ContractRef }} ({{ $c->SupplierName }})</option>
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
            <div class="row mb-4 supplier-row">
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

        // Utility: rebuild line items from payload
        function populateItems(items) {
            const $tbody = $('#item-rows');
            $tbody.empty();
            const addRow = (idx, it) => {
                const $tr = $('<tr/>');
                $tr.append(`<td class="line-no">${idx + 1}.</td>`);
                const $itemTd = $('<td class="text-start"/>');
                const $select = $('<select class="form-select form-select-sm itemCode" name="itemCode[]" required/>');
                const optionText = it.itemName || `Item #${it.itemCode || ''}`;
                const optionVal = Number.isFinite(it.itemCode) ? it.itemCode : 0;
                $select.append(`<option value="${optionVal}" selected>${optionText}</option>`);
                $itemTd.append($select);
                $tr.append($itemTd);
                $tr.append(`<td class="text-start"><textarea class="form-control form-control-sm itemDescription" name="itemDescription[]" rows="5" readonly style="display:flex;align-items:center;justify-content:center;text-align:center;padding:0;resize:none;">${optionText}</textarea></td>`);
                $tr.append(`<td class="text-start"><input type="number" class="form-control form-control-sm qty quantity" name="quantity[]" step="any" required value="${it.quantity ?? ''}"></td>`);
                $tr.append(`<td class="text-start"><input type="number" class="form-control form-control-sm unit-price" name="unitPrice[]" step="any" required value="${it.unitPrice ?? ''}"></td>`);
                $tr.append('<td class="text-start"><input type="number" class="form-control form-control-sm tax" name="tax[]" step="any"></td>');
                $tr.append('<td class="text-start"><input type="number" class="form-control form-control-sm discount" name="discount[]" step="any"></td>');
                const lineTotal = (+it.quantity || 0) * (+it.unitPrice || 0);
                $tr.append(`<td class="text-start"><input type="number" class="form-control form-control-sm line-total" name="lineTotal[]" step="any" readonly value="${lineTotal.toFixed(2)}"></td>`);
                $tr.append('<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Item"><i class="fa fa-trash"></i> Remove</button></td>');
                $tbody.append($tr);
            };
            (items || []).forEach((it, idx) => addRow(idx, it));
            if ((items || []).length === 0) {
                // keep one blank row
                addRow(0, { itemCode: 0, itemName: '', quantity: '', unitPrice: '' });
            }
            updateTotals();
        }

        function parseNumber(val) {
            const n = parseFloat(val);
            return Number.isFinite(n) ? n : 0;
        }

        function recalcRow($tr) {
            const qty = parseNumber($tr.find('.quantity').val());
            const price = parseNumber($tr.find('.unit-price').val());
            const discountPct = parseNumber($tr.find('.discount').val());
            const base = qty * price;
            const discounted = base * (discountPct ? (1 - (discountPct / 100)) : 1);
            $tr.find('.line-total').val(discounted.toFixed(2));
        }

        function updateTotals() {
            let exclusive = 0;
            let taxTotal = 0;
            $('#item-rows tr').each(function () {
                const $tr = $(this);
                const lt = parseNumber($tr.find('.line-total').val());
                const taxPct = parseNumber($tr.find('.tax').val());
                exclusive += lt;
                taxTotal += lt * (taxPct / 100);
            });
            const inclusive = exclusive + taxTotal;
            $('.exclusiveTotal').val(exclusive.toFixed(2));
            $('.taxAmount').val(taxTotal.toFixed(2));
            $('.inclusiveTotal').val(inclusive.toFixed(2));
        }

        $(document).on('input change', '.quantity, .unit-price, .tax, .discount', function () {
            const $tr = $(this).closest('tr');
            recalcRow($tr);
            updateTotals();
        });

        $(document).on('keyup blur', '.quantity, .unit-price, .tax, .discount', function () {
            const $input = $(this);
            if ($input.val() === '') {
                $input.val('0');
            }
            const $tr = $input.closest('tr');
            recalcRow($tr);
            updateTotals();
        });

        $(function(){
            updateTotals();
        });

        // Source mode toggling
        function applySourceMode() {
            const mode = $('input[name="SourceType"]:checked').val();
            if (mode === 'RFQ') {
                $('.source-rfq').removeClass('d-none');
                $('.source-tender').addClass('d-none');
                $('.source-contract').addClass('d-none');
                $('#supplier').prop('disabled', true); // auto in RFQ
                $('.direct-only').addClass('d-none');
                // Live refresh of awarded RFQs from t_RFQAward
                const $ref = $('#refNo');
                $ref.empty().append('<option selected disabled>Loading awarded RFQs...</option>');
                fetch('/procurement/purchase-order/awarded-rfqs')
                    .then(r => r.json())
                    .then(({success, data}) => {
                        $ref.empty().append('<option selected disabled>Select RFQ</option>');
                        if (!success) return;
                        const converted = (window.convertedRFQIds || []);
                        (data || []).forEach(ar => {
                            const dis = converted.includes(ar.Id) ? 'disabled' : '';
                            const thirdPartyId = ar.ThirdPartyId || 0;
                            const supplierLegacyId = ar.SupplierId || '';
                            const supplierName = ar.SupplierName || '';
                            const address = ar.Address || '';
                            $ref.append(`<option value="${ar.RFQNumber}"
                                            data-rfq-id="${ar.Id}"
                                            data-supplier-legacy-id="${supplierLegacyId}"
                                            data-thirdparty-id="${thirdPartyId}"
                                            data-supplier-name="${supplierName}"
                                            data-address="${address}"
                                            ${dis}>${ar.RFQNumber}</option>`);
                        });
                    })
                    .catch(() => {});
            } else if (mode === 'TENDER') {
                $('.source-rfq').addClass('d-none');
                $('.source-tender').removeClass('d-none');
                $('.source-contract').addClass('d-none');
                $('#supplier').prop('disabled', true);
                $('.direct-only').addClass('d-none');
            } else if (mode === 'CONTRACT') {
                $('.source-rfq').addClass('d-none');
                $('.source-tender').addClass('d-none');
                $('.source-contract').removeClass('d-none');
                $('#supplier').prop('disabled', true);
                $('.direct-only').addClass('d-none');
            } else {
                $('.source-rfq').addClass('d-none');
                $('.source-tender').addClass('d-none');
                $('.source-contract').addClass('d-none');
                $('#supplier').prop('disabled', false);
                $('#SourceId').val('');
                $('.direct-only').removeClass('d-none');
                populateItems([]);
            }
        }
        $(document).on('change', 'input[name="SourceType"]', applySourceMode);
        applySourceMode();

        // RFQ selection: auto-fill supplier and items
        $(document).on('change', '#refNo', function () {
            const selectedRFQNo = $(this).val();
            const rfqOption = $(this).find('option:selected');
            const rfqId = parseInt(rfqOption.data('rfq-id'));
            const supplierLegacyId = parseInt(rfqOption.data('supplier-legacy-id')); // t_Suppliers.Id for posting
            const awardedThirdPartyId = parseInt(rfqOption.data('thirdparty-id')); // t_ThirdParties.Id for lookups/items
            const matchThirdPartyId = Number.isFinite(awardedThirdPartyId) ? awardedThirdPartyId : NaN;
            if (!isNaN(rfqId)) {
                $('#SourceId').val(rfqId);
                        } else {
                $('#SourceId').val('');
            }

            const $supplier = $('#supplier');
            $supplier.empty().append('<option selected disabled>Select supplier</option>');

            if (Number.isFinite(supplierLegacyId)) {
                const awardResp = rfqResponses.find(r => (r.RFQNumber === selectedRFQNo) && (parseInt(r.SupplierId) === matchThirdPartyId));
                const fallbackName = rfqOption.data('supplier-name') || '';
                const fallbackAddress = rfqOption.data('address') || '';
                const displayName = awardResp ? (awardResp.TradingName || awardResp.SupplierName || awardResp.Name) : (fallbackName || `Supplier #${supplierLegacyId}`);
                const address = awardResp ? (awardResp.Address || awardResp.TradingAddress || '') : fallbackAddress;
                $supplier.append(`<option value="${supplierLegacyId}" selected data-address="${address}">${displayName}</option>`);
                $supplier.prop('disabled', true);
                $('input[name="address"]').val(address);
                if ($("input[name='supplier']").length === 0) {
                    $('<input>').attr({type:'hidden', name:'supplier', value:String(supplierLegacyId)}).appendTo('#purchaseOrdersForm');
            } else {
                    $("input[name='supplier']").val(String(supplierLegacyId));
                }

                // Fetch RFQ items for this supplier (use ThirdPartyId as rr.SupplierId in t_RFQResponse)
                if (!isNaN(rfqId) && Number.isFinite(matchThirdPartyId)) {
                    fetch(`/procurement/purchase-order/rfq-items/${rfqId}?supplierId=${matchThirdPartyId}`)
                .then(r => r.json())
                        .then(({items}) => { populateItems(items || []); updateTotals(); })
                        .catch(() => populateItems([]));
                } else if (!isNaN(rfqId)) {
                    // fallback without supplier filter
                    fetch(`/procurement/purchase-order/rfq-items/${rfqId}`)
                        .then(r => r.json())
                        .then(({items}) => { populateItems(items || []); updateTotals(); })
                        .catch(() => populateItems([]));
                }
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
                // Populate items for first found supplier (optional)
                if (!isNaN(rfqId) && suppliers.length > 0) {
                    const thirdParty = parseInt(suppliers[0].SupplierId);
                    if (Number.isFinite(thirdParty)) {
                        fetch(`/procurement/purchase-order/rfq-items/${rfqId}?supplierId=${thirdParty}`)
                            .then(r => r.json())
                            .then(({items}) => { populateItems(items || []); updateTotals(); })
                            .catch(() => populateItems([]));
                    }
                }
            }
        });

        // Tender selection: auto-fill supplier and items
        $(document).on('change', '#tenderNo', function () {
            const opt = $(this).find('option:selected');
            const tenderId = parseInt(opt.data('tender-id'));
            const supplierId = parseInt(opt.data('supplier-id')); // t_Suppliers.Id for posting
            const supplierName = opt.data('supplier-name') || '';
            const address = opt.data('address') || '';

            if (!isNaN(tenderId)) {
                $('#SourceId').val(tenderId);
            } else {
                $('#SourceId').val('');
            }

            const $supplier = $('#supplier');
            $supplier.empty().append('<option selected disabled>Select supplier</option>');
            if (!isNaN(supplierId)) {
                const name = supplierName || `Supplier #${supplierId}`;
                $supplier.append(`<option value="${supplierId}" selected data-address="${address}">${name}</option>`);
                        $supplier.prop('disabled', true);
                $('input[name="address"]').val(address);
                if ($("input[name='supplier']").length === 0) {
                        $('<input>').attr({type:'hidden', name:'supplier', value:String(supplierId)}).appendTo('#purchaseOrdersForm');
                } else {
                    $("input[name='supplier']").val(String(supplierId));
                }
            }

            if (!isNaN(tenderId)) {
                fetch(`/procurement/purchase-order/tender-items/${tenderId}`)
                    .then(r => r.json())
                    .then(({items}) => { populateItems(items || []); updateTotals(); })
                    .catch(() => populateItems([]));
            }
        });

        // Contract selection: auto-fill supplier and (optionally) items
        $(document).on('change', '#contractRef', function () {
            const opt = $(this).find('option:selected');
            const contractId = parseInt(opt.data('contract-id'));
            const supplierId = parseInt(opt.data('supplier-id'));
            const address = opt.data('address') || '';
            if (!isNaN(contractId)) {
                $('#SourceId').val(contractId);
            } else {
                $('#SourceId').val('');
            }

            const $supplier = $('#supplier');
            $supplier.empty().append('<option selected disabled>Select supplier</option>');
            if (!isNaN(supplierId)) {
                $supplier.append(`<option value="${supplierId}" selected data-address="${address}">Supplier #${supplierId}</option>`);
                $supplier.prop('disabled', true);
                $('input[name="address"]').val(address);
                if ($("input[name='supplier']").length === 0) {
                    $('<input>').attr({type:'hidden', name:'supplier', value:String(supplierId)}).appendTo('#purchaseOrdersForm');
            } else {
                    $("input[name='supplier']").val(String(supplierId));
                }
            }

            // Optional: fetch mapped contract items here if needed (kept minimal to match dev UX)
            // fetch(`/procurement/purchaseOrder/contract-details/${contractId}`)
            //     .then(r => r.json())
            //     .then(({items}) => { populateItems(items || []); updateTotals(); })
            //     .catch(console.error);
        });

        // Autopopulate address when supplier is selected
        $(document).on('change', '#supplier', function () {
            let address = $(this).find('option:selected').data('address') || '';
            $('input[name="address"]').val(address);
        });

        $(function () {
            $('form#purchaseOrdersForm').submit(async function (e) {
                e.preventDefault();
                const supVal = $('#supplier').val();
                if (supVal && $("input[name='supplier']").length === 0) {
                    $('<input>').attr({type:'hidden', name:'supplier', value:String(supVal)}).appendTo('#purchaseOrdersForm');
                }
                if (await saveForm($(this), $('#saveOrder'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
        });

        // === Direct mode wiring ===
        function setDirectMode(enabled){
            const $direct = $('.direct-only');
            const $supplierRow = $('.supplier-row');
            if(enabled){
                $direct.removeClass('d-none');
                // hide the main supplier field per requirement
                $supplierRow.addClass('d-none');
                // load root categories and merge with direct-plan categories
                const $sel = $('#itemCategory');
                $sel.empty().append(`<option value="">-- None --</option>`);
                Promise.all([
                    fetch(`{{ url('procurement/purchaseOrder/root-categories') }}`).then(r=>r.json()).catch(()=>({data:[]})),
                    fetch(`{{ url('procurement/purchaseOrder/direct-plan-categories') }}`).then(r=>r.json()).catch(()=>({data:[]})),
                ]).then(([root, direct])=>{
                    const seen = new Set();
                    [...(root.data||[]), ...(direct.data||[])].forEach(row=>{
                        if(!row || !row.Id || seen.has(row.Id)) return;
                        seen.add(row.Id);
                        $sel.append(`<option value="${row.Id}">${row.Name}</option>`);
                    });
                });
            }else{
                $direct.addClass('d-none');
                $supplierRow.removeClass('d-none');
            }
        }

        function refreshSourceUI(){
            const src = $('input[name="SourceType"]:checked').val();
            setDirectMode(src === 'DIRECT');
        }
        $(document).on('change','input[name="SourceType"]', refreshSourceUI);
        // initial
        refreshSourceUI();

        // Category change => load prequalified suppliers and items
        $('#itemCategory').on('change', function(){
            const catId = $(this).val() ? parseInt($(this).val(),10) : 0;
            const $preq = $('#preqSupplierHelper');
            $preq.empty().append('<option value="">-- None --</option>');
            if(catId>0){
                fetch(`{{ url('procurement/purchaseOrder/prequalified-suppliers') }}/${catId}`)
                    .then(r=>r.json()).then(({data})=>{
                        (data||[]).forEach(row=>{
                            $preq.append(`<option value="${row.ThirdPartyId || ''}" data-address="${row.Address||''}">${row.SupplierName||('Supplier #'+(row.SupplierId||''))}</option>`)
                        });
                    }).catch(()=>{});
                fetch(`{{ url('procurement/purchaseOrder/items-by-category') }}/${catId}`)
                    .then(r=>r.json()).then(({items})=>{
                        const options = (items||[]).map(it=>`<option value="${it.itemCode}">${it.itemName}</option>`).join('');
                        const $first = $('#item-rows tr').first();
                        $first.find('select.itemCode').empty().append(`<option disabled selected>Select Item Code</option>`).append(options);
                    }).then(()=>{
                        // Merge in direct plan items
                        return fetch(`{{ url('procurement/purchaseOrder/direct-plan-items') }}/${catId}`)
                            .then(r=>r.json()).then(({items})=>{
                                const $first = $('#item-rows tr').first();
                                const $sel = $first.find('select.itemCode');
                                const existingVals = new Set($sel.find('option').map(function(){return this.value;}).get());
                                (items||[]).forEach(it=>{
                                    const val = String(it.itemCode||'');
                                    if(val && !existingVals.has(val)){
                                        $sel.append(`<option value="${val}">${it.itemName}</option>`);
                                    }
                                });
                            });
                    }).catch(()=>{});
            }
        });

        // Mirror helper address to address field
        $('#preqSupplierHelper').on('change', function(){
            const addr = $(this).find('option:selected').data('address') || '';
            $('input[name="address"]').val(addr);
        });

        // Remove row handler and totals calculation kept from your current dev form
        </script>
@endsection

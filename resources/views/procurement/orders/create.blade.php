@extends('layouts.app')
@section('title', 'Purchase Order')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }

        /* Alignment helpers */
        .table-form th, .table-form td { vertical-align: middle; }
        .number-input { text-align: right; }
        .textarea-compact { height: 64px; resize: vertical; }
        .form-label { margin-bottom: .25rem; }
        .w-min-80 { min-width: 80px; }
        .w-min-100 { min-width: 100px; }
        .w-min-150 { min-width: 150px; }
        .w-min-200 { min-width: 200px; }

    </style>
@endsection
@section('content')
{{-- <div class="mb-3"> --}}
{{-- <h1 class="h3 d-inline align-middle">@yield('title')</h1> --}}
{{-- </div> --}}

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
                    @php
                    $rfqNumber = trim((string)($ar->RFQNumber ?? ''));
                    $isConvertedId = in_array($ar->Id, $convertedRFQIds ?? []);
                    $isUsedRef = in_array($rfqNumber, $usedReferenceNumbers ?? []);
                    @endphp
                    {{-- Skip RFQs that are already converted by id or by reference number in ExtOrdNum --}}
                    @if($isConvertedId || $isUsedRef)
                    @continue
                    @endif
                    <option value="{{ $ar->RFQNumber }}"
                        data-rfq-id="{{ $ar->Id }}"
                        data-supplier-id="{{ $ar->ThirdPartyId ?? $ar->SupplierId }}"
                        data-thirdparty-id="{{ $ar->ThirdPartyId ?? 0 }}"
                        data-supplier-legacy-id="{{ $ar->SupplierId }}"
                        data-supplier-name="{{ $ar->SupplierName ?? '' }}"
                        data-address="{{ $ar->Address ?? '' }}">{{ $ar->RFQNumber }}</option>
                    @endforeach
                    {{-- Only awarded RFQs must be listed (no non-awarded options) --}}
                </select>
                @error('refNo')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label>LPO Number <span class="text-danger">*</span></label>
                <input type="text" name="LPONo" class="form-control @error('LPONo') is-invalid @enderror" value="{{ old('LPONo', uniqid('LPO-')) }}" readonly required />
                @error('LPONo')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label>Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control poDate @error('pODate') is-invalid @enderror" name="pODate" value="{{ old('pODate', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required />
                @error('pODate')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

            <!-- Tender Selection -->
            <div class="row mb-4 source-tender d-none">
                <div class="col-md-6">
                    <label>Tender No <span class="text-danger">*</span></label>
                    <select class="form-control" id="tenderNo">
                        <option selected disabled>Select Tender</option>
                        @foreach(($awardedTenders ?? []) as $t)
                            @php
                                $tenderNo = trim((string)($t->TenderNo ?? ''));
                                $isConvertedTenderId = in_array($t->Id, ($convertedTenderIds ?? []));
                                $isUsedTenderRef = in_array($tenderNo, $usedReferenceNumbers ?? []);
                            @endphp
                            {{-- Skip Tenders already converted by id or whose TenderNo appears in ExtOrdNum --}}
                            @if($isConvertedTenderId || $isUsedTenderRef)
                                @continue
                            @endif
                            <option value="{{ $t->TenderNo }}"
                                    data-tender-id="{{ $t->Id }}"
                                    data-supplier-id="{{ $t->SupplierId }}"
                                    data-thirdparty-id="{{ $t->ThirdPartyId ?? 0 }}"
                                    data-supplier-name="{{ $t->SupplierName ?? '' }}"
                                    data-address="{{ $t->Address ?? '' }}">{{ $t->TenderNo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>LPO Number <span class="text-danger">*</span></label>
                    <input type="text" name="LPONo" class="form-control" value="{{ old('LPONo', uniqid('LPO-')) }}" readonly required/>
                </div>
                <!-- Date input removed for Tender-based flow -->
                <input type="hidden" name="pODate" value="{{ old('pODate', now()->format('Y-m-d')) }}" />
            </div>
            <div class="col-md-4">
                <label>Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control poDate" name="pODate" value="{{ old('pODate', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required />
            </div>
        </div>

            <!-- Contract Selection -->
                <div class="row mb-4 source-contract d-none">
                    <div class="col-md-6">
                    <label>Contract Ref <span class="text-danger">*</span></label>
                    <select class="form-control" id="contractRef">
                        <option selected disabled>Select Active Contract</option>
                        @foreach(($contracts ?? []) as $c)
                            <option value="{{ $c->ContractRef }}"
                                    data-contract-id="{{ $c->Id }}"
                                    data-supplier-id="{{ $c->SupplierId }}"
                                    data-supplier-name="{{ $c->SupplierName ?? '' }}"
                                    data-address="{{ $c->Address }}">
                                {{ $c->ContractRef }} - {{ $c->SupplierName }} [{{ $c->ContractStatus }}]
                            </option>
                        @endforeach
                        </select>
                </div>
                <div class="col-md-6">
                    <label>LPO Number <span class="text-danger">*</span></label>
                    <input type="text" name="LPONo" class="form-control" value="{{ old('LPONo', uniqid('LPO-')) }}" readonly required/>
                </div>
                <!-- Date input removed for Contract-based flow -->
                <input type="hidden" name="pODate" value="{{ old('pODate', now()->format('Y-m-d')) }}" />
            </div>
            <div class="col-md-4">
                <label>Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control poDate" name="pODate" value="{{ old('pODate', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required />
            </div>
        </div>

        <!-- Supplier & Details -->
        <div class="row mb-4 supplier-row">
            <div class="col-md-6">
                <!-- Host for supplier driver (non-direct) and plan driver (direct) -->
                <div id="supplierDriverHost"></div>

                <!-- Direct Mode: Plan Selection -->
                <div id="planDriver" class="plan-driver d-none">
                    <label>Approved Procurement Plan (Direct) <span class="text-danger">*</span></label>
                    <select id="directPlanSelect" class="form-control">
                        <option value="" selected>Select Approved Plan</option>
                    </select>
                    <small class="text-muted">These are approved plans whose method is Direct Purchase.</small>
                </div>

                <!-- Other Modes: Read-only Supplier Name -->
                <div id="supplierDisplayField">
                    <label>Supplier Name</label>
                    <input type="text" class="form-control" id="visibleSupplierName" readonly placeholder="Supplier will be auto-filled">
                </div>
            </div>
            <div class="col-md-6">
                <label>Address</label>
                <input type="text" class="form-control" name="address" placeholder="Supplier address" readonly />
            </div>
        </div>

        <!-- Visible supplier field removed; we'll use prequalified supplier (Direct) or hidden supplier input (other modes). -->

        <!-- Direct mode helpers (will receive supplier select when Direct) -->
        <div class="row mb-3 direct-only d-none">
            <div class="col-md-6">
                <label>Item Category <span class="text-danger">*</span></label>
                <select id="itemCategory" class="form-control" required>
                    <option value="" selected>-- None --</option>
                </select>
            </div>
            <div class="col-md-6" id="directSupplierHost">
                <label>Prequalified Supplier <span class="text-danger">*</span></label>
                <select id="preqSupplier" class="form-control" required>
                    <option value="" selected>-- None --</option>
                </select>
                <small class="text-muted">Only suppliers prequalified for the selected category will appear.</small>
                <!-- Supplier select will be appended here in Direct mode -->
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
                <table class="table table-bordered table-form" id="line-items-table">
                    <thead class="table-light">
                    <tr>
                        <th class="w-min-80" style="width: 3%;">#</th>
                        <th class="w-min-150" style="width: 15%;">Item Code <span class="text-danger">*</span></th>
                        <th class="w-min-200" style="width: 20%;">Item Name</th>
                        <th class="w-min-80 text-end" style="width: 5%;">Quantity <span class="text-danger">*</span></th>
                        <th class="w-min-100 text-end" style="width: 10%;">Unit Price <span class="text-danger">*</span></th>
                        <th class="w-min-80 text-end" style="width: 5%;">Tax %</th>
                        <th class="w-min-80 text-end" style="width: 5%;">Discount %</th>
                        <th class="w-min-150 text-end" style="width: 15%;">Line Total</th>
                    </tr>
                </thead>
                <tbody id="item-rows">
                    <tr>
                        <td class="line-no">1.</td>
                        <td class="text-start">
                            <input type="text" class="form-control form-control-sm itemCode" name="itemCode[]" placeholder="Item (code/name)" required>
                        </td>
                        <td class="text-start">
                            <textarea class="form-control form-control-sm itemDescription textarea-compact" name="itemDescription[]" id="Description" rows="3" readonly></textarea>
                        </td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm qty quantity number-input" name="quantity[]" id="Quantity" step="any" required></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm unit-price number-input" name="unitPrice[]" id="Price" step="any" required></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm tax number-input" name="tax[]" id="Tax" step="any"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm discount number-input" name="discount[]" id="Discount" step="any"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm line-total number-input" name="lineTotal[]" id="lineTotal" step="any" readonly></td>
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
                    <input type="text" class="form-control exclusiveTotal" name="exclusiveTotal" readonly />
                </div>
                <div class="mb-2">
                    <label>Tax Amount</label>
                    <input type="text" class="form-control taxAmount" name="taxAmount" readonly />
                </div>
                <div>
                    <label>Inclusive Total</label>
                    <input type="text" class="form-control inclusiveTotal" name="inclusiveTotal" readonly />
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary me-2" id="saveOrder">Save</button>
            {{-- <button class="btn btn-warning me-2">Edit</button> --}}
            {{-- <button class="btn btn-danger">Delete</button> --}}
        </div>
    </form>
</div>

@endsection
@section('scripts')

<script>
    // Prepare RFQ responses for JS (for supplier filtering)
    const rfqResponses = @json($rfqResponses);
    const convertedRFQIds = @json($convertedRFQIds);

    // Load all items for dropdowns
    const allItems = @json($allItems);

    // Build item options HTML
    let itemOptions = '<option value="" disabled selected>Select Item</option>';
    let currentAvailableItems = allItems; // Keep reference to current available items

    function buildItemOptions(items) {
        let options = '<option value="" disabled selected>Select Item</option>';
        items.forEach(item => {
            options += `<option value="${item.itemCode}"
                                   data-name="${item.itemName}"
                                   data-description="${item.description || item.itemName}"
                                   data-price="${item.unitPrice || 0}"
                                   data-type="${item.itemType || ''}"
                                   data-category="${item.categoryName || ''}">
                               ${item.itemName}
                           </option>`;
        });
        return options;
    }

    // Initialize with all items
    itemOptions = buildItemOptions(allItems);

    // Function to update item options for contract-specific tender items
    function updateItemOptionsForContract(contractItems) {
            currentAvailableItems = contractItems;
            itemOptions = buildItemOptions(contractItems);

            // Update existing dropdowns with new options
            $('.itemCode').each(function() {
                if (this.tagName !== 'SELECT') return; // only update selects
                const currentVal = $(this).val();
                $(this).html(itemOptions);

                // Try to restore the previously selected value if it still exists
                if (currentVal && contractItems.find(item => item.itemCode == currentVal)) {
                    $(this).val(currentVal);
                }
            });
        }
    </script>

    <script>

        // Utility: rebuild line items from payload
        function populateItems(items) {
            const $tbody = $('#item-rows');
            $tbody.empty();
            const addRow = (idx, it) => {
                const $tr = $('<tr/>');
                $tr.append(`<td class="line-no">${idx + 1}.</td>`);

                // Create item dropdown with currently available items (contract-specific or all items)
                const $itemTd = $('<td class="text-start"/>' );
                const $inputItem = $('<input type="text" class="form-control form-control-sm itemCode" name="itemCode[]" placeholder="Item (code/name)" required/>' );
                if (it.itemCode) { $inputItem.val(it.itemCode); }
                $itemTd.append($inputItem);
                $tr.append($itemTd);

                // Item description
                const itemDescription = it.description || it.itemName || '';
                $tr.append(`<td class="text-start"><textarea class="form-control form-control-sm itemDescription textarea-compact" name="itemDescription[]" rows="3" readonly>${itemDescription}</textarea></td>`);

                // Other fields
                $tr.append(`<td class="text-start"><input type="number" class="form-control form-control-sm qty quantity number-input" name="quantity[]" step="any" required value="${it.quantity ?? ''}"></td>`);
                $tr.append(`<td class="text-start"><input type="number" class="form-control form-control-sm unit-price number-input" name="unitPrice[]" step="any" required value="${it.unitPrice ?? ''}"></td>`);
                $tr.append('<td class="text-start"><input type="number" class="form-control form-control-sm tax number-input" name="tax[]" step="any"></td>');
                $tr.append('<td class="text-start"><input type="number" class="form-control form-control-sm discount number-input" name="discount[]" step="any"></td>');

                const lineTotal = (+it.quantity || 0) * (+it.unitPrice || 0);
                $tr.append(`<td class="text-start"><input type="number" class="form-control form-control-sm line-total number-input" name="lineTotal[]" step="any" readonly value="${lineTotal.toFixed(2)}"></td>`);
                $tr.append('<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Item"><i class="fa fa-trash"></i> Remove</button></td>');
                $tbody.append($tr);
            };

            (items || []).forEach((it, idx) => addRow(idx, it));
            if ((items || []).length === 0) {
                // keep one blank row with current available items
                addRow(0, { itemCode: '', itemName: '', quantity: '', unitPrice: '' });
            }
        });
    }
</script>

<script>
    // Utility: rebuild line items from payload
    function populateItems(items) {
        const $tbody = $('#item-rows');
        $tbody.empty();
        const addRow = (idx, it) => {
            const $tr = $('<tr/>');
            $tr.append(`<td class="line-no">${idx + 1}.</td>`);

            // Create item dropdown with currently available items (contract-specific or all items)
            const $itemTd = $('<td class="text-start"/>');
            const $inputItem = $('<input type="text" class="form-control form-control-sm itemCode" name="itemCode[]" placeholder="Item (code/name)" required/>');
            if (it.itemCode) {
                $inputItem.val(it.itemCode);
            }
            $itemTd.append($inputItem);
            $tr.append($itemTd);

            // Item description
            const itemDescription = it.description || it.itemName || '';
            $tr.append(`<td class="text-start"><textarea class="form-control form-control-sm itemDescription" name="itemDescription[]" rows="5" readonly style="display:flex;align-items:center;justify-content:center;text-align:center;padding:0;resize:none;">${itemDescription}</textarea></td>`);

            // Other fields
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
            // keep one blank row with current available items
            addRow(0, {
                itemCode: '',
                itemName: '',
                quantity: '',
                unitPrice: ''
            });
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
        $('#item-rows tr').each(function() {
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

    $(document).on('input change', '.quantity, .unit-price, .tax, .discount', function() {
        const $tr = $(this).closest('tr');
        recalcRow($tr);
        updateTotals();
    });

    $(document).on('keyup blur', '.quantity, .unit-price, .tax, .discount', function() {
        const $input = $(this);
        if ($input.val() === '') {
            $input.val('0');
        }
        const $tr = $input.closest('tr');
        recalcRow($tr);
        updateTotals();
    });

    $(function() {
        updateTotals();
    });

    // Source mode toggling
    function applySourceMode() {
        const mode = $('input[name="SourceType"]:checked').val();
        const $planDriver = $('#planDriver');
        const $supplierField = $('#supplierDisplayField');

        // Clear supplier name when switching modes
        $('#visibleSupplierName').val('');

        if (mode === 'RFQ') {
            $('.source-rfq').removeClass('d-none');
            $('.source-tender').addClass('d-none');
            $('.source-contract').addClass('d-none');
            $('.direct-only').addClass('d-none');
            // Show supplier driver
            $planDriver.addClass('d-none');
            $supplierField.removeClass('d-none');
            // Live refresh of awarded RFQs from t_RFQAward
            const $ref = $('#refNo');
            $ref.empty().append('<option selected disabled>Loading awarded RFQs...</option>');
            fetch('/procurement/purchase-order/awarded-rfqs')
                .then(r => r.json())
                .then(({
                    success,
                    data
                }) => {
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
            // Reset to all items for RFQ mode
            updateItemOptionsForContract(allItems);
        } else if (mode === 'TENDER') {
            $('.source-rfq').addClass('d-none');
            $('.source-tender').removeClass('d-none');
            $('.source-contract').addClass('d-none');
            $('.direct-only').addClass('d-none');
            $planDriver.addClass('d-none');
            $supplierField.removeClass('d-none');
            // Reset to all items for Tender mode
            updateItemOptionsForContract(allItems);
        } else if (mode === 'CONTRACT') {
            $('.source-rfq').addClass('d-none');
            $('.source-tender').addClass('d-none');
            $('.source-contract').removeClass('d-none');
            $('.direct-only').addClass('d-none');
            $planDriver.addClass('d-none');
            $supplierField.removeClass('d-none');
            // Keep current available items (will be updated when contract is selected)
        } else {
            $('.source-rfq').addClass('d-none');
            $('.source-tender').addClass('d-none');
            $('.source-contract').addClass('d-none');
            $('#SourceId').val('');
            // Hide Item Category row until a plan is selected
            $('.direct-only').addClass('d-none');
            // Reset to all items for Direct mode
            updateItemOptionsForContract(allItems);
            populateItems([]);
            // Show plan driver
            $planDriver.removeClass('d-none');
            $supplierField.addClass('d-none');
            // Load approved direct procurement plans
            const $planSel = $('#directPlanSelect');
            $planSel.prop('disabled', true).html('<option value="" selected>Loading plans...</option>');
            fetch('/procurement/purchase-order/direct-plans')
                .then(r => r.json())
                .then(({
                    success,
                    data
                }) => {
                    $planSel.empty().append('<option value="" selected>Select Approved Plan</option>');
                    if (!success) {
                        $planSel.append('<option disabled>Error loading plans</option>');
                        return;
                    }
                    (data || []).forEach(p => {
                        const label = `${p.Title || ('Plan #' + (p.PlanID||''))}${p.FiscalYear ? ' - ' + p.FiscalYear : ''} (${p.PendingItems ?? 0} pending)`;
                        $planSel.append(`<option value="${p.PlanID}">${label}</option>`);
                    });
                    $planSel.prop('disabled', false);
                })
                .catch(() => {
                    $planSel.html('<option value="" selected>Failed to load plans</option>');
                    $planSel.prop('disabled', false);
                });
        }
    }
    $(document).on('change', 'input[name="SourceType"]', applySourceMode);
    applySourceMode();

    // RFQ selection: auto-fill hidden supplier and items
    $(document).on('change', '#refNo', function() {
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

        if (Number.isFinite(supplierLegacyId)) {
            const awardResp = rfqResponses.find(r => (r.RFQNumber === selectedRFQNo) && (parseInt(r.SupplierId) === matchThirdPartyId));
            const fallbackName = rfqOption.data('supplier-name') || '';
            const fallbackAddress = rfqOption.data('address') || '';
            const displayName = awardResp ? (awardResp.TradingName || awardResp.SupplierName || awardResp.Name) : (fallbackName || `Supplier #${supplierLegacyId}`);
            const address = awardResp ? (awardResp.Address || awardResp.TradingAddress || '') : fallbackAddress;
            $('input[name="address"]').val(address);
            $('#visibleSupplierName').val(displayName);
            if ($("input[name='supplier']").length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'supplier',
                    value: String(supplierLegacyId)
                }).appendTo('#purchaseOrdersForm');
            } else {
                $("input[name='supplier']").val(String(supplierLegacyId));
            }

            // Fetch RFQ items for this supplier (use ThirdPartyId as rr.SupplierId in t_RFQResponse)
            if (!isNaN(rfqId) && Number.isFinite(matchThirdPartyId)) {
                fetch(`/procurement/purchase-order/rfq-items/${rfqId}?supplierId=${matchThirdPartyId}`)
                    .then(r => r.json())
                    .then(({
                        items
                    }) => {
                        populateItems(items || []);
                        updateTotals();
                    })
                    .catch(() => populateItems([]));
            } else if (!isNaN(rfqId)) {
                // fallback without supplier filter
                fetch(`/procurement/purchase-order/rfq-items/${rfqId}`)
                    .then(r => r.json())
                    .then(({
                        items
                    }) => {
                        populateItems(items || []);
                        updateTotals();
                    })
                    .catch(() => populateItems([]));
            }
        }
    });

    // Tender selection: auto-fill supplier and items
    $(document).on('change', '#tenderNo', function() {
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

        if (!isNaN(supplierId)) {
            const name = supplierName || `Supplier #${supplierId}`;
            $('input[name="address"]').val(address);
            if ($("input[name='supplier']").length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'supplier',
                    value: String(supplierId)
                }).appendTo('#purchaseOrdersForm');
            } else {
                $("input[name='supplier']").val(String(supplierId));
            }
        }

        if (!isNaN(tenderId)) {
            fetch(`/procurement/purchase-order/tender-items/${tenderId}`)
                .then(r => r.json())
                .then(({
                    items
                }) => {
                    populateItems(items || []);
                    updateTotals();
                })
                .catch(() => populateItems([]));
        }
    });

    // Contract selection: auto-fill supplier and load contract tender items
    $(document).on('change', '#contractRef', function() {
        const opt = $(this).find('option:selected');
        const contractId = parseInt(opt.data('contract-id'));
        const supplierId = parseInt(opt.data('supplier-id'));
        const supplierName = opt.data('supplier-name') || '';
        const address = opt.data('address') || '';

        if (!isNaN(contractId)) {
            $('#SourceId').val(contractId);
        } else {
            $('#SourceId').val('');
        }

        if (!isNaN(supplierId)) {
            const name = supplierName || `Supplier #${supplierId}`;
            $('input[name="address"]').val(address);
            if ($("input[name='supplier']").length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'supplier',
                    value: String(supplierId)
                }).appendTo('#purchaseOrdersForm');
            } else {
                $("input[name='supplier']").val(String(supplierId));
            }
        }

        // Load contract tender items and update available items
        if (!isNaN(contractId)) {
            fetch(`/procurement/purchase-order/contract-items/${contractId}`)
                .then(r => r.json())
                .then(({
                    items,
                    availableItems
                }) => {
                    // Update global item options with contract-specific tender items
                    updateItemOptionsForContract(availableItems || []);

                    // Populate the form with the tender items
                    populateItems(items || []);
                    updateTotals();
                })
                .catch(err => {
                    console.error('Failed to load contract tender items:', err);
                    populateItems([]);
                });
        }
    });

    // No supplier dropdown in UI; address is updated by specific handlers

    $(function() {
        $('form#purchaseOrdersForm').submit(function(e) {
            e.preventDefault();

            // Ensure supplier value is set (Direct uses preqSupplier, others set hidden earlier)
            const preqVal = $('#preqSupplier').val();
            if (preqVal && $("input[name='supplier']").length === 0 && $('input[name="SourceType"]:checked').val() === 'DIRECT') {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'supplier',
                    value: String(preqVal)
                }).appendTo('#purchaseOrdersForm');
            }

            // Validate required fields before submission
            let isValid = true;
            let errorMessages = [];

            // If Direct mode, ensure a plan is selected
            const mode = $('input[name="SourceType"]:checked').val();
            if (mode === 'DIRECT') {
                if (!$('#directPlanSelect').val()) {
                    errorMessages.push('Please select an approved procurement plan');
                    isValid = false;
                }
            }

            // Check supplier
            if (mode === 'DIRECT') {
                if (!$('#preqSupplier').val()) {
                    errorMessages.push('Please select a prequalified supplier');
                    isValid = false;
                }
            } else {
                const hiddenSup = $("input[name='supplier']").val();
                if (!hiddenSup) {
                    errorMessages.push('Supplier could not be determined from your selection');
                    isValid = false;
                }
            }

            // Check payment terms
            if (!$('#terms').val()) {
                errorMessages.push('Please select payment terms');
                isValid = false;
            }

            // Check if we have at least one item
            const itemCount = $('.itemCode').length;
            if (itemCount === 0) {
                errorMessages.push('Please add at least one item');
                isValid = false;
            }

            // Check each item has required fields
            $('.itemCode').each(function(index) {
                const $row = $(this).closest('tr');
                const itemCode = $(this).val();
                const quantity = $row.find('.quantity').val();
                const unitPrice = $row.find('.unit-price').val();

                if (!itemCode) {
                    errorMessages.push(`Row ${index + 1}: Please enter an item`);
                    isValid = false;
                }
                if (!quantity || quantity <= 0) {
                    errorMessages.push(`Row ${index + 1}: Please enter a valid quantity`);
                    isValid = false;
                }
                if (!unitPrice || unitPrice < 0) {
                    errorMessages.push(`Row ${index + 1}: Please enter a valid unit price`);
                    isValid = false;
                }
            });

            if (!isValid) {
                alert('Please fix the following errors:\n\n' + errorMessages.join('\n'));
                return false;
            }

            // Show loading state
            $('#saveOrder').prop('disabled', true).text('Saving...');

            // Submit the form normally
            this.submit();
        });
    });

    // === Direct mode wiring ===
    function setDirectMode(enabled) {
        const $direct = $('.direct-only');
        const $supplierRow = $('.supplier-row');
        if (enabled) {
            // Show supplier row because it contains the Plan dropdown
            $supplierRow.removeClass('d-none');
            // Keep item category row hidden until a plan is selected
            $direct.addClass('d-none');
            // load root categories and merge with direct-plan categories
            const $sel = $('#itemCategory');
            $sel.empty().append(`<option value="">-- None --</option>`);
            Promise.all([
                fetch(`{{ url('procurement/purchase-order/root-categories') }}`).then(r => r.json()).catch(() => ({
                    data: []
                })),
                fetch(`{{ url('procurement/purchase-order/direct-plan-categories') }}`).then(r => r.json()).catch(() => ({
                    data: []
                })),
            ]).then(([root, direct]) => {
                const seen = new Set();
                [...(root.data || []), ...(direct.data || [])].forEach(row => {
                    if (!row || !row.Id || seen.has(row.Id)) return;
                    seen.add(row.Id);
                    $sel.append(`<option value="${row.Id}">${row.Name}</option>`);
                });
            });
        } else {
            $direct.addClass('d-none');
            $supplierRow.removeClass('d-none');
        }
    }

    function refreshSourceUI() {
        const src = $('input[name="SourceType"]:checked').val();
        setDirectMode(src === 'DIRECT');
    }
    $(document).on('change', 'input[name="SourceType"]', refreshSourceUI);
    // initial
    refreshSourceUI();

    // Category change => load plan-scoped items and prequalified suppliers
    $('#itemCategory').on('change', function() {
        const catId = $(this).val() ? parseInt($(this).val(), 10) : 0;
        const planId = $('#directPlanSelect').val() ? parseInt($('#directPlanSelect').val(), 10) : 0;
        if (!(catId > 0) || !(planId > 0)) {
            return;
        }
        // 1) Populate prequalified supplier dropdown for this category
        const $preqSupplier = $('#preqSupplier');
        $preqSupplier.prop('disabled', true).empty().append('<option value="" selected>-- None --</option>');
        // Clear hidden supplier and address until user selects one for this category
        const $hiddenSup = $("input[name='supplier']");
        if ($hiddenSup.length) {
            $hiddenSup.val('');
        }
        $('input[name="address"]').val('');
        fetch(`{{ url('procurement/purchase-order/prequalified-suppliers') }}/${catId}`)
            .then(r => r.json()).then(({
                data
            }) => {
                (data || []).forEach(row => {
                    const supplierId = row.SupplierId || row.SupplierID || '';
                    const display = row.SupplierName || (supplierId ? `Supplier #${supplierId}` : `ThirdParty #${row.ThirdPartyId || ''}`);
                    const address = row.Address || '';
                    const value = String(supplierId).length ? supplierId : (row.ThirdPartyId || '');
                    $preqSupplier.append(`<option value="${value}" data-supplier-id="${supplierId || ''}" data-address="${address}">${display}</option>`);
                });
                $preqSupplier.prop('disabled', false);
            }).catch(() => {});

        // 2) Load plan items filtered by category and populate the grid
        fetch(`{{ url('procurement/purchase-order/plan') }}/${planId}/category/${catId}/items`)
            .then(r => r.json())
            .then((resp) => {
                const items = (resp && (resp.data || resp.items)) ? (resp.data || resp.items) : [];
                populateItems(Array.isArray(items) ? items : []);
                updateTotals();
                if (!items || items.length === 0) {
                    // Give a hint if category has no pending Direct items in this plan
                    // alert('No items found for the selected category in this plan.'); // optional
                }
            })
            .catch(() => {});
    });

    // Selecting a prequalified supplier should set the hidden supplier input and address
    $(document).on('change', '#preqSupplier', function() {
        const $opt = $(this).find('option:selected');
        const supplierId = String($opt.data('supplier-id') || $(this).val() || '').trim();
        const address = $opt.data('address') || '';
        if (supplierId) {
            // Update hidden supplier input
            if ($("input[name='supplier']").length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'supplier',
                    value: String(supplierId)
                }).appendTo('#purchaseOrdersForm');
            } else {
                $("input[name='supplier']").val(String(supplierId));
            }
        }
        $('input[name="address"]').val(address);
    });

    // Remove row handler and totals calculation kept from your current dev form
    // Add Item Button Handler
    $(document).on('click', '#add-row', function() {
        const $tbody = $('#item-rows');
        const rowCount = $tbody.find('tr').length + 1;

        const $newRow = $(`
                <tr>
                    <td class="line-no">${rowCount}.</td>
                    <td class="text-start">
                        <input type="text" class="form-control form-control-sm itemCode" name="itemCode[]" placeholder="Item (code/name)" required>
                    </td>
                    <td class="text-start">
                        <textarea class="form-control form-control-sm itemDescription textarea-compact" name="itemDescription[]" rows="3" readonly></textarea>
                    </td>
                    <td class="text-start">
                        <input type="number" class="form-control form-control-sm qty quantity number-input" name="quantity[]" step="any" required>
                    </td>
                    <td class="text-start">
                        <input type="number" class="form-control form-control-sm unit-price number-input" name="unitPrice[]" step="any" required>
                    </td>
                    <td class="text-start">
                        <input type="number" class="form-control form-control-sm tax number-input" name="tax[]" step="any">
                    </td>
                    <td class="text-start">
                        <input type="number" class="form-control form-control-sm discount number-input" name="discount[]" step="any">
                    </td>
                    <td class="text-start">
                        <input type="number" class="form-control form-control-sm line-total number-input" name="lineTotal[]" step="any" readonly>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Item">
                            <i class="fa fa-trash"></i> Remove
                        </button>
                    </td>
                </tr>
            `);

        $tbody.append($newRow);
        updateRowNumbers();
    });

    // Item Selection Change Handler
    $(document).on('change', '.itemCode', function() {
        if (this.tagName !== 'SELECT') return;
        const $row = $(this).closest('tr');
        const $option = $(this).find('option:selected');
        const itemName = $option.data('name') || $option.text();
        const itemDescription = $option.data('description') || itemName;
        const unitPrice = $option.data('price') || 0;
        // Update description and price
        $row.find('.itemDescription').val(itemDescription);
        $row.find('.unit-price').val(unitPrice);
        // Recalculate totals
        recalcRow($row);
        updateTotals();
    });

    // Remove Row Handler
    $(document).on('click', '.remove-row', function() {
        const $tbody = $('#item-rows');
        if ($tbody.find('tr').length > 1) {
            $(this).closest('tr').remove();
            updateRowNumbers();
            updateTotals();
        } else {
            alert('At least one item row is required.');
        }
    });

    // Update quantity, price, tax, discount handlers
    $(document).on('input', '.quantity, .unit-price, .tax, .discount', function() {
        const $row = $(this).closest('tr');
        recalcRow($row);
        updateTotals();
    });

    // Update row numbers
    function updateRowNumbers() {
        $('#item-rows tr').each(function(index) {
            $(this).find('.line-no').text((index + 1) + '.');
        });
    }

    // Initialize on page load
    $(document).ready(function() {
        updateTotals();
    });

    // Direct plan selection handler
    $(document).on('change', '#directPlanSelect', function() {
        const val = $(this).val();
        if (val) {
            $('#SourceId').val(val); // set SourceId to PlanID for backend awareness
            // Clear current items before loading
            populateItems([]);
            const $planSel = $(this);
            $planSel.prop('disabled', true);
            fetch(`/procurement/purchase-order/direct-plan-items/${val}`)
                .then(r => r.json())
                .then(({
                    success,
                    data,
                    items
                }) => {
                    const rows = data || items || [];
                    if (rows && rows.length >= 0) {
                        populateItems(rows);
                        updateTotals();
                        // Now reveal the Item Category and supplier helpers for further narrowing
                        $('.direct-only').removeClass('d-none');
                        // Load categories tied to this plan
                        const $cat = $('#itemCategory');
                        $cat.prop('disabled', true).empty().append('<option value="" selected>-- None --</option>');
                        fetch(`{{ url('procurement/purchase-order/plan') }}/${val}/categories`)
                            .then(r => r.json())
                            .then(({
                                success,
                                data
                            }) => {
                                (data || []).forEach(row => {
                                    $cat.append(`<option value="${row.Id}">${row.Name}</option>`);
                                });
                                $cat.prop('disabled', false);
                            })
                            .catch(() => {
                                $cat.prop('disabled', false);
                            });
                    } else {
                        alert('No items found for selected plan.');
                    }
                })
                .catch(() => alert('Error loading plan items.'))
                .finally(() => $planSel.prop('disabled', false));
        } else {
            $('#SourceId').val('');
            populateItems([]);
            // Hide again when plan cleared
            $('.direct-only').addClass('d-none');
            $('#itemCategory').empty().append('<option value="" selected>-- None --</option>');
        }
    });
</script>
@endsection
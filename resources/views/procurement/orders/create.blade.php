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
            <!-- RFQ Selection First -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label>Reference Number (RFQ) <span class="text-danger">*</span></label>
                    <select class="form-control refNo @error('refNo') is-invalid @enderror" name="refNo" id="refNo" required>
                        <option selected disabled>Select RFQ</option>
                        @foreach($rfqs as $rfq)
                            <option value="{{ $rfq->RFQNumber }}" {{ old('refNo') == $rfq->RFQNumber ? 'selected' : '' }}>{{ $rfq->RFQNumber ?? '' }}</option>
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
                    <input type="date" class="form-control poDate @error('pODate') is-invalid @enderror" name="pODate" value="{{ old('pODate') }}" required/>
                    @error('pODate')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Supplier & Details (after RFQ) -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label>Supplier <span class="text-danger">*</span></label>
                    <select class="form-control supplier @error('supplier') is-invalid @enderror" id="supplier" name="supplier" required>
                        <option selected disabled>Select supplier</option>
                        {{-- Options will be populated by JS based on selected RFQ --}}
                    </select>
                    @error('supplier')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label>Address</label>
                    <input type="text" class="form-control" name="address" placeholder="Supplier address" readonly/>
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
                    <select class="form-control terms @error('terms') is-invalid @enderror" name="terms" required>
                        <option selected disabled>Select Payment Term</option>
                        @foreach ($paymentTerms as $term)
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
                        <th style="width: 10%; min-width: 100px;">Item Type <span class="text-danger">*</span></th>
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
                            <select class="form-select form-select-sm type" name="type[]" id="Type" required>
                                <option disabled selected>Select Type</option>
                                @foreach ($itemTypes as $type)
                                    <option value="{{ $type->Id }}">{{ $type->TypeName }}</option>
                                @endforeach
                            </select>
                        </td>
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
        const prefillContract = @json($prefillContract ?? null);
    </script>

    <script>
        @php($itemTypes = collect($itemTypes))
        const itemTypeOptions = `{!! $itemTypes->map(function($type) {
        return "<option value='{$type->Id}'>{$type->TypeName}</option>";
    })->implode('') !!}`;
    </script>

    <script>

        // Filter suppliers when RFQ is selected
        $(document).on('change', '#refNo', function () {
            const selectedRFQ = $(this).val();
            // Filter rfqResponses for this RFQ
            const suppliers = rfqResponses.filter(r => r.RFQNumber === selectedRFQ);
            // Remove duplicates by SupplierId or SupplierName
            const uniqueSuppliers = [];
            const seen = new Set();
            suppliers.forEach(s => {
                const key = s.SupplierName + (s.SupplierId || s.SupplierID || '');
                if (!seen.has(key)) {
                    uniqueSuppliers.push(s);
                    seen.add(key);
                }
            });
            // Populate supplier dropdown
            const $supplier = $('#supplier');
            $supplier.empty().append('<option selected disabled>Select supplier</option>');
            uniqueSuppliers.forEach(s => {
                $supplier.append(`<option value="${s.SupplierId || s.Id || ''}" data-address="${s.Address || ''}">${s.SupplierName || s.Name || ''}</option>`);
            });
            // Clear address field
            $('input[name="address"]').val('');
        });

        // If coming from contract, preselect reference & supplier
        $(function(){
            if (prefillContract && prefillContract.ref) {
                const $ref = $('#refNo');
                if ($ref.length) {
                    $ref.val(prefillContract.ref).trigger('change');
                    setTimeout(function(){
                        if (prefillContract.supplierId) {
                            $('#supplier').val(prefillContract.supplierId).trigger('change');
                        }
                        if (prefillContract.address) {
                            $('input[name="address"]').val(prefillContract.address);
                        }
                    }, 250);
                }
            }
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
            // Handle item type change using event delegation

            $('form#purchaseOrdersForm').submit(async function (e) {
                // alert($('#RequisitionID').val());
                e.preventDefault();
                if (await saveForm($(this), $('#saveOrder'), true, true, true)) {
                    $Modal.modal('hide');
                }

            });


            $(document).on('change', '.type', function () {
                let row = $(this).closest('tr');
                let type = $(this).val();

                if (type !== '') {
                    $.ajax({
                        url: `/procurement/requisitionItem/getItem/${type}`,
                        type: 'GET',
                        success: function (response) {

                            console.log(response)
                            let itemCodeSelect = row.find('.itemCode');
                            itemCodeSelect.empty().append(
                                '<option value="">Select Item</option>');

                            $.each(response.data, function (key, item) {
                                itemCodeSelect.append(
                                    `<option value="${item.Id}">${item.ItemName}</option>`
                                );
                            });
                        },
                        error: function (response) {
                            alert('Failed to load items');
                            console.log(response);
                        }
                    });
                } else {
                    row.find('.itemCode').empty().append('<option value="">Select Item</option>');
                }
            });

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
                $('#po-items tr').each(function () {
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


            $(document).ready(function () {
                calculateSummaryTotals();
            });

            function updateLineNumbers() {
                $('#po-items tr').each(function (index) {
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
                <select class="form-select form-select-sm type" name="type[]" id="Type" required>
                    <option disabled selected>Select Type</option>
                    ${itemTypeOptions}
                </select>
            </td>
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
            document.getElementById('po-items').insertAdjacentHTML('beforeend', row);
            updateLineNumbers();
        });

        // Remove row handler
        $(document).on('click', '.remove-row', function () {
            // Only remove if more than one row remains
            if ($('#po-items tr').length > 1) {
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

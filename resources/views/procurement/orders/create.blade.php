@extends('layouts.app')
@section('title', 'Purchase Order')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
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
        <h2 class="text-center my-4">@yield('title')</h2>

        <!-- Top Buttons -->
        <div class="d-flex justify-content-between mb-3">
            <div>
                <button class="btn btn-primary">New LPO</button>
                <button class="btn btn-secondary">Open</button>
                <button class="btn btn-info">Print</button>
            </div>
            <button class="btn btn-success">Place Order</button>
        </div>
        <form action="{{ route('purchaseOrder.store') }}" method="post" id="purchaseOrdersForm">
            @csrf
            <!-- Supplier & Details -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label>Supplier</label>
                    <select class="form-control supplier" id="supplier" name="supplier">
                        <option selected disabled>Select supplier</option>
                        {{--                        <option value="01">Supplier 1</option>--}}
                        {{--                        <option value="02">Supplier 2</option>--}}
                        @foreach ($suppliers as $vendor)
                            <option value="{{ $vendor->Id }}">{{ $vendor->Name }}</option>
                        @endforeach

                        <!-- Loop suppliers here -->
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Address</label>
                    <input type="text" class="form-control" name="address" placeholder="Supplier address"/>
                </div>
            </div>

            <!-- LPO Details -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label>LPO Number</label>
                    <input type="text" name="LPONo" class="form-control" value="{{ uniqid('LPO-') }}" readonly/>
                </div>
                <div class="col-md-4">
                    <label>Date</label>
                    <input type="date" class="form-control poDate" name="pODate"/>
                </div>
                <div class="col-md-4">
                    <label>Reference Number</label>
                    <input type="text" class="form-control refNo" name="refNo" placeholder="RFQ Number"/>
                </div>
                <div class="col-md-4 mt-2">
                    <label>Priority</label>
                    <select class="form-control priority" name="priority">
                        <option>High</option>
                        <option>Medium</option>
                        <option>Low</option>
                    </select>
                </div>
                <div class="col-md-4 mt-2">
                    <label>Payment Terms</label>
                    <input type="text" name="terms" class="form-control terms" placeholder="e.g., Net 30, 50%"/>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-outline-primary" id="add-row">
                    + Add Item
                </button>
            </div>


            <!-- Line Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 3%; min-width: 30px;">#</th>
                        <th style="width: 10%; min-width: 100px;">Item Type</th>
                        <th style="width: 15%; min-width: 150px;">Item Name</th>
                        <th style="width: 20%; min-width: 200px;">Item Description</th>
                        <th style="width: 5%; min-width: 80px;">Quantity</th>
                        <th style="width: 10%; min-width: 100px;">Unit Price</th>
                        <th style="width: 5%; min-width: 80px;">Tax</th>
                        <th style="width: 5%; min-width: 80px;">Discount</th>
                        <th style="width: 15%; min-width: 150px;">Line Total</th>
                    </tr>
                    </thead>
                    <tbody id="po-items">
                    <tr>
                        <td class="line-no">1.</td>
                        <td class="text-start">
                            <select class="form-select form-select-sm type" name="type[]" id="Type">
                                <option disabled selected>Select Type</option>
                                @foreach ($itemTypes as $type)
                                    <option value="{{ $type->Id }}">{{ $type->TypeName }}</option>
                                @endforeach
{{--                                <option value="Stock">Stock</option>--}}
{{--                                <option value="Asset">Asset</option>--}}
{{--                                <option value="Non-Stock">Non-Stock</option>--}}
                            </select>
                        </td>
                        <td class="text-start">
                            <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item">
                                <option disabled selected>Select Item Code</option>

                                {{--                                                                <option value="1">Item1</option>--}}
                                {{--                                                                <option value="2">Item2</option>--}}
                            </select>
                        </td>
                        {{--                    <td><input type="text" class="form-control" name="itemCode[]"></td> --}}
                        <td class="text-start">
                                <textarea class="form-control form-control-sm itemDescription" name="itemDescription[]"
                                          id="Description" cols="30"
                                          rows="5" readonly
                                          style="display: flex; align-items: center; justify-content: center; text-align: center; padding: 0; resize: none;"></textarea>
                            {{--                            <input type="text" class="form-control form-control-sm itemDescription" --}}
                            {{--                                name="itemDescription[]" id="Description" readonly> --}}
                        </td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm qty quantity"
                                                      name="quantity[]" id="Quantity" step="any"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm unit-price "
                                                      name="unitPrice[]" id="Price" step="any"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm tax"
                                                      name="tax[]" id="Tax" step="any"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm discount"
                                                      name="discount[]" id="Discount" step="any"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm line-total"
                                                      name="lineTotal[]" id="lineTotal" step="any"></td>
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
        const itemTypeOptions = `{!! $itemTypes->map(function($type) {
        return "<option value='{$type->Id}'>{$type->TypeName}</option>";
    })->implode('') !!}`;
    </script>

    <script>
        function fetchSuppliers() {
            const supplierUrl = "{{ route('purchaseOrder.getSuppliers') }}"


            // console.log(supplierUrl);

            $.ajax({
                url: supplierUrl,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log('AJAX Response:', response);


                    if (!response || !response.data || response.data.length === 0) {
                        console.warn('No suppliers found');
                        $('#supplier').html('<option selected disabled>No suppliers available</option>');
                        return;
                    }

                    let supplierSelect = $('#supplier');
                    if (supplierSelect.children().length <= 1) {
                        supplierSelect.empty().append('<option selected disabled>Select supplier</option>');

                        $.each(response.data, function (key, item) {
                            supplierSelect.append(
                                `<option value="${item.id}">${item.name}</option>`
                            );
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error: ', status, error);
                    console.error('Raw response:', xhr.responseText); // This is key!
                    $('#supplier').html('<option selected disabled>Error loading suppliers</option>');
                }
            });
        }

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

            // Handle quantity or price change and calculate line total
            $(document).on('change', '.quantity, .unit-price, .tax, .discount', function () {
                let row = $(this).closest('tr');
                let qty = parseFloat(row.find('.quantity').val()) || 0;
                let price = parseFloat(row.find('.unit-price').val()) || 0;
                let tax = parseFloat(row.find('.tax').val()) || 0;
                let discount = parseFloat(row.find('.discount').val()) || 0;

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
                <select class="form-select form-select-sm type" name="type[]" id="Type">
                    <option disabled selected>Select Type</option>
                    ${itemTypeOptions}
                </select>
            </td>
            <td class="text-start">
                <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item">
                    <option disabled selected>Select Item Code</option>
                </select>
            </td>

            <td>
                <textarea class="form-control form-control-sm itemDescription" name="itemDescription[]"
                                          id="Description" cols="30"
                                          rows="5" readonly   style="display: flex; align-items: center; justify-content: center; text-align: center; padding: 0; resize: none;"></textarea>
            </td>
            <td><input type="number" class="form-control form-control-sm qty quantity" name="quantity[]" id="Quantity" ></td>
            <td><input type="number" class="form-control form-control-sm unit-price" name="unitPrice[]" id="Price" ></td>
            <td><input type="number" class="form-control form-control-sm tax" name="tax[]" id="Tax" ></td>
            <td><input type="number" class="form-control form-control-sm discount" name="discount[]" id="Discount" ></td>
            <td><input type="number" class="form-control form-control-sm line-total" name="lineTotal[]"  id="lineTotal" readonly></td>
        </tr>`;
            document.getElementById('po-items').insertAdjacentHTML('beforeend', row);
            updateLineNumbers()
        });
    </script>

@endsection

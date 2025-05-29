@extends('layouts.app')
@section('title', 'Purchase Order')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }


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
                        <option disabled selected>Choose Supplier</option>
                    </select>
                </div>
                {{--                <div class="col-md-6">--}}
                {{--                    <label>Address</label>--}}
                {{--                    <input type="text" class="form-control" name="address" placeholder="Supplier address"/>--}}
                {{--                </div>--}}
            </div>

            <!-- LPO Details -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label>LPO Number</label>
                    <input type="text" name="LPONo" class="form-control" value="" readonly/>
                </div>
                <div class="col-md-4">
                    <label>Date</label>
                    <input type="date" class="form-control poDate" name="pODate" id="pODate" value=""/>
                </div>
                <div class="col-md-4">
                    <label>Reference Number</label>
{{--                    <input type="text" class="form-control refNo" name="refNo" placeholder="RFQ Number" value="{{$orderInfo->ExtOrdNum ?? 'N/A'}}"/>--}}
                    <select class="form-control refNo" name="refNo" id="refNo">
                        <option selected disabled>Select RFQ</option>
                            @foreach ($RFQ as $data)
                                <option value="{{ $data->RFQId }}">{{ $data->RFQNumber }}</option>
                            @endforeach
                    </select>

                </div>
                <div class="col-md-4 mt-2">
                    <label>Priority</label>
                    <select class="form-control priority" name="priority">
                        <option selected></option>
                    </select>
                </div>
                <div class="col-md-4 mt-2">
                    <label>Payment Terms</label>
                    <input type="text" name="terms" class="form-control terms" placeholder="e.g., Net 30, 50%" value=""/>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-outline-primary" id="add-row">
                    + Add Item
                </button>
            </div>


            <!-- Line Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm" id="poTable">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 1%; min-width: 10px;">#</th>
                        <th style="width: 10%; min-width: 100px;">Item Type</th>
                        <th style="width: 15%; min-width: 150px;">Item Name</th>
                        <th style="width: 20%; min-width: 200px;">Item Description</th>
                        <th style="width: 5%; min-width: 80px;">Quantity</th>
                        <th style="width: 10%; min-width: 100px;">Unit Price</th>
                        <th style="width: 7%; min-width: 80px;">Tax</th>
                        <th style="width: 5%; min-width: 80px;">Discount</th>
                        <th style="width: 15%; min-width: 150px;">Line Total</th>
                    </tr>
                    </thead>
                    <tbody id="po-items">
{{--                        <tr>--}}
{{--                            <td class="line-no">{{ $index + 1 }}</td>--}}
{{--                            <td class="text-start">--}}
{{--                                <select class="form-select form-select-sm type" name="type[]" id="Type">--}}
{{--                                    <option  selected>{{$line ->ItemType}}</option>--}}

{{--                                </select>--}}
{{--                            </td>--}}
{{--                            <td class="text-start">--}}
{{--                                <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item">--}}
{{--                                    <option  selected>{{$line ->ItemName}}</option>--}}
{{--                                </select>--}}
{{--                            </td>--}}
{{--                            --}}{{--                    <td><input type="text" class="form-control" name="itemCode[]"></td> --}}
{{--                            <td class="text-start">--}}
{{--                                <textarea class="form-control form-control-sm itemDescription" name="itemDescription[]"--}}
{{--                                          id="Description" cols="30"--}}
{{--                                          rows="5" readonly style="display: flex; align-items: center; justify-content: center; text-align: center; padding: 0; resize: none;">{{$line ->Description}}</textarea>--}}
{{--                             --}}
{{--                            </td>--}}
{{--                            <td class="text-start"><input type="number" class="form-control form-control-sm qty quantity"--}}
{{--                                                          name="quantity[]" id="Quantity" value="{{$line ->fQuantity}}"></td>--}}
{{--                            <td class="text-start"><input type="number" class="form-control form-control-sm unit-price "--}}
{{--                                                          name="unitPrice[]" id="Price" value="{{$line ->fUnitPriceExcl}}"></td>--}}
{{--                            <td class="text-start"><input type="number" class="form-control form-control-sm tax"--}}
{{--                                                          name="tax[]" id="Tax" value="{{$line ->fTaxRate}}"></td>--}}
{{--                            <td class="text-start"><input type="number" class="form-control form-control-sm discount"--}}
{{--                                                          name="discount[]" id="Discount" value="{{$line ->fLineDiscount}}"></td>--}}
{{--                            <td class="text-start"><input type="number" class="form-control form-control-sm line-total"--}}
{{--                                                          name="lineTotal[]" id="lineTotal" step="" value="{{$line ->LineTotal}}"></td>--}}
{{--                        </tr>--}}
{{--                        @empty--}}
{{--                            <tr>--}}
{{--                                <td colspan="9" class="text-center">No line items found.</td>--}}
{{--                            </tr>--}}
{{--                        @endforelse--}}
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
                        <input type="text" class="form-control" value="" readonly/>
                    </div>
                    <div class="mb-2">
                        <label>Tax Amount</label>
                        <input type="text" class="form-control" value="" readonly/>
                    </div>
                    <div>
                        <label>Inclusive Total</label>
                        <input type="text" class="form-control" value="" readonly/>
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

        // fetch related RFQs

        $(document).on('change', '#refNo', function () {
            let referenceNumber = $(this).val();

            if (referenceNumber && referenceNumber !== 'Select RFQ') {
                $.ajax({
                    url: `/procurement/purchaseOrder/rqfDetails/${referenceNumber}`,
                    type: 'GET',
                    success: function (response) {
                        console.log('RFQ Details:', response);


                        if (response.success) {
                            const rfq = response.data;

                            // $('#supplier').val(rfq.SupplierName || '');
                            $('#supplier').empty().append(
                                `<option selected value="${rfq.SupplierName}">${rfq.SupplierName}</option>`
                            );
                            $('#lpo_number').val(rfq.lpoNumber || '');
                            $('#date').val(rfq.date || '');
                            $('#priority').val(rfq.priority || '');
                            $('#payment_terms').val(rfq.paymentTerms || '');

                            // Populate line items
                            if (rfq.items && rfq.items.length > 0) {
                                let itemsTable = $('#poTable tbody');
                                itemsTable.empty();
                                    // console.log('start')
                                $.each(rfq.items, function (index, line) {
                                    // const item = line.item;

                                    // console.log('end' + line.ItemType)

                                    itemsTable.append(`
                                <tr>
                                    <td class="line-no">${index + 1}</td>
                                    <td><select class="type form-control"><option value="${line.ItemType}">${line.ItemType}</option></select></td>
                                    <td><input type="text" class="itemName form-control" value="${line.ItemName}" readonly></td>
                                    <td><input type="text" class="itemDescription form-control" value="${line.ItemDescription}" readonly></td>
                                    <td><input type="number" class="quantity form-control" value="${line.Quantity}"></td>
                                    <td><input type="number" class="unit-price form-control" value="${line.QuotedPrice}"></td>
                                    <td><input type="number" class="tax form-control" value="${line.Tax || 0}"></td>
                                    <td><input type="number" class="discount form-control" value="${line.Discount || 0}"></td>
                                    <td><input type="number" class="line-total form-control" value="${line.TotalPayable || 0}"" readonly></td>
                                </tr>
                            `);
                                });
                            }
                        } else {
                            alert('Failed to load RFQ: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error fetching RFQ details:', error);
                    },
                });
            } else {
                $('#supplier, #lpo_number, #priority, #payment_terms').val('');
                $('#date').val('');
                $('#poTable tbody').empty();
            }
        });

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
                        url: `/requisitionItem/getItem/${type}`,
                        type: 'GET',
                        success: function (response) {

                            // console.log(response)/
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


            // Handle item code change using event delegation
            $(document).on('change', '.itemCode', function () {
                let row = $(this).closest('tr');
                let itemId = $(this).val();

                if (itemId !== '') {
                    $.ajax({
                        url: `/requisitionItem/getItemDetails/${itemId}`,
                        type: 'GET',
                        success: function (response) {
                            if (response.data && response.data.length > 0) {
                                $.each(response.data, function (key, item) {
                                    row.find('.itemDescription').val(item.Description ||
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
            });
        });
    </script>

    <script>
        let rowCount = 1;

        document.getElementById('add-row').addEventListener('click', function () {
            rowCount++;
            const row = `
        <tr>
            <td class="line-no">${rowCount}.</td>
            <td class="text-start">
                <select class="form-select form-select-sm type" name="type[]" id="Type">
                    <option disabled selected>Select Type</option>
                    <option value="Stock">Stock</option>
                    <option value="Asset">Asset</option>
                    <option value="Non-Stock">Non-Stock</option>
                </select>
            </td>
            <td class="text-start">
                <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item">
                    <option disabled selected>Select Item Code</option>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm itemDescription" name="item_description[]" id="Description" readonly></td>
            <td><input type="number" class="form-control form-control-sm qty quantity" name="quantity[]" id="Quantity" ></td>
            <td><input type="number" class="form-control form-control-sm unit-price" name="unit_price[]" id="Price" ></td>
            <td><input type="number" class="form-control form-control-sm tax" name="tax[]" id="Tax" ></td>
            <td><input type="number" class="form-control form-control-sm discount" name="discount[]" id="Discount" ></td>
            <td><input type="number" class="form-control form-control-sm line-total" name="line_total[]"  id="lineTotal" readonly></td>
        </tr>`;
            document.getElementById('po-items').insertAdjacentHTML('beforeend', row);
        });
    </script>

@endsection

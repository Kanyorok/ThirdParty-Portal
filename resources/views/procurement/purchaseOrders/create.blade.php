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

        <!-- Supplier & Details -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label>Supplier</label>
                <select class="form-control">
                    <option>Select supplier</option>
                    <!-- Loop suppliers here -->
                </select>
            </div>
            <div class="col-md-6">
                <label>Address</label>
                <input type="text" class="form-control" placeholder="Supplier address" />
            </div>
        </div>

        <!-- LPO Details -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label>LPO Number</label>
                <input type="text" class="form-control" value="{{ uniqid('LPO-') }}" readonly />
            </div>
            <div class="col-md-4">
                <label>Date</label>
                <input type="date" class="form-control" />
            </div>
            <div class="col-md-4">
                <label>Reference Number</label>
                <input type="text" class="form-control" placeholder="RFQ Number" />
            </div>
            <div class="col-md-4 mt-2">
                <label>Priority</label>
                <select class="form-control">
                    <option>High</option>
                    <option>Medium</option>
                    <option>Low</option>
                </select>
            </div>
            <div class="col-md-4 mt-2">
                <label>Payment Terms</label>
                <input type="text" class="form-control" placeholder="e.g., Net 30, 50%" />
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
                        <th style="width:5%;">#</th>
                        <th style="width:10%;">Item Type</th>
                        <th style="width:15%;">Item Code</th>
                        <th style="width:15%;">Item Description</th>
                        <th style="width:10%;">Quantity</th>
                        <th style="width:10%;">Unit Price</th>
                        <th style="width:10%;">Tax</th>
                        <th style="width:10%;">Discount</th>
                        <th style="width:20%;">Line Total</th>
                    </tr>
                </thead>
                <tbody id="po-items">
                    <tr>
                        <td class="line-no">1.</td>
                        <td class="text-start">
                            <select class="form-select form-select-sm type" name="type[]" id="Type">
                                <option disabled selected>Select Type</option>
                                <option value="good">Goods</option>
                                <option value="service">Services</option>
                            </select>
                        </td>
                        <td class="text-start">
                            <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item">
                                <option disabled selected>Select Item Code</option>
                                {{--                            <option value="good">Goods</option> --}}
                                {{--                            <option value="services">Services</option> --}}
                            </select>
                        </td>
                        {{--                    <td><input type="text" class="form-control" name="itemCode[]"></td> --}}
                        <td class="text-start"><input type="text" class="form-control form-control-sm itemDescription"
                                name="itemDescription[]" id="Description" readonly></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm qty quantity"
                                name="quantity[]" id="Quantity"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm unit-price "
                                name="unitPrice[]" id ="Price"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm tax" name="tax[]"
                                id="Tax"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm discount"
                                name="discount[]" id="Discount"></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm line-total"
                                name="lineTotal[]" id="lineTotal"></td>
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
                    <input type="text" class="form-control" readonly />
                </div>
                <div class="mb-2">
                    <label>Tax Amount</label>
                    <input type="text" class="form-control" readonly />
                </div>
                <div>
                    <label>Inclusive Total</label>
                    <input type="text" class="form-control" readonly />
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary me-2">Save</button>
            <button class="btn btn-warning me-2">Edit</button>
            <button class="btn btn-danger">Delete</button>
        </div>
    </div>



@endsection
@section('scripts')
    <script>
        $(function () {
            // Handle item type change using event delegation
            $(document).on('change', '.type', function () {
                let row = $(this).closest('tr');
                let type = $(this).val();

                if (type !== '') {
                    $.ajax({
                        url: `/requisitionItem/getItem/${type}`,
                        type: 'GET',
                        success: function (response) {
                            let itemCodeSelect = row.find('.itemCode');
                            itemCodeSelect.empty().append('<option value="">Select Item</option>');

                            $.each(response.data, function (key, item) {
                                itemCodeSelect.append(
                                    `<option value="${item.id}">${item.name}</option>`
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
                                    row.find('.itemDescription').val(item.Description || '');
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

        document.getElementById('add-row').addEventListener('click', function() {
            rowCount++;
            const row = `
        <tr>
            <td class="line-no">${rowCount}.</td>
            <td class="text-start">
                <select class="form-select form-select-sm type" name="type[]" id="Type">
                    <option disabled selected>Select Type</option>
                    <option value="good">Goods</option>
                    <option value="service">Services</option>
                </select>
            </td>
            <td class="text-start">
                <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item">
                    <option disabled selected>Select Item Code</option>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm itemDescription" name="item_description[]" id="Description" ></td>
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

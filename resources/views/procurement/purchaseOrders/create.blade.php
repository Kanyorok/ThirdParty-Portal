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
{{--    <div class="mb-3">--}}
{{--        <h1 class="h3 d-inline align-middle">@yield('title')</h1>--}}
{{--    </div>--}}

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
                    <th>Line No.</th>
                    <th>Item Type</th>
                    <th>Item Code</th>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Tax</th>
                    <th>Discount</th>
                    <th>Line Total</th>
                </tr>
                </thead>
                <tbody id="po-items">
                <tr>
                    <td class="line-no">1.</td>
                    <td class="text-start">
                        <select class="form-select form-select-sm" name="type[]" id="Type">
                            <option disabled selected>Select Type</option>
                            <option value="good">Goods</option>
                            <option value="service">Services</option>
                        </select>
                    </td>
                    <td class="text-start">
                        <select class="form-select form-select-sm" name="itemCode[]" id="Item">
                            <option disabled selected>Select Item Code</option>
{{--                            <option value="good">Goods</option>--}}
{{--                            <option value="services">Services</option>--}}
                        </select>
                    </td>
{{--                    <td><input type="text" class="form-control" name="itemCode[]"></td>--}}
                    <td class="text-start"><input type="text" class="form-control form-control-sm" name="itemDescription[]" id="Description" readonly></td>
                    <td class="text-start"><input type="number" class="form-control form-control-sm qty" name="quantity[]" id="Quantity"></td>
                    <td class="text-start"><input type="number" class="form-control form-control-sm unit-price" name="unitPrice[]" id ="Price"></td>
                    <td class="text-start"><input type="number" class="form-control form-control-sm tax" name="tax[]" id="Tax"></td>
                    <td class="text-start"><input type="number" class="form-control form-control-sm discount" name="discount[]" id="Discount"></td>
                    <td class="text-start"><input type="number" class="form-control form-control-sm line-total" name="lineTotal[]"  id="lineTotal"></td>
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
        $(function() {
            $('#Type').on('change', function () {
                // alert('hello');
                let type = $(this).val();

                if (type !== '') {
                    // alert(type + 'eric');
                    $.ajax({
                        url: `/requisitionItem/getItem/${type}`,
                        type: 'GET',
                        success: function (response) {
                            // console.log('AJAX Response:', response);

                            $('#Item').empty().append('<option value="">Select Item</option>');
                            $.each(response.data, function (key, item) {
                                $('#Item').append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );

                            });
                        },
                        error: function (response) {
                            // alert('Failed to load items');
                            alert(response)
                            console.log(response)
                        }
                    });
                } else {

                    $('#Item').empty().append('<option value="">Select Item</option>')
                }
            })


            $('#Item').on('change', function () {
                // alert('hello');
                let item = $(this).val();

                if (item !== '') {
                    $.ajax({
                        url: `/requisitionItem/getItemDetails/${item}`,
                        type: 'GET',
                        success: function (response) {
                            if (response.data && response.data.length > 0) {
                                $.each(response.data, function (key, item) {

                                    $('#Description').val(item.Description || '');
                                    $('#Price').val(item.UnitPrice || '');

                                });
                            }
                        },
                        error: function (response) {
                            alert('Failed to load item details');
                            console.log(response);
                        }
                    });
                } else {

                    $('#Description').val('');
                    $('#Price').val('');

                }
            })
        })

        $('#Quantity').on('change', function () {

            let Qty = $(this).val();
            let Price = $('#Price').val()
            // let LineTotal = $('#lineTotal').val()
            let cost = 0

            if ( Qty !=='' && Price !==''){

                let cost =Qty * Price

                $('#lineTotal').val(cost || 0);
            }
            else {
                $('#lineTotal').val('');
            }


        })
    </script>

    <script>
        let rowCount = 1;

        document.getElementById('add-row').addEventListener('click', function () {
            rowCount++;
            const row = `
        <tr>
            <td class="line-no">${rowCount}.</td>
<td class="text-start">
                        <select class="form-select form-select-sm" name="type[]" id="Type">
                            <option disabled selected>Select Type</option>
                            <option value="good">Goods</option>
                            <option value="service">Services</option>
                        </select>
                    </td>
                    <td class="text-start">
                        <select class="form-select form-select-sm" name="itemCode[]" id="Item">
                            <option disabled selected>Select Item Code</option>

            </select>
        </td>
<td><input type="text" class="form-control form-control-sm" name="item_description[]" id="Description" ></td>
<td><input type="number" class="form-control form-control-sm qty" name="quantity[]" id="Quantity" ></td>
<td><input type="number" class="form-control form-control-sm unit-price" name="unit_price[]" id="Price" ></td>
<td><input type="number" class="form-control form-control-sm tax" name="tax[]" id="Tax" ></td>
<td><input type="number" class="form-control form-control-sm discount" name="discount[]" id="Discount" ></td>
<td><input type="number" class="form-control form-control-sm line-total" name="line_total[]"  id="lineTotal" readonly></td>
</tr>`;
            document.getElementById('po-items').insertAdjacentHTML('beforeend', row);
        });
    </script>

@endsection

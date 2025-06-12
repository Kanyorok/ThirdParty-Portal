@extends('layouts.app')
@section('title', 'Sales Order')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
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
                <button class="btn btn-primary">New LSO</button>
                <button class="btn btn-secondary">Open</button>
                <button class="btn btn-info">Print</button>
            </div>
            <button class="btn btn-success">Place Order</button>
        </div>

        <!-- Customer & Details -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label>Customer</label>
                <select class="form-control">
                    <option>Select customer</option>
                    <!-- Loop customers here -->
                </select>
            </div>
            <div class="col-md-6">
                <label>Address</label>
                <input type="text" class="form-control" placeholder="Customer address" />
            </div>
        </div>

        <!-- LSO Details -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label>LSO Number</label>
                <input type="text" class="form-control" value="{{ uniqid('LSO-') }}" readonly />
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
            <table class="table table-bordered">
                <thead class="table-light">
                <tr>
                    <th>Line No.</th>
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
                    <td><input type="text" class="form-control" name="item_code[]"></td>
                    <td><input type="text" class="form-control" name="item_description[]"></td>
                    <td><input type="number" class="form-control qty" name="quantity[]"></td>
                    <td><input type="number" class="form-control unit-price" name="unit_price[]"></td>
                    <td><input type="number" class="form-control tax" name="tax[]"></td>
                    <td><input type="number" class="form-control discount" name="discount[]"></td>
                    <td><input type="number" class="form-control line-total" name="line_total[]" readonly></td>
                </tr>
                </tbody>
            </table>

        </div>

        <!-- Optional Note -->
        <div class="mb-4">
            <label>Line Note</label>
            <textarea class="form-control" rows="3" placeholder="Optional message to customer"></textarea>
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
        let rowCount = 1;

        document.getElementById('add-row').addEventListener('click', function () {
            rowCount++;
            const row = `
        <tr>
            <td class="line-no">${rowCount}.</td>
            <td><input type="text" class="form-control" name="item_code[]"></td>
            <td><input type="text" class="form-control" name="item_description[]"></td>
            <td><input type="number" class="form-control qty" name="quantity[]"></td>
            <td><input type="number" class="form-control unit-price" name="unit_price[]"></td>
            <td><input type="number" class="form-control tax" name="tax[]"></td>
            <td><input type="number" class="form-control discount" name="discount[]"></td>
            <td><input type="number" class="form-control line-total" name="line_total[]" readonly></td>
        </tr>`;
            document.getElementById('po-items').insertAdjacentHTML('beforeend', row);
        });
    </script>

@endsection

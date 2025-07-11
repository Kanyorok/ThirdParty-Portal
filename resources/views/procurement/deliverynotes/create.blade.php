@extends('layouts.app')
@section('title', 'Create Delivery Note')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">📦 Create Delivery Note</h4>
        <form method="POST" action="#">
            @csrf
            <div class="mb-3">
                <label for="po_number" class="form-label">PO Number</label>
                <select class="form-select" id="po_number" name="po_number">
                    <option selected disabled>-- Select PO --</option>
                    <option value="PO-789">PO-789 - ABC Supplies Ltd</option>
                    <option value="PO-790">PO-790 - Tech World</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="delivery_date" class="form-label">Delivery Date</label>
                <input type="date" class="form-control" id="delivery_date" name="delivery_date">
            </div>

            <div class="mb-3">
                <label class="form-label">Delivered Items</label>
                <table class="table table-sm table-bordered">
                    <thead>
                    <tr>
                        <th>Item</th>
                        <th>Delivered Qty</th>
                        <th>UOM</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>HP Printer</td>
                        <td><input type="number" class="form-control" value="2"></td>
                        <td>Units</td>
                    </tr>
                    <tr>
                        <td>A4 Paper Box</td>
                        <td><input type="number" class="form-control" value="10"></td>
                        <td>Boxes</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">Submit Delivery Note</button>
        </form>
    </div>
@endsection

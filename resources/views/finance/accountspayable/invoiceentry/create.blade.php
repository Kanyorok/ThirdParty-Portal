@extends('layouts.app')
@section('title', 'Invoice Entry - Accounts Payable')
@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Invoice Entry - Accounts Payable</h2>

    <form action="submit_invoice.php" method="post">
        <div class="mb-3">
            <label for="vendor_name" class="form-label">Vendor Name</label>
            <input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="e.g., ABC Supplies Ltd." required>
        </div>

        <div class="mb-3">
            <label for="invoice_number" class="form-label">Invoice Number</label>
            <input type="text" class="form-control" id="invoice_number" name="invoice_number" placeholder="e.g., INV123456" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="invoice_date" class="form-label">Invoice Date</label>
                <input type="date" class="form-control" id="invoice_date" name="invoice_date" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date">
            </div>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount (Ksh)</label>
            <input type="number" class="form-control" id="amount" name="amount" placeholder="e.g., 12000" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Invoice Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="e.g., Office supplies and consumables"></textarea>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Payment Status</label>
            <select class="form-select" id="status" name="status" required>
                <option disabled selected>Select Status</option>
                <option value="unpaid">Unpaid</option>
                <option value="partially_paid">Partially Paid</option>
                <option value="paid">Paid</option>
            </select>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">Save Invoice</button>
        </div>
    </form>
</div>
@endsection

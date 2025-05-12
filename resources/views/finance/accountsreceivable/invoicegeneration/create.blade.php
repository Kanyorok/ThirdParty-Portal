@extends('layouts.app')
@section('title', 'Invoice Generation- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <h2>Create Invoice - Accounts Receivable</h2>
    <form method="POST" action="store_invoice.php">
        <div class="mb-3">
            <label for="invoiceNumber" class="form-label">Invoice Number</label>
            <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" required>
        </div>

        <div class="mb-3">
            <label for="invoiceDate" class="form-label">Invoice Date</label>
            <input type="date" class="form-control" id="invoiceDate" name="invoiceDate" required>
        </div>

        <div class="mb-3">
            <label for="customer" class="form-label">Customer</label>
            <input type="text" class="form-control" id="customer" name="customer" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Item Description</label>
            <textarea class="form-control" id="description" name="description" rows="2" required></textarea>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="unitPrice" class="form-label">Unit Price (Ksh)</label>
                <input type="number" class="form-control" id="unitPrice" name="unitPrice" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="totalAmount" class="form-label">Total Amount (Ksh)</label>
                <input type="number" class="form-control" id="totalAmount" name="totalAmount" readonly>
            </div>
        </div>

        <div class="mb-3">
            <label for="paymentTerms" class="form-label">Payment Terms</label>
            <select class="form-control" id="paymentTerms" name="paymentTerms">
                <option>Net 30</option>
                <option>Net 45</option>
                <option>Net 60</option>
                <option>Cash on Delivery</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Generate Invoice</button>
        <a href="index_invoice.php" class="btn btn-secondary">Back to Invoices</a>
    </form>
</div>
@endsection

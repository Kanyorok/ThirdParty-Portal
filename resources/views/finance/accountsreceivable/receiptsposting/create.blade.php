@extends('layouts.app')
@section('title', 'Receipts Posting- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <div class="container mt-5">
        <h2>Receipts Posting - Record Collection</h2>
        <form method="post" action="submit_receipts_posting.php">
            <div class="mb-3">
                <label for="receiptDate" class="form-label">Receipt Date</label>
                <input type="date" class="form-control" id="receiptDate" name="receiptDate" required>
            </div>

            <div class="mb-3">
                <label for="customerName" class="form-label">Customer</label>
                <input type="text" class="form-control" id="customerName" name="customerName" required>
            </div>

            <div class="mb-3">
                <label for="invoiceNumber" class="form-label">Invoice Number</label>
                <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" required>
            </div>

            <div class="mb-3">
                <label for="receiptAmount" class="form-label">Amount Received</label>
                <input type="number" class="form-control" id="receiptAmount" name="receiptAmount" required>
            </div>

            <div class="mb-3">
                <label for="paymentMethod" class="form-label">Payment Method</label>
                <select class="form-control" id="paymentMethod" name="paymentMethod">
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cash">Cash</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Mobile Money">Mobile Money</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="referenceNo" class="form-label">Reference Number</label>
                <input type="text" class="form-control" id="referenceNo" name="referenceNo">
            </div>

            <div class="mb-3">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Post Receipt</button>
            <a href="index_receipts_posting.php" class="btn btn-secondary">Back to Index</a>
        </form>
    </div>
    @endsection

@extends('layouts.app')
@section('title', 'Customer Master- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <h2>Create Customer - Accounts Receivable</h2>
    <form method="POST" action="store_customer.php">
        <div class="mb-3">
            <label for="customerName" class="form-label">Customer Name</label>
            <input type="text" name="customerName" id="customerName" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="customerCode" class="form-label">Customer Code</label>
            <input type="text" name="customerCode" id="customerCode" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="contactPerson" class="form-label">Contact Person</label>
            <input type="text" name="contactPerson" id="contactPerson" class="form-control">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" id="email" class="form-control">
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" name="phone" id="phone" class="form-control">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea name="address" id="address" class="form-control" rows="2"></textarea>
        </div>

        <div class="mb-3">
            <label for="creditLimit" class="form-label">Credit Limit (Ksh)</label>
            <input type="number" name="creditLimit" id="creditLimit" class="form-control">
        </div>

        <div class="mb-3">
            <label for="paymentTerms" class="form-label">Payment Terms</label>
            <select name="paymentTerms" id="paymentTerms" class="form-control">
                <option value="Net 30">Net 30</option>
                <option value="Net 45">Net 45</option>
                <option value="Net 60">Net 60</option>
                <option value="Cash on Delivery">Cash on Delivery</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Save Customer</button>
        <a href="index_customer.php" class="btn btn-secondary">Back to List</a>
    </form>
</div>
@endsection

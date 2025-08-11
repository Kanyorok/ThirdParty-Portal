@extends('layouts.app')
@section('title', 'Payment Voucher - Accounts Payable')
@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Create Payment Voucher</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="voucher_no" class="form-label">Voucher Number</label>
            <input type="text" class="form-control" id="voucher_no" name="voucher_no" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>
        <div class="mb-3">
            <label for="payee" class="form-label">Payee</label>
            <input type="text" class="form-control" id="payee" name="payee" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
        </div>
        <div class="mb-3">
            <label for="payment_mode" class="form-label">Payment Mode</label>
            <select class="form-select" id="payment_mode" name="payment_mode" required>
                <option value="" disabled selected>Select mode</option>
                <option value="Cash">Cash</option>
                <option value="Cheque">Cheque</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Mobile Money">Mobile Money</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Voucher</button>
    </form>
</div>
@endsection

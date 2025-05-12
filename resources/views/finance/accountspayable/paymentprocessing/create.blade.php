@extends('layouts.app')
@section('title', 'Payment Processing - Accounts Payable')
@section('content')
<div class="container mt-5">
    <h2>Payment Processing - Accounts Payable</h2>
    <form method="post" action="#">
        <div class="mb-3">
            <label for="paymentType" class="form-label">Payment Type</label>
            <select class="form-select" id="paymentType" name="payment_type" onchange="togglePaymentMode()">
                <option value="make">Make Payment</option>
                <option value="schedule">Schedule Payment</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="vendor" class="form-label">Vendor</label>
            <input type="text" class="form-control" id="vendor" name="vendor" placeholder="Enter vendor name">
        </div>

        <div class="mb-3">
            <label for="invoiceNo" class="form-label">Invoice Number</label>
            <input type="text" class="form-control" id="invoiceNo" name="invoice_no">
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount (Ksh)</label>
            <input type="number" class="form-control" id="amount" name="amount">
        </div>

        <!-- Immediate Payment Section -->
        <div id="immediateFields">
            <div class="mb-3">
                <label for="paymentDate" class="form-label">Payment Date</label>
                <input type="date" class="form-control" id="paymentDate" name="payment_date">
            </div>
            <div class="mb-3">
                <label for="paymentMethod" class="form-label">Payment Method</label>
                <select class="form-select" id="paymentMethod" name="payment_method">
                    <option value="bank">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                    <option value="mpesa">M-Pesa</option>
                </select>
            </div>
        </div>

        <!-- Schedule Payment Section -->
        <div id="scheduleFields" style="display: none;">
            <div class="mb-3">
                <label for="scheduleDate" class="form-label">Scheduled Date</label>
                <input type="date" class="form-control" id="scheduleDate" name="schedule_date">
            </div>
            <div class="mb-3">
                <label for="scheduleNote" class="form-label">Schedule Note</label>
                <textarea class="form-control" id="scheduleNote" name="schedule_note" rows="2"></textarea>
            </div>
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Submit Payment</button>
    </form>
</div>
@endsection

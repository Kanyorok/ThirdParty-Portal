@extends('layouts.app')
@section('title', 'Vendor Master - Accounts Payable')
@section('content')

<div class="container mt-5">
    <h2 class="mb-4">Vendor Entry - Accounts Payable</h2>

    <form action="submit_vendor.php" method="post">
        <div class="mb-3">
            <label for="vendor_name" class="form-label">Vendor Name</label>
            <input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="e.g., ABC Supplies Ltd." required>
        </div>

        <div class="mb-3">
            <label for="contact_person" class="form-label">Contact Person</label>
            <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="e.g., Jane Mwangi">
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="tel" class="form-control" id="phone" name="phone" inputmode="tel" pattern="^\+[1-9]\d{7,14}$" placeholder="e.g., +12025550123" aria-describedby="vendor_phone_help">
            <div class="form-text" id="vendor_phone_help">Use international format (E.164): +[country code][number], 8–15 digits.</div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="e.g., accounts@abc.com">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Physical Address</label>
            <textarea class="form-control" id="address" name="address" rows="2" placeholder="e.g., Industrial Area, Nairobi"></textarea>
        </div>

        <div class="mb-3">
            <label for="bank_account" class="form-label">Bank Account Number</label>
            <input type="text" class="form-control" id="bank_account" name="bank_account" placeholder="e.g., 1234567890">
        </div>

        <div class="mb-3">
            <label for="bank_name" class="form-label">Bank Name</label>
            <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="e.g., Equity Bank">
        </div>

        <div class="mb-3">
            <label for="payment_terms" class="form-label">Payment Terms</label>
            <select class="form-select" id="payment_terms" name="payment_terms" required>
                <option disabled selected>Select Terms</option>
                <option value="Net 30">Net 30</option>
                <option value="Net 60">Net 60</option>
                <option value="Due on Receipt">Due on Receipt</option>
            </select>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">Save Vendor</button>
        </div>
    </form>
</div>
@endsection

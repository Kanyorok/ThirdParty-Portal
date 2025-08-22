@extends('layouts.app')
@section('title', 'New Insurance Referral')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📝 New Insurance Referral</h4>
        <form method="POST" action="#">
            @csrf

            <div class="mb-3">
                <label class="form-label">Client Full Name</label>
                <input type="text" name="ClientName" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Client Phone</label>
                <input type="text" name="ClientPhone" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Client Email</label>
                <input type="email" name="ClientEmail" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Client ID Number / Passport</label>
                <input type="text" name="ClientID" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Referring Branch</label>
                <select name="BranchID" class="form-select">
                    <option value="">-- Select Branch --</option>
                    <option value="1">Branch A</option>
                    <option value="2">Branch B</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Referring Officer</label>
                <input type="text" name="ReferringOfficer" class="form-control" value="John Doe" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Bank Product</label>
                <select name="BankProduct" class="form-select">
                    <option value="">-- Select Product --</option>
                    <option value="loan">Loan</option>
                    <option value="mortgage">Mortgage</option>
                    <option value="savings">Savings</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Suggested Insurance Type</label>
                <select name="SuggestedInsuranceType" class="form-select">
                    <option value="credit-life">Credit Life</option>
                    <option value="fire">Fire Insurance</option>
                    <option value="medical">Medical</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes / Comments</label>
                <textarea name="Notes" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Referral</button>
        </form>
    </div>
@endsection

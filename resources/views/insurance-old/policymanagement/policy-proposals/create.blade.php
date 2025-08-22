@extends('layouts.app')
@section('title', 'New Policy Proposal')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📑 New Policy Proposal</h4>

        <form method="POST" action="#">
            @csrf

            <!-- Linked Referral -->
            <div class="mb-3">
                <label class="form-label">Referral</label>
                <select name="ReferralID" class="form-select" required>
                    <option value="">-- Select Referral --</option>
                    <option value="1">Jane Njeri – Credit Life</option>
                    <option value="2">John Mwangi – Property</option>
                </select>
            </div>

            <!-- Product -->
            <div class="mb-3">
                <label class="form-label">Insurance Product</label>
                <select name="ProductID" class="form-select" required>
                    <option value="">-- Select Product --</option>
                    <option value="1">Credit Life</option>
                    <option value="2">Property Fire Cover</option>
                </select>
            </div>

            <!-- Sum Assured -->
            <div class="mb-3">
                <label class="form-label">Sum Assured (KES)</label>
                <input type="number" name="SumAssured" class="form-control" required>
            </div>

            <!-- Premium -->
            <div class="mb-3">
                <label class="form-label">Calculated Premium (KES)</label>
                <input type="number" name="Premium" class="form-control" readonly value="0">
            </div>

            <!-- Insured Period -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="StartDate" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="EndDate" class="form-control" required>
                </div>
            </div>

            <!-- Additional Notes -->
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Submit Proposal</button>
        </form>
    </div>
@endsection

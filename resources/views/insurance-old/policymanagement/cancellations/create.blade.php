@extends('layouts.app')
@section('title', 'Policy Cancellation Request')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🚫 Cancel Policy</h4>

    <form method="POST" action="#">
        @csrf

        <!-- Policy Selection -->
        <div class="mb-3">
            <label class="form-label">Select Active Policy</label>
            <select name="PolicyID" class="form-select" required>
                <option value="">-- Select Policy --</option>
                <option value="1">POL-202507001 – Jane Njeri</option>
                <option value="2">POL-202507002 – Michael Otieno</option>
            </select>
        </div>

        <!-- Cancellation Reason -->
        <div class="mb-3">
            <label class="form-label">Cancellation Reason</label>
            <select name="ReasonCode" class="form-select" required>
                <option value="">-- Choose Reason --</option>
                <option value="Client Request">Client Request</option>
                <option value="Early Loan Repayment">Early Loan Repayment</option>
                <option value="Claim Payout">Claim Payout</option>
                <option value="Duplicate Policy">Duplicate Policy</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <!-- Notes -->
        <div class="mb-3">
            <label class="form-label">Additional Comments</label>
            <textarea name="Notes" class="form-control" rows="3" placeholder="Optional comments or justification..."></textarea>
        </div>

        <button type="submit" class="btn btn-danger">Submit Cancellation</button>
    </form>
</div>
@endsection

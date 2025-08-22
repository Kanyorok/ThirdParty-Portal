@extends('layouts.app')
@section('title', 'Review Proposal')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">🧐 Underwriting – Review Proposal</h4>

        <!-- Proposal Summary -->
        <div class="card mb-4">
            <div class="card-header">📄 Proposal Information</div>
            <div class="card-body">
                <p><strong>Proposal ID:</strong> PROP-202507001</p>
                <p><strong>Client Name:</strong> Jane Njeri</p>
                <p><strong>Client Phone:</strong> 0722000000</p>
                <p><strong>Client Email:</strong> jane@example.com</p>
                <p><strong>Product:</strong> Credit Life</p>
                <p><strong>Sum Assured:</strong> KES 500,000</p>
                <p><strong>Premium:</strong> KES 5,000</p>
                <p><strong>Coverage Period:</strong> 2025-07-10 to 2026-07-10</p>
                <p><strong>Proposal Notes:</strong> Client has no pre-existing medical conditions.</p>
            </div>
        </div>

        <!-- Underwriting Tools -->
        <div class="mb-4 d-flex gap-2">
            <a href="#" class="btn btn-outline-primary">📂 View Supporting Documents</a>
            <a href="#" class="btn btn-outline-secondary">🧮 Premium Calculator</a>
        </div>

        <!-- Underwriting Action Form -->
        <form method="POST" action="#">
            @csrf
            <div class="card">
                <div class="card-header">✅ Underwriting Decision</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Decision</label>
                        <select name="Decision" class="form-select" required>
                            <option value="">-- Select Decision --</option>
                            <option value="Approved">Approve</option>
                            <option value="Rejected">Reject</option>
                            <option value="Returned">Return to Originator</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea name="Comments" class="form-control" rows="3"
                                  placeholder="Reason, justification or action to be taken..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Submit Decision</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@extends('layouts.app')
@section('title', 'Underwriting Feedback')

@section('content')
    <div class="container mt-4">
        <h4>📋 Underwriting Feedback – Policy #{{ $policy->PolicyNumber }}</h4>
        <p><strong>Customer:</strong> {{ $policy->CustomerName }}</p>

        <form method="POST" action="{{ route('bancassurance.policies.feedback.store', $policy->Id) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Underwriter Name</label>
                <input type="text" name="UnderwriterName" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Feedback Date</label>
                <input type="date" name="FeedbackDate" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Decision</label>
                <select name="Decision" class="form-select" required>
                    <option value="Approved">✅ Approved</option>
                    <option value="Declined">❌ Declined</option>
                    <option value="More Info Needed">🔁 More Info Needed</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Risk Score (%)</label>
                <input type="number" name="RiskScore" class="form-control" step="0.01" min="0" max="100">
            </div>

            <div class="mb-3">
                <label class="form-label">Comments</label>
                <textarea name="Comments" class="form-control" rows="4"></textarea>
            </div>

            <div class="text-end">
                <button class="btn btn-success">💾 Submit Feedback</button>
            </div>
        </form>
    </div>
@endsection

@extends('layouts.app')
@section('title', 'Review Proposal')

@section('content')
<div class="container mt-4">
    <h4>🧾 Review Proposal – Policy ID #{{ $policy->Id }}</h4>

    <div class="mb-4">
        <strong>Customer:</strong> {{ $policy->CustomerName }}<br>
        <strong>Product:</strong> {{ $policy->ProductName }}<br>
        <strong>Sum Assured:</strong> {{ number_format($policy->SumAssured, 2) }}<br>
        <strong>Premium:</strong> {{ number_format($policy->PremiumAmount, 2) }}
    </div>

    <form method="POST" action="{{ route('bancassurance.underwriting.submit', $policy->Id) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Underwriter Comments</label>
            <textarea name="UnderwriterComments" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Risk Rating</label>
            <select name="RiskRating" class="form-select" required>
                <option value="">-- Select Risk Level --</option>
                <option>Low</option>
                <option>Medium</option>
                <option>High</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Decision</label>
            <select name="Decision" class="form-select" required>
                <option value="">-- Select Decision --</option>
                <option>Approved</option>
                <option>Declined</option>
                <option>Request More Info</option>
            </select>
        </div>

        <div class="text-end">
            <button class="btn btn-success">✅ Submit Decision</button>
        </div>
    </form>
</div>
@endsection

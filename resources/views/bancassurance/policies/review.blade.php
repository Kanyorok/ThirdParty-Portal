@extends('layouts.app')
@section('title', 'Review Proposal')

@section('content')
<div class="container mt-4">
    <h4>📄 Review Proposal – Policy ID #{{ $policy->Id }}</h4>

    <div class="mb-4">
        <strong>Customer:</strong> {{ $policy->CustomerName }}<br>
        <strong>Product:</strong> {{ $policy->ProductName }}<br>
        <strong>Sum Assured:</strong> {{ number_format($policy->SumAssured, 2) }}<br>
        <strong>Premium:</strong> {{ number_format($policy->PremiumAmount, 2) }}<br>
        <strong>Status:</strong> <span class="badge bg-warning">{{ $policy->Status }}</span>
    </div>

    <form action="{{ route('bancassurance.policies.submitUnderwriting', $policy->Id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Upload Proposal Documents (PDF, Images, etc.)</label>
            <input type="file" name="documents[]" class="form-control" multiple required>
        </div>

        <div class="mb-3">
            <label class="form-label">Underwriter Email</label>
            <input type="email" name="underwriter_email" class="form-control" placeholder="e.g., underwriting@provider.com" required>
        </div>

        <div class="text-end">
            <button class="btn btn-primary">📤 Submit to Underwriter</button>
        </div>
    </form>
</div>
@endsection

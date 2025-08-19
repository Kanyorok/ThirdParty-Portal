@extends('layouts.app')
@section('title', 'Claims Assessment')

@section('content')
<div class="container mt-4">
    <h4>🧾 Assess Claim – #{{ $claim->policy->PolicyNumber }}</h4>

    <form method="POST" action="{{ route('bancassurance.claims.assess', $claim->Id) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Claim Type</label>
            <input type="text" class="form-control" value="{{ $claim->claimtype->Description }}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Claim Reason</label>
            <textarea class="form-control" rows="2" readonly>{{ $claim->ClaimReason }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Claim Amount</label>
            <input type="text" class="form-control" value="{{ number_format($claim->ClaimAmount, 2) }}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Assessed Amount</label>
            <input type="number" name="AssessmentAmount" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Assessment Comments</label>
            <textarea name="AssessmentComments" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Decision</label>
            <select name="Decision" class="form-select" required>
                <option value="">-- Select Decision --</option>
                @foreach($decisions as $type)
                    <option value="{{ $type->ID }}">{{ $type->Description }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3 text-end">
            <button type="submit" class="btn btn-success">✅ Submit Assessment</button>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Initiate Claim')

@section('content')
<div class="container mt-4">
    <h4>🆘 Initiate Insurance Claim</h4>

    <form method="POST" action="{{ route('bancassurance.claims.store') }}">
        @csrf

        <div class="mb-3">
            <label for="PolicyID" class="form-label">Policy Number</label>
            <select name="PolicyID" class="form-select" required>
                <option value="">-- Select Policy --</option>
                @foreach($policies as $id => $number)
                    <option value="{{ $id }}">{{ $number }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="ClaimType" class="form-label">Claim Type</label>
            <select name="ClaimType" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="Death">Death</option>
                <option value="Accident">Accident</option>
                <option value="Loss">Loss</option>
                <option value="Medical">Medical</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="ClaimReason" class="form-label">Claim Reason</label>
            <textarea name="ClaimReason" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label for="ClaimAmount" class="form-label">Claim Amount (KES)</label>
            <input type="number" step="0.01" name="ClaimAmount" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="ClaimDate" class="form-label">Date of Claim</label>
            <input type="date" name="ClaimDate" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">🚀 Submit Claim</button>
        <a href="{{ route('bancassurance.claims.index') }}" class="btn btn-secondary">Back to Claims</a>
    </form>
</div>
@endsection

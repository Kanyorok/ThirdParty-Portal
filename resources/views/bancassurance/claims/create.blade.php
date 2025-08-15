@extends('layouts.app')
@section('title', 'Initiate Claim')

@section('content')
<div class="container mt-4">

    <form method="POST" action="{{ route('bancassurance.claims.store') }}">
        @csrf

        <div class="mb-3">
            <label for="PolicyId" class="form-label">Policy Number</label>
            <select name="PolicyId" class="form-select" required>
                <option value="">-- Select Policy --</option>
                @foreach($policies as $policy)
                    <option value="{{ $policy->Id }}">{{ $policy->PolicyNumber }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="ClaimType" class="form-label">Claim Type</label>
            <select name="ClaimType" class="form-select" required>
                <option value="">-- Select Type --</option>
                @foreach($claimtypes as $claim)
                    <option value="{{ $claim->ID }}">{{ $claim->Description }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="ClaimReason" class="form-label">Claim Reason</label>
            <textarea name="ClaimReason" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label for="ClaimAmount" class="form-label">Claim Amount</label>
            <input type="number" step="0.01" name="ClaimAmount" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="ClaimDate" class="form-label">Date of Claim</label>
            <input type="date" name="ClaimDate" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="Status" class="form-label">Status</label>
            <select name="Status" class="form-select" required>
                <option value="">-- Select Status --</option>
                @foreach($claimstatus as $status)
                    <option value="{{ $status->ID }}">{{ $status->Description }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit Claim</button>
        <a href="{{ route('bancassurance.claims.index') }}" class="btn btn-secondary">Back to Claims</a>
    </form>
</div>
@endsection

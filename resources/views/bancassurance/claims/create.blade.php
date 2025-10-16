@extends('layouts.app')
@section('title', 'Initiate Claim')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary rounded-top-4">
            <p class="mb-0"><b>Claim</b></p>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.claims.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="PolicyId" class="form-label fw-semibold">Policy Number <span class="text-danger">*</span></label>
                        <select name="PolicyId" class="form-select" required>
                            <option value="">-- Select Policy --</option>
                            @foreach($policies as $policy)
                                <option value="{{ $policy->Id }}">{{ $policy->PolicyNumber }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="ClaimType" class="form-label fw-semibold">Claim Type <span class="text-danger">*</span></label>
                        <select name="ClaimType" class="form-select" required>
                            <option value="">-- Select Type --</option>
                            @foreach($claimtypes as $claim)
                                <option value="{{ $claim->ID }}">{{ $claim->Description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="ClaimAmount" class="form-label fw-semibold">Claim Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="ClaimAmount" class="form-control" placeholder="Enter claim amount" required>
                    </div>

                    <div class="col-md-6">
                        <label for="ClaimDate" class="form-label fw-semibold">Date of Claim <span class="text-danger">*</span></label>
                        <input type="date" name="ClaimDate" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="ClaimReason" class="form-label fw-semibold">Claim Reason <span class="text-danger">*</span></label>
                    <textarea name="ClaimReason" class="form-control" rows="3" placeholder="Enter the reason for the claim..." required></textarea>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('bancassurance.claims.index') }}" class="btn btn-outline-secondary me-2 px-4">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send-check me-1"></i> Submit Claim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

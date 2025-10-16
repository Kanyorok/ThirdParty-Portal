@extends('layouts.app')
@section('title', 'Claims Assessment')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <p class="mb-0"><b>Claims Assessment</b></p>
        </div>

        <div class="card-body p-4">
            <h5 class="mb-4 text-primary">Assess Claim – <span class="fw-bold">#{{ $claim->policy->PolicyNumber }}</span></h5>

            <form method="POST" action="{{ route('bancassurance.claims.assess', $claim->Id) }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Claim Type</label>
                        <input type="text" class="form-control" value="{{ $claim->claimtype->Description }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Claim Amount</label>
                        <input type="text" class="form-control" value="{{ number_format($claim->ClaimAmount, 2) }}" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Claim Reason</label>
                    <textarea class="form-control" rows="2" readonly>{{ $claim->ClaimReason }}</textarea>
                </div>

                <hr class="my-4">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assessed Amount <span class="text-danger">*</span></label>
                        <input type="number" name="AssessmentAmount" class="form-control" step="0.01" min="0" placeholder="Enter assessed amount" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Decision <span class="text-danger">*</span></label>
                        <select name="Decision" class="form-select" required>
                            <option value="">-- Select Decision --</option>
                            @foreach($decisions as $type)
                                <option value="{{ $type->ID }}">{{ $type->Description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Assessment Comments <span class="text-danger">*</span></label>
                    <textarea name="AssessmentComments" class="form-control" rows="3" placeholder="Enter your assessment comments..." required></textarea>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('bancassurance.claims.index') }}" class="btn btn-outline-secondary me-2 px-4">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Submit Assessment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

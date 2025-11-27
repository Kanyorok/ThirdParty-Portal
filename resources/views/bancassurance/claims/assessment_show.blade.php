@extends('layouts.app')
@section('title', 'Claim Assessment Details')

@section('content')
<div class="container mt-5" style="max-width: 850px;">
    <div class="card shadow-lg border-0 rounded-4">
        {{-- Header --}}
        <div class="card-header bg-primary rounded-top-4">
            <p class="mb-0"><b>Assessment info</b></p>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form>
                {{-- Claim Info --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Policy Number</label>
                        <input type="text" class="form-control"
                            value="{{ $assessment->claim->policy->PolicyNumber ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Decision</label>
                        <input type="text" class="form-control"
                            value="{{ $assessment->decision->Description ?? '-' }}" readonly>
                    </div>
                </div>

                {{-- Assessment Info --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assessed By</label>
                        <input type="text" class="form-control"
                            value="{{ $assessment->assessedby->Name ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assessment Date</label>
                        <input type="text" class="form-control"
                            value="{{ $assessment->AssessmentDate ? \Carbon\Carbon::parse($assessment->AssessmentDate)->format('d M Y') : '-' }}"
                            readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assessment Amount</label>
                        <input type="text" class="form-control"
                            value="{{ number_format($assessment->AssessmentAmount, 2) }}" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Assessment Comments</label>
                    <textarea class="form-control" rows="3" readonly>{{ $assessment->AssessmentComments ?? 'N/A' }}</textarea>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="card-footer bg-light rounded-bottom-4 px-4 py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('bancassurance.claims.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-left-circle me-1"></i> Back to List
                </a>
                <a href="#" class="btn btn-primary px-4">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                </a>
            </div>

            {{-- Created/Modified Info --}}
            <div class="border-top pt-3 text-muted small">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Created By:</strong> {{ $assessment->CreatedBy ?? '-' }}
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <strong>Modified By:</strong> {{ $assessment->ModifiedBy ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

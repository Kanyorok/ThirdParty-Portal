@extends('layouts.app')

@section('title', 'View Bid Submission')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📋 View Bid Submission Details</h4>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <strong>Submission Details</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tender Reference</label>
                        <p class="text-muted">{{ $submission->TenderRef ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Supplier Name</label>
                        <p class="text-muted">{{ $submission->SupplierName ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Submission Mode</label>
                        <p class="text-muted">{{ $submission->submissionMode->Description ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Received At</label>
                        <p class="text-muted">{{ $submission->ReceivedAt ? $submission->ReceivedAt->format('Y-m-d h:i A') : 'N/A' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Recorded By</label>
                        <p class="text-muted">{{ $submission->createdByUser->Name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <p class="text-muted">{{ $submission->Remarks ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Documents</label>
                        <p class="text-muted">
                            @if ($submission->Documents && Storage::exists($submission->Documents))
                                <a href="{{ Storage::url($submission->Documents) }}" class="btn btn-sm btn-link" download>Download</a>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

    <div class="mt-3">
        <a href="{{ route('tendersubmission.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'View Bid Responsiveness')
@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>👁️ Bid Responsiveness Details</h5>
                        <div>
                            <a href="{{ route('bidresponsiveness.edit', $bidResponsiveness) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('bidresponsiveness.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Bidder Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Bidder</h6>
                                <p class="fw-bold">{{ $bidResponsiveness->tenderSupplier->supplier->thirdParty->ThirdPartyName ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Tender Reference</h6>
                                <p class="fw-bold">{{ $bidResponsiveness->tenderSupplier->tender->TenderNo ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Overall Status -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted">Overall Status</h6>
                                @if($bidResponsiveness->IsResponsive)
                                    <span class="badge bg-success fs-6">✅ Responsive</span>
                                @else
                                    <span class="badge bg-danger fs-6">❌ Non-Responsive</span>
                                @endif
                            </div>
                        </div>

                        <!-- Responsiveness Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-3">Responsiveness Criteria</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-left-{{ $bidResponsiveness->SubmittedTimely ? 'success' : 'danger' }}">
                                            <div class="card-body text-center">
                                                <h6>Timely Submission</h6>
                                                <span class="badge bg-{{ $bidResponsiveness->SubmittedTimely ? 'success' : 'danger' }}">
                                                    {{ $bidResponsiveness->SubmittedTimely ? '✅ Yes' : '❌ No' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-left-{{ $bidResponsiveness->HasMandatoryDocuments ? 'success' : 'danger' }}">
                                            <div class="card-body text-center">
                                                <h6>Mandatory Documents</h6>
                                                <span class="badge bg-{{ $bidResponsiveness->HasMandatoryDocuments ? 'success' : 'danger' }}">
                                                    {{ $bidResponsiveness->HasMandatoryDocuments ? '✅ Complete' : '❌ Missing' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-left-{{ $bidResponsiveness->IsEligible ? 'success' : 'danger' }}">
                                            <div class="card-body text-center">
                                                <h6>Eligibility Criteria</h6>
                                                <span class="badge bg-{{ $bidResponsiveness->IsEligible ? 'success' : 'danger' }}">
                                                    {{ $bidResponsiveness->IsEligible ? '✅ Eligible' : '❌ Not Eligible' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        @if($bidResponsiveness->Remarks)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted">Remarks</h6>
                                    <div class="alert alert-info">
                                        {{ $bidResponsiveness->Remarks }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Audit Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Created</h6>
                                <p>{{ $bidResponsiveness->CreatedOn ? $bidResponsiveness->CreatedOn->format('M d, Y - h:i A') : 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Last Modified</h6>
                                <p>{{ $bidResponsiveness->ModifiedOn ? $bidResponsiveness->ModifiedOn->format('M d, Y - h:i A') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
.border-left-success {
    border-left: 4px solid #28a745 !important;
}
.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}
</style>

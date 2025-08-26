@extends('layouts.app')

@section('title', 'Referral Details')

@section('content')
<div class="container mt-4">
    <div class="card shadow border-0 rounded-4">
        <div class="card-body p-4">

            {{-- Client Info --}}
            <h5 class="mb-3">Client Information</h5>
            <hr class="text-muted">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-muted">Client Name</label>
                    <input type="text" class="form-control bg-light" value="{{ $referral->ClientName }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted">ID Number</label>
                    <input type="text" class="form-control bg-light" value="{{ $referral->ClientIDNumber }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted">Phone</label>
                    <input type="text" class="form-control bg-light" value="{{ $referral->ClientPhone }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted">Email</label>
                    <input type="text" class="form-control bg-light" value="{{ $referral->ClientEmail }}" readonly>
                </div>
            </div>

            {{-- Referral Info --}}
            <h5 class="mb-3">Referral Information</h5>
            <hr class="text-muted">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-muted">Preferred Insurer</label>
                    <input type="text" class="form-control bg-light" value="{{ $referral->preferredInsurer->Name ?? '-' }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-muted">Insurance Product</label>
                    <input type="text" class="form-control bg-light" value="{{ $referral->insuranceProduct->Name ?? '-' }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted">Referral Date</label>
                    <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($referral->ReferralDate)->format('d/m/Y') }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted">Referred By</label>
                    <input type="text" class="form-control bg-light" value="{{ $referral->referredByEmployee->FirstName }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted">Assigned To</label>
                    <input type="text" class="form-control bg-light" value="{{ $referral->assignedToUser->Name ?? $referral->assignedToUser->name ?? '-' }}" readonly>
                </div>
            </div>

            {{-- Status & Remarks --}}
            <h5 class="mb-3">Status & Remarks</h5>
            <hr class="text-muted">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted">Status</label>
                    <div>
                        <span class="badge rounded bg-{{ $referral->Status->badgeColor() }} px-5 py-2">
                                {{ $referral->Status->label() ?? '-' }}
                            </span>
                    </div>
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-semibold text-muted">Remarks</label>
                    <textarea class="form-control bg-light" rows="3" readonly>{{ $referral->Remarks ?? 'No remarks provided.' }}</textarea>
                </div>
            </div>

            {{-- Actions --}}
            <div class="text-end mt-4">
                <a href="{{ route('bancassurance.referrals.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>
            </div>

        </div>

        {{-- Footer Info --}}
        <div class="card-footer small text-muted bg-light border-0 rounded-bottom-3">
            <div class="d-flex flex-wrap justify-content-between"> 
                <div> <strong>Created By:</strong> {{ $referral->createdByUser->Name }} 
                <span class="ms-3"><strong>Created On:</strong> 
                {{ \Carbon\Carbon::parse($referral->CreatedOn)->format('d/m/Y') }}</span> 
            </div> 
            <div> 
                <strong>Modified By:</strong> {{$referral->modifiedByUser->Name }} 
                <span class="ms-3"><strong>Modified On:</strong> 
                {{ \Carbon\Carbon::parse($referral->ModifiedOn)->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

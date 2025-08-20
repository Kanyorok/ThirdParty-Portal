@extends('layouts.app')

@section('title', 'Referral Details')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            
            {{-- Client Info --}}
            <h5 class="mb-3 text-primary">Client Information</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Client Name</label>
                    <div class="fs-6">{{ $referral->ClientName }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted">ID Number</label>
                    <div class="fs-6">{{ $referral->ClientIDNumber }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted">Phone</label>
                    <div class="fs-6">{{ $referral->ClientPhone }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted">Email</label>
                    <div class="fs-6">{{ $referral->ClientEmail }}</div>
                </div>
            </div>

            <hr>

            {{-- Insurer & Product --}}
            <h5 class="mb-3 text-primary">Insurance Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Preferred Insurer</label>
                    <div class="fs-6">{{ $referral->preferredInsurer->Name ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Insurance Product</label>
                    <div class="fs-6">{{ $referral->insuranceProduct->Name ?? '-' }}</div>
                </div>
            </div>

            <hr>

            {{-- Referral Info --}}
            <h5 class="mb-3 text-primary">Referral Information</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted">Referral Date</label>
                    <div class="fs-6">{{ \Carbon\Carbon::parse($referral->ReferralDate)->format('d/m/Y') }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted">Referred By</label>
                    <div class="fs-6">
                        {{ $referral->referredByEmployee->Name ?? $referral->ReferredBy ?? '-' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted">Assigned To</label>
                    <div class="fs-6">
                        {{ $referral->assignedToUser->Name ?? $referral->assignedToUser->name ?? '-' }}
                    </div>
                </div>
            </div>

            <hr>

            {{-- Status & Remarks --}}
            <h5 class="mb-3 text-primary">Status & Remarks</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted">Status</label>
                    <div>
                        <span class="badge rounded-pill bg-{{ $referral->Status->badgeColor() }} px-3 py-2">
                            {{ $referral->Status->label() ?? '-' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-bold text-muted">Remarks</label>
                    <div class="alert alert-light border rounded mt-1">
                        {{ $referral->Remarks ?? 'No remarks provided.' }}
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <a href="{{ route('bancassurance.referrals.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

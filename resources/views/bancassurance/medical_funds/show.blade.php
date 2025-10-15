@extends('layouts.app')

@section('title', 'Medical Fund Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="fw-bold mb-0">
            Medical Fund: <span class="text-dark">{{ $medicalfund->FundName }}</span>
        </p>
        <div class="d-flex gap-2">
            <a href="{{ route('bancassurance.medicalfunds.edit', $medicalfund->Id) }}" class="btn btn-primary px-4">
                <i class="bi bi-pencil-square me-1"></i> Edit
            </a>
            <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-secondary px-4">
                <i class="bi bi-arrow-left-circle me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Fund Information as Readonly Form --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-light fw-semibold text-secondary rounded-top-4">
                    Fund Information
                </div>
                <div class="card-body">
                    <form>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">Fund Name</label>
                                <input type="text" class="form-control bg-light" value="{{ $medicalfund->FundName }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">Provider</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ optional($medicalfund->provider)->Name ?? '—' }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">Coverage Type</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $medicalfund->coverages->Description ?? '—' }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">Coverage Limit</label>
                                <input type="text" class="form-control bg-light text-end" 
                                       value="{{ number_format((float)($medicalfund->CoverageLimit ?? 0), 2) }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">Active</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $medicalfund->IsActive ? 'Yes' : 'No' }}" readonly>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-muted">Description</label>
                                <textarea class="form-control bg-light" rows="3" readonly>{{ $medicalfund->Description ?? '—' }}</textarea>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-light fw-semibold text-secondary rounded-top-4">
                    Quick Links
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('bancassurance.medicalfunds.beneficiaries.index', $medicalfund->Id) }}" 
                           class="btn btn-outline-primary rounded-3">
                            <i class="bi bi-people me-1"></i> Manage Beneficiaries
                        </a>
                        <a href="{{ route('bancassurance.medicalfunds.contributions.index', $medicalfund->Id) }}" 
                           class="btn btn-outline-success rounded-3">
                            <i class="bi bi-wallet2 me-1"></i> View Contributions
                        </a>
                        <a href="{{ route('bancassurance.medicalfunds.disbursements.index', $medicalfund->Id) }}" 
                           class="btn btn-outline-secondary rounded-3">
                            <i class="bi bi-cash-stack me-1"></i> View Disbursements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

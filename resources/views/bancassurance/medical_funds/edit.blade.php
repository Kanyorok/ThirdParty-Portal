@extends('layouts.app')

@section('title', 'Edit Medical Fund')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong><i class="bi bi-exclamation-triangle me-2"></i>Validation Errors:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Edit Form --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-light fw-semibold text-secondary rounded-top-4">
            <i class="bi bi-pencil-square me-1"></i> Fund Information
        </div>

        <div class="card-body">
            <form action="{{ route('bancassurance.medicalfunds.update', $medicalfund->Id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    {{-- Fund Name --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fund Name <span class="text-danger">*</span></label>
                        <input type="text" name="FundName" class="form-control shadow-sm" 
                               value="{{ old('FundName', $medicalfund->FundName) }}" required>
                    </div>

                    {{-- Provider --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Provider <span class="text-danger">*</span></label>
                        <select name="ProviderId" class="form-select shadow-sm" required>
                            <option value="">-- Select Provider --</option>
                            @foreach($providers as $p)
                                <option value="{{ $p->Id }}" 
                                    @selected(old('ProviderId', $medicalfund->ProviderId) == $p->Id)>
                                    {{ $p->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Coverage Type --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Coverage Type</label>
                        <select name="CoverageType" class="form-select shadow-sm">
                            <option value="">-- Select Coverage Type --</option>
                            @foreach ($coverageTypes as $ct)
                                <option value="{{ $ct->ID }}" 
                                    @selected(old('CoverageType', $medicalfund->CoverageType) == $ct->ID)>
                                    {{ $ct->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Coverage Limit --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Coverage Limit (KSh)</label>
                        <input type="number" step="0.01" name="CoverageLimit" class="form-control shadow-sm text-end" 
                               value="{{ old('CoverageLimit', $medicalfund->CoverageLimit) }}">
                    </div>

                    {{-- Active Checkbox --}}
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check ms-2">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="isActive" 
                                   {{ old('IsActive', $medicalfund->IsActive) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActive">
                                <i class="bi bi-check2-circle text-success me-1"></i> Active
                            </label>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="Description" class="form-control shadow-sm" rows="3" 
                                  placeholder="Brief description...">{{ old('Description', $medicalfund->Description) }}</textarea>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Update Fund
                    </button>
                    <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick Access Cards --}}
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-3 text-primary mb-2"></i>
                    <h6 class="fw-semibold">Beneficiaries</h6>
                    <p class="text-muted small">Manage fund beneficiaries.</p>
                    <a href="{{ route('bancassurance.medicalfunds.beneficiaries.index', $medicalfund->Id) }}" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-wallet2 fs-3 text-success mb-2"></i>
                    <h6 class="fw-semibold">Contributions</h6>
                    <p class="text-muted small">Record & review contributions.</p>
                    <a href="{{ route('bancassurance.medicalfunds.contributions.index', $medicalfund->Id) }}" 
                       class="btn btn-outline-success btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cash-stack fs-3 text-secondary mb-2"></i>
                    <h6 class="fw-semibold">Disbursements</h6>
                    <p class="text-muted small">Authorize & track disbursements.</p>
                    <a href="{{ route('bancassurance.medicalfunds.disbursements.index', $medicalfund->Id) }}" 
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

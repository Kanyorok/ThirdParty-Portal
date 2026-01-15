@extends('layouts.app')
@section('title', 'Initiate Claim')

@section('content')

{{-- ================= STYLES ================= --}}
<style>
    .section-title {
        color: #000;
        font-weight: 600;
        font-size: .9rem;
        padding-bottom: .35rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
</style>

<div class="container mt-4" style="max-width: 800px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary border-bottom">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-file-earmark-text me-2"></i>Initiate
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST"
                  action="{{ route('bancassurance.claims.store') }}"
                  enctype="multipart/form-data">
                @csrf

                {{-- ================= POLICY & TYPE ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Policy Information</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Policy Number <span class="text-danger">*</span>
                            </label>
                            <select name="PolicyId"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Policy --</option>
                                @foreach($policies as $policy)
                                    <option value="{{ $policy->Id }}">
                                        {{ $policy->PolicyNumber }} - {{ $policy->product->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Claim Type <span class="text-danger">*</span>
                            </label>
                            <select name="ClaimType"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Type --</option>
                                @foreach($claimtypes as $claim)
                                    <option value="{{ $claim->ID }}">
                                        {{ $claim->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Date of Claim <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="ClaimDate"
                                   class="form-control form-control-sm"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= CLAIM DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Claim Details</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Currency <span class="text-danger">*</span>
                            </label>
                            <select name="CurrencyId"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Currency --</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->Id }}">
                                        {{ $currency->Code ?? 'Co'}} - {{ $currency->SymbolNative ?? 'Sn' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Claim Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   name="ClaimAmount"
                                   class="form-control form-control-sm text-end"
                                   placeholder="0.00"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= SUPPORTING DOCUMENTS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Supporting Documents</h6>

                    <div class="col-md-8">
                        <label class="form-label small ">
                            Upload File <span class="text-danger">*</span>
                        </label>
                        <small class="text-muted d-block mb-1">
                            Allowed: PDF, JPG, PNG, DOCX, XLSX · Max 25MB
                        </small>
                        <input type="file"
                               name="file[]"
                               class="form-control form-control-sm"
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                               required>
                    </div>
                </div>

                {{-- ================= CLAIM REASON ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Claim Reason</h6>

                    <textarea name="ClaimReason"
                              class="form-control form-control-sm"
                              rows="3"
                              placeholder="Provide a brief explanation for this claim..."
                              required></textarea>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('bancassurance.claims.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Back
                    </a>
                    <button type="submit"
                            class="btn btn-sm btn-primary px-4">
                        <i class="bi bi-send-check me-1"></i> Submit Claim
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

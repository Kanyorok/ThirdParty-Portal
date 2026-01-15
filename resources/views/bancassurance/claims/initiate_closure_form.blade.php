@extends('layouts.app')
@section('title', 'Initiate Claim Closure')

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

<div class="container mt-4" style="max-width: 850px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary border-bottom rounded-top-4">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-lock-fill me-2"></i>
                Initiate Claim Closure
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show small" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('bancassurance.claims.storeClosureFromList') }}">
                @csrf

                {{-- ================= CLAIM SELECTION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Claim Selection</h6>

                    <label class="form-label small ">
                        Select Claim <span class="text-danger">*</span>
                    </label>
                    <select name="ClaimId"
                            class="form-select form-select-sm"
                            required>
                        <option value="">-- Choose Claim --</option>
                        @foreach($claims as $claim)
                            <option value="{{ $claim->ClaimId }}">
                                {{ $claim->claim->policy->PolicyNumber ?? '-' }}
                                – {{ $claim->claim->policy->customer->ThirdParty->ThirdPartyName ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ================= CLOSURE DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Closure Details</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Closure Status <span class="text-danger">*</span>
                            </label>
                            <select name="FinalStatus"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Status --</option>
                                @foreach ($status as $case)
                                    <option value="{{ $case->value }}">
                                        {{ $case->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Closure Date <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="ClosureDate"
                                   class="form-control form-control-sm"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= REMARKS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Remarks</h6>

                    <textarea name="FinalRemarks"
                              class="form-control form-control-sm"
                              rows="3"
                              placeholder="Provide closure details or remarks..."
                              required></textarea>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('bancassurance.claims.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-sm btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i>
                        Close Claim
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

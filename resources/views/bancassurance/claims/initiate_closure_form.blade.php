@extends('layouts.app')
@section('title', 'Initiate Claim Closure')

@section('content')
<div class="container mt-5" style="max-width: 850px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary rounded-top-4 d-flex justify-content-between align-items-center">
            <p class="mb-0 fw-bold"><b>Initiate Claim Closure</b></p>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('bancassurance.claims.storeClosureFromList') }}">
                @csrf

                {{-- Claim Selection --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Select Claim to Close <span class="text-danger">*</span></label>
                    <select name="ClaimId" class="form-select" required>
                        <option value="">-- Choose Claim --</option>
                        @foreach($claims as $claim)
                            <option value="{{ $claim->ClaimId }}">
                                {{ $claim->claim->policy->PolicyNumber ?? '-' }} – {{ $claim->claim->policy->customer->ThirdParty->ThirdPartyName ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Closure Info --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Closure Status <span class="text-danger">*</span></label>
                        <select name="FinalStatus" class="form-select" required>
                            <option value="">-- Select Status --</option>
                            @foreach ($status as $case)
                                <option value="{{ $case->value }}">{{ $case->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Closure Date <span class="text-danger">*</span></label>
                        <input type="date" name="ClosureDate" class="form-control" required>
                    </div>
                </div>

                {{-- Remarks --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Remarks <span class="text-danger">*</span></label>
                    <textarea name="FinalRemarks" class="form-control" rows="3" placeholder="Provide closure details or remarks..."></textarea>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i> Close Claim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

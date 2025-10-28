@extends('layouts.app')
@section('title', 'Underwriting Feedback')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-check me-2"></i> Underwriting Feedback – Policy #{{ $policy->PolicyNumber }}
            </h5>
        </div>

        <div class="card-body">
            <div class="mb-4">
                <h6 class="fw-semibold text-secondary mb-1">Policy Details</h6>
                <p class="mb-0">
                    <strong>Customer:</strong> {{ $policy->customer->thirdParty->ThirdPartyName ?? '-' }} <br>
                    <strong>Insurer:</strong> {{ $policy->insurer->Name ?? '-' }}
                </p>
            </div>

            <form method="POST" action="{{ route('bancassurance.policies.feedback.store', $policy->Id) }}">
                @csrf

                {{-- Feedback Date --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Feedback Date <span class="text-danger">*</span></label>
                    <input type="date" 
                           name="FeedbackDate" 
                           class="form-control @error('FeedbackDate') is-invalid @enderror"
                           value="{{ old('FeedbackDate', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                    @error('FeedbackDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Decision --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Decision <span class="text-danger">*</span></label>
                    <select name="Decision" class="form-select @error('Decision') is-invalid @enderror" required>
                        <option value="">-- Select Decision --</option>
                        @foreach ($decisions as $decision)
                            <option value="{{ $decision->ID }}" {{ old('Decision') == $decision->ID ? 'selected' : '' }}>
                                {{ $decision->Description }}
                            </option>
                        @endforeach
                    </select>
                    @error('Decision')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Risk Score --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Risk Score (%) <span class="text-danger">*</span></label>
                    <input type="number" 
                           name="RiskScore" 
                           class="form-control @error('RiskScore') is-invalid @enderror"
                           min="0" max="100" 
                           value="{{ old('RiskScore') }}" 
                           required>
                    @error('RiskScore')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Comments --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Comments </label>
                    <textarea name="Comments" 
                              class="form-control @error('Comments') is-invalid @enderror" 
                              rows="4"
                              placeholder="Enter detailed feedback here...">{{ old('Comments') }}</textarea>
                    @error('Comments')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" 
                            class="btn btn-success px-4"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        <i class="bi bi-send-check me-1"></i> Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

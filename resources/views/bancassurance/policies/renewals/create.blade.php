@extends('layouts.app')
@section('title', 'Initiate Policy Renewal')

@section('content')
<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-arrow-clockwise me-2"></i> Initiate Policy Renewal
            </h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('bancassurance.policies.storeRenewal', $policy->Id) }}">
                @csrf
                <input type="hidden" name="PolicyID" value="{{ $policy->Id }}">

                {{-- Policy Info --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Policy Number</label>
                        <input type="text" class="form-control" value="{{ $policy->PolicyNumber }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Current End Date</label>
                        <input type="text" class="form-control"
                            value="{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}" readonly>
                    </div>
                </div>

                {{-- Renewal Dates --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Renewal Date <span class="text-danger">*</span></label>
                        <input type="date" name="RenewalDate"
                            class="form-control @error('RenewalDate') is-invalid @enderror"
                            value="{{ old('RenewalDate') }}" required>
                        @error('RenewalDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">New Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="NewStartDate"
                            class="form-control @error('NewStartDate') is-invalid @enderror"
                            value="{{ old('NewStartDate') }}" required>
                        @error('NewStartDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">New End Date <span class="text-danger">*</span></label>
                        <input type="date" name="NewEndDate"
                            class="form-control @error('NewEndDate') is-invalid @enderror"
                            value="{{ old('NewEndDate') }}" required>
                        @error('NewEndDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Notes / Comments --}}
                <div class="mb-3">
                    <label class="form-label">Notes / Comments</label>
                    <textarea name="Notes" rows="2"
                        class="form-control @error('Notes') is-invalid @enderror"
                        placeholder="Optional comments...">{{ old('Notes') }}</textarea>
                    @error('Notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i> Confirm Renewal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
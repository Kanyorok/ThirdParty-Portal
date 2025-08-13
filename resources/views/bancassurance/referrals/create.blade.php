@extends('layouts.app')
@section('title', 'New Insurance Referral')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📋 New Insurance Referral</h4>

    <form method="POST" action="{{ route('bancassurance.referrals.store') }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                <input type="text" name="ClientName" class="form-control" value="{{ old('ClientName') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">ID Number <span class="text-danger">*</span></label>
                <input type="text" name="ClientIDNumber" class="form-control" value="{{ old('ClientIDNumber') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="text" name="ClientPhone" class="form-control" value="{{ old('ClientPhone') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="ClientEmail" class="form-control" value="{{ old('ClientEmail') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Insurance Product <span class="text-danger">*</span></label>
                <select name="InsuranceProductId" class="form-select" required>
                    <option value="">-- Select Product --</option>
                    @foreach ($insuranceproducts as $product)
                        <option value="{{ $product->ID }}" {{ old('InsuranceProductId') == $product->ID ? 'selected' : '' }}>
                            {{ $product->Description }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Preferred Insurer <span class="text-danger">*</span></label>
                <select name="PreferredInsurerId" class="form-select" required>
                    <option value="">-- Select Insurer --</option>
                    @foreach ($insurers as $insurer)
                        <option value="{{ $insurer->ID }}" {{ old('PreferredInsurerId') == $insurer->ID ? 'selected' : '' }}>
                            {{ $insurer->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Referral Date</label>
                <input type="date" name="ReferralDate" class="form-control" value="{{ old('ReferralDate', now()->toDateString()) }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Referred By</label>
                <select name="ReferredBy" class="form-select">
                    <option value="">-- Select User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->Id }}" {{ old('ReferredBy') == $user->Id ? 'selected' : '' }}>
                            {{ $user->Name }}{{ $user->employee ? ' - ' . $user->employee->FullName : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Assign To</label>
                <select name="AssignedTo" class="form-select">
                    <option value="">-- Optional Assignment --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->Id }}" {{ old('AssignedTo') == $user->Id ? 'selected' : '' }}>
                            {{ $user->employee->FirstName ?? $user->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Remarks</label>
                <input type="text" name="Remarks" class="form-control" value="{{ old('Remarks') }}">
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit Referral
            </button>
        </div>
    </form>
</div>
@endsection

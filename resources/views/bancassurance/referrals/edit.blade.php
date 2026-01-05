@extends('layouts.app')

@section('title', 'Insurance Referral')

@section('content')
<div class="container my-4" style="max-width: 900px;">

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('bancassurance.referrals.update', $referral->Id) }}">
        @csrf
        @method('PUT')

        <div class="card shadow overflow-hidden">

            {{-- Header --}}
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-normal">
                    <i class="bi bi-pencil-square me-2"></i>
                    Edit Insurance Referral
                </h5>
            </div>

            {{-- Body --}}
            <div class="card-body p-4">

                {{-- Client --}}
                <section class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted">
                                Client <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control form-control-sm"
                                value="{{ $referral->customerreferral->thirdParty->ThirdPartyName }}"
                                readonly>
                            <input type="hidden" name="ClientId" value="{{ $referral->ClientId }}">
                        </div>
                    </div>
                </section>


                {{-- Insurance Details --}}
                <section class="mb-4">
                    <div class="row g-3">

                        {{-- Insurer --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted">
                                Preferred Insurer <span class="text-danger">*</span>
                            </label>
                            <select id="insurer-select"
                                    name="PreferredInsurerId"
                                    class="form-select form-select-sm" required>
                                <option value="">-- Select Insurer --</option>
                                @foreach ($insurers as $insurer)
                                    <option value="{{ $insurer->Id }}"
                                        {{ old('PreferredInsurerId', $referral->PreferredInsurerId) == $insurer->Id ? 'selected' : '' }}>
                                        {{ $insurer->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Product --}}
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted">
                                Insurance Product <span class="text-danger">*</span>
                            </label>
                            <select id="product-select"
                                    name="InsuranceProductId"
                                    class="form-select form-select-sm" required>
                                <option value="">-- Select Product --</option>
                            </select>
                        </div>
                    </div>
                </section>

                {{-- Metadata --}}
                <section class="mb-4">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted">Referral Date</label>
                            <input type="date"
                                   name="ReferralDate"
                                   class="form-control form-control-sm"
                                   value="{{ old('ReferralDate', \Carbon\Carbon::parse($referral->ReferralDate)->toDateString()) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted">
                                Referred By <span class="text-danger">*</span>
                            </label>
                            <select name="ReferredBy" class="form-select form-select-sm" required>
                                <option value="">-- Select User --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->Id }}"
                                        {{ old('ReferredBy', $referral->ReferredBy) == $user->Id ? 'selected' : '' }}>
                                        {{ $user->Name }}
                                        {{ $user->employee ? ' - ' . $user->employee->FullName : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted">Assign To</label>
                            <select name="AssignedTo" class="form-select form-select-sm">
                                <option value="">-- Optional Assignment --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->Id }}"
                                        {{ old('AssignedTo', $referral->AssignedTo) == $user->Id ? 'selected' : '' }}>
                                        {{ $user->employee->FirstName ?? $user->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                {{-- Remarks --}}
                <section>
                    <label class="form-label fw-medium text-muted">Remarks</label>
                    <textarea name="Remarks"
                              class="form-control form-control-sm rounded-3"
                              rows="3"
                              placeholder="Additional notes...">{{ old('Remarks', $referral->Remarks) }}</textarea>
                </section>

            </div>

            {{-- Footer --}}
            <div class="card-footer d-flex justify-content-between align-items-center bg-light py-3 px-4">
                <a href="{{ route('bancassurance.referrals.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-left-circle me-2"></i> Back
                </a>

                <button type="submit"
                        class="btn btn-primary px-5"
                        onclick="this.disabled=true;this.innerText='Updating...';this.form.submit();">
                    <i class="bi bi-save2 me-2"></i> Update Referral
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const insurerSelect = document.getElementById('insurer-select');
    const productSelect = document.getElementById('product-select');

    const productsRoute = @json(
        route('bancassurance.referrals.referrals.products', ['insurerId' => 'INSURER_ID'])
    );

    const oldProductId = @json(old('InsuranceProductId', $referral->InsuranceProductId));

    insurerSelect.addEventListener('change', function () {
        const insurerId = this.value;
        productSelect.innerHTML = '<option value="">Loading...</option>';

        if (!insurerId) {
            productSelect.innerHTML = '<option value="">-- Select Product --</option>';
            return;
        }

        fetch(productsRoute.replace('INSURER_ID', insurerId))
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">-- Select Product --</option>';
                data.forEach(p => {
                    const selected = oldProductId == p.Id ? 'selected' : '';
                    options += `<option value="${p.Id}" ${selected}>${p.Name}</option>`;
                });
                productSelect.innerHTML = options;
            });
    });

    // Load products on page load
    if (insurerSelect.value) {
        insurerSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
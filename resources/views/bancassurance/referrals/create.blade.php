@extends('layouts.app')

@section('title', 'Insurance Referral')

@section('content')
<div class="container my-4" style="max-width: 900px;">
    <form method="POST" action="{{ route('bancassurance.referrals.store') }}">
        @csrf

        <div class="card shadow border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-normal">
                    <i class="bi bi-shield-check me-2"></i> Create Insurance Referral
                </h5>
            </div>

            <div class="card-body p-4">
                <section class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="ClientName" class="form-control form-control-sm rounded-pill"
                                placeholder="Enter full name" value="{{ old('ClientName') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium text-muted">ID Number <span class="text-danger">*</span></label>
                            <input type="text" name="ClientIDNumber" class="form-control form-control-sm rounded-pill"
                                placeholder="e.g. 12345678" value="{{ old('ClientIDNumber') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium text-muted">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="ClientPhone" class="form-control form-control-sm rounded-pill"
                                placeholder="e.g. 07XXXXXXXX" value="{{ old('ClientPhone') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted">Email <span class="text-danger">*</span></label>
                            <input type="email" name="ClientEmail" class="form-control form-control-sm rounded-pill"
                                placeholder="example@email.com" value="{{ old('ClientEmail') }}" required>
                        </div>
                    </div>
                </section>

                <section class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted">Preferred Insurer <span class="text-danger">*</span></label>
                            <select id="insurer-select" name="PreferredInsurerId"
                                class="form-select form-select-sm rounded-pill" required>
                                <option value="">-- Select Insurer --</option>
                                @foreach ($insurers as $insurer)
                                    <option value="{{ $insurer->Id }}" {{ old('PreferredInsurerId') == $insurer->Id ? 'selected' : '' }}>
                                        {{ $insurer->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted">Insurance Product <span class="text-danger">*</span></label>
                            <select id="product-select" name="InsuranceProductId"
                                class="form-select form-select-sm rounded-pill" required>
                                <option value="">-- Select Product --</option>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted">Referral Date</label>
                            <input type="date" name="ReferralDate" class="form-control form-control-sm rounded-pill"
                                value="{{ old('ReferralDate', now()->toDateString()) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted">Referred By</label>
                            <select name="ReferredBy" class="form-select form-select-sm rounded-pill">
                                <option value="">-- Select User --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->Id }}" {{ old('ReferredBy') == $user->Id ? 'selected' : '' }}>
                                        {{ $user->Name }} {{ $user->employee ? ' - ' . $user->employee->FullName : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted">Assign To</label>
                            <select name="AssignedTo" class="form-select form-select-sm rounded-pill">
                                <option value="">-- Optional Assignment --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->Id }}" {{ old('AssignedTo') == $user->Id ? 'selected' : '' }}>
                                        {{ $user->employee->FirstName ?? $user->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                <section>
                    <label class="form-label fw-medium text-muted">Remarks</label>
                    <textarea name="Remarks" class="form-control form-control-sm rounded-3" rows="3"
                        placeholder="Additional notes or context...">{{ old('Remarks') }}</textarea>
                </section>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center bg-light py-3 px-4">
                <a href="{{ route('bancassurance.referrals.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left-circle me-2"></i> Back
                </a>
                <button type="submit" class="btn btn-primary rounded-pill px-5"
                    onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                    <i class="bi bi-send-check me-2"></i> Submit Referral
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const insurerSelect = document.getElementById('insurer-select');
    const productSelect = document.getElementById('product-select');
    const productsRoute = @json(route('bancassurance.referrals.referrals.products', ['insurerId' => 'INSURER_ID']));

    insurerSelect.addEventListener('change', function() {
        const insurerId = this.value;
        productSelect.innerHTML = '<option value="">Loading...</option>';
        if (insurerId) {
            fetch(productsRoute.replace('INSURER_ID', insurerId))
                .then(res => res.json())
                .then(data => {
                    let options = '<option value="">-- Select Product --</option>';
                    data.forEach(p => options += `<option value="${p.Id}">${p.Name}</option>`);
                    productSelect.innerHTML = options;
                });
        } else {
            productSelect.innerHTML = '<option value="">-- Select Product --</option>';
        }
    });

    @if(old('PreferredInsurerId'))
        insurerSelect.value = "{{ old('PreferredInsurerId') }}";
        insurerSelect.dispatchEvent(new Event('change'));
    @endif
});
</script>
@endpush
@endsection

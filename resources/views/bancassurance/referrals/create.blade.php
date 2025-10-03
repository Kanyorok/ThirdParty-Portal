@extends('layouts.app')
@section('title', 'New Insurance Referral')

@section('content')
<div class="container mt-4">
    <form method="POST" action="{{ route('bancassurance.referrals.store') }}">
        @csrf
        <div class="card shadow-sm">
            <div class="card-body">
                {{-- Client Info --}}
                <h6 class="mb-3 text-secondary">Client Information</h6>
                <hr class="text-muted">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Client Name <span class="text-danger">*</span></label>
                        <input type="text" name="ClientName" class="form-control" 
                               value="{{ old('ClientName') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ID Number <span class="text-danger">*</span></label>
                        <input type="text" name="ClientIDNumber" class="form-control" 
                               value="{{ old('ClientIDNumber') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="ClientPhone" class="form-control" 
                               value="{{ old('ClientPhone') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="ClientEmail" class="form-control" 
                               value="{{ old('ClientEmail') }}" required>
                    </div>
                </div>

                {{-- Insurance Details --}}
                <h6 class="mb-3 text-secondary">Insurance Details</h6>
                <hr class="text-muted">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Preferred Insurer <span class="text-danger">*</span></label>
                        <select id="insurer-select" name="PreferredInsurerId" class="form-select" required>
                            <option value="">-- Select Insurer --</option>
                            @foreach ($insurers as $insurer)
                                <option value="{{ $insurer->Id }}" 
                                    {{ old('PreferredInsurerId') == $insurer->Id ? 'selected' : '' }}>
                                    {{ $insurer->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Insurance Product <span class="text-danger">*</span></label>
                        <select id="product-select" name="InsuranceProductId" class="form-select" required>
                            <option value="">-- Select Product --</option>
                        </select>
                    </div>
                </div>

                {{-- Referral & Assignment --}}
                <h6 class="mb-3 text-secondary">Referral & Assignment</h6>
                <hr class="text-muted">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Referral Date</label>
                        <input type="date" name="ReferralDate" class="form-control" 
                               value="{{ old('ReferralDate', now()->toDateString()) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Referred By</label>
                        <select name="ReferredBy" class="form-select">
                            <option value="">-- Select User --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->Id }}" 
                                    {{ old('ReferredBy') == $user->Id ? 'selected' : '' }}>
                                    {{ $user->Name }} 
                                    {{ $user->employee ? ' - ' . $user->employee->FullName : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Assign To</label>
                        <select name="AssignedTo" class="form-select">
                            <option value="">-- Optional Assignment --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->Id }}" 
                                    {{ old('AssignedTo') == $user->Id ? 'selected' : '' }}>
                                    {{ $user->employee->FirstName ?? $user->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Remarks --}}
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="Remarks" class="form-control" rows="3">{{ old('Remarks') }}</textarea>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary px-4" 
                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                    <i class="fas fa-paper-plane"></i> Submit Referral
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
                .then(r => r.json())
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

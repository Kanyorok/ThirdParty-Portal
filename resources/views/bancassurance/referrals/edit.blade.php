@extends('layouts.app')
@section('title', 'Edit Insurance Referral')

@section('content')
<div class="container mt-4">
    <form method="POST" action="{{ route('bancassurance.referrals.update',$referral->Id) }}">
        @csrf
        @method('PUT')

            {{-- <input type="hidden" name="Id" value="{{ $referral->Id }}"> --}}

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Client Name <span class="text-danger">*</span></label>
                    <input type="text" name="ClientName" class="form-control"
                           value="{{ old('ClientName', $referral->ClientName) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ID Number <span class="text-danger">*</span></label>
                    <input type="text" name="ClientIDNumber" class="form-control"
                           value="{{ old('ClientIDNumber', $referral->ClientIDNumber) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="ClientPhone" class="form-control"
                           value="{{ old('ClientPhone', $referral->ClientPhone) }}" required>
                </div>
            </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="ClientEmail" class="form-control" value="{{ old('ClientEmail', $referral->ClientEmail) }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Preferred Insurer <span class="text-danger">*</span></label>
                <select name="PreferredInsurerId" class="form-select" required>
                    <option value="">-- Select Insurer --</option>
                    @foreach ($insurers as $insurer)
                        <option value="{{ $insurer->Id }}" {{ old('PreferredInsurerId', $referral->PreferredInsurerId) == $insurer->Id ? 'selected' : '' }}>
                            {{ $insurer->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Insurance Product <span class="text-danger">*</span></label>
                <select name="InsuranceProductId" class="form-select" required>
                    <option value="">-- Select Product --</option>
                    @foreach ($insuranceproducts as $product)
                        <option value="{{ $product->Id }}" {{ old('InsuranceProductId', $referral->InsuranceProductId) == $product->Id ? 'selected' : '' }}>
                            {{ $product->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Referral Date <span class="text-danger">*</span></label>
                <input type="date" name="ReferralDate" class="form-control" value="{{ old('ReferralDate', \Carbon\Carbon::parse($referral->ReferralDate)->toDateString()) }}">
            </div>
        </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Referred By</label>
                    <select name="ReferredBy" class="form-select">
                        <option value="">-- Select User --</option>
                        @foreach ($users as $user)
                            <option
                                value="{{ $user->Id }}" {{ old('ReferredBy', $referral->ReferredBy) == $user->Id ? 'selected' : '' }}>
                                {{ $user->Name }}{{ $user->employee ? ' - ' . $user->employee->FullName : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Assigned To</label>
                    <select name="AssignedTo" class="form-select">
                        <option value="">-- Optional Assignment --</option>
                        @foreach ($users as $user)
                            <option
                                value="{{ $user->Id }}" {{ old('AssignedTo', $referral->AssignedTo) == $user->Id ? 'selected' : '' }}>
                                {{ $user->employee->FirstName ?? $user->Name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="Remarks" class="form-control">{{ old('Remarks', $referral->Remarks) }}</textarea>
        </div>

        <div class="text-end">
            <a href="{{ route('bancassurance.referrals.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <button type="submit" class="btn btn-success"> Update Referral</button>
        </div>
    </form>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    const insurerSelect = document.querySelector('select[name="PreferredInsurerId"]');
    const productSelect = document.querySelector('select[name="InsuranceProductId"]');
    const currentProductId = "{{ old('InsuranceProductId', $referral->InsuranceProductId) }}";
    // Use Laravel route helper for dynamic URL
    const productsRoute = @json(route('bancassurance.referrals.referrals.products', ['insurerId' => 'INSURER_ID']));

    insurerSelect.addEventListener('change', function () {
        const insurerId = this.value;
        if (!insurerId) {
            productSelect.innerHTML = '<option value="">-- Select Product --</option>';
            return;
        }
        const url = productsRoute.replace('INSURER_ID', insurerId);
        fetch(url)
            .then(response => response.json())
            .then(products => {
                let options = '<option value="">-- Select Product --</option>';
                products.forEach(product => {
                    const selected = product.Id == currentProductId ? 'selected' : '';
                    options += `<option value="${product.Id}" ${selected}>${product.Name}</option>`;
                });
                productSelect.innerHTML = options;
            });
    });

    // Optionally trigger change on page load if insurer is already selected
    if (insurerSelect.value) {
        insurerSelect.dispatchEvent(new Event('change'));
    }
});
</script>

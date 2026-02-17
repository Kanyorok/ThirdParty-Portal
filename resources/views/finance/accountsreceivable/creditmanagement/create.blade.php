@extends('layouts.app')
@section('title','New Credit Profile')

@section('content')
<div class="container my-3">
    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-muted">
                <i class="fas fa-plus-circle text-info me-2"></i> Create Credit Profile
            </h6>
        </div>

        <div class="card-body p-3">
            <form action="{{ route('creditmanagement.store') }}" method="POST">
                @csrf
                <p class="text-muted">Fill in all required fields before submitting.</p>
                    <div class="col-md-12">
                        <label for="CustomerID" class="form-label">Customer</label>
                        <select class="form-select" name="CustomerID" id="CustomerID" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->Id }}"
                                        data-idnumber="{{ $customer->RegistrationNumber }}"
                                        data-email="{{ $customer->Email }}">
                                    {{ $customer->ThirdPartyName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                <div class="row mb-3">
                    <div class="col-md-4 mt-3">
                        <label for="IDNumber" class="form-label">ID Number</label>
                        <input type="text" id="IDNumber" name="IDRegistrationNo" class="form-control" placeholder="Enter ID/Registration number" readonly>
                    </div>
                    <div class="col-md-4 mt-3">
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" name="EmailAddress" id="Email" class="form-control" placeholder="Enter email address" readonly>
                    </div>
                    <div class="col-md-4 mt-3">
                        <label for="CreditLimit" class="form-label">Credit Limit</label>
                        <input type="number" step="0.01" name="CreditLimit" id="CreditLimit" class="form-control" placeholder="Enter credit limit" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="PaymentTerms" class="form-label">Payment Terms</label>
                        <select class="form-select" name="PaymentTerms" id="PaymentTerms" required>
                            <option value="">-- Select Terms --</option>
                            @foreach($paymentTerms as $term)
                                <option value="{{ $term->Value }}">{{ $term->Description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="EffectiveFrom" class="form-label">Effective From</label>
                        <input type="date" name="EffectiveFrom" id="EffectiveFrom" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label for="ExpiryDate" class="form-label">Expiry/Next Review</label>
                        <input type="date" name="ExpiryDate" id="ExpiryDate" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="Colleteral" class="form-label">Collateral</label>
                    <input type="text" name="Colleteral" id="Colleteral" class="form-control" placeholder="Enter collateral details">
                </div>

                <div class="mb-3">
                    <label for="Remarks" class="form-label">Remarks</label>
                    <textarea name="Remarks" id="Remarks" class="form-control" rows="3" placeholder="Enter remarks"></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('creditmanagement.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-sm btn-info" onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='Saving...'; this.form.submit();}">
                        <i class="fas fa-save me-1"></i> Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customerSelect = document.getElementById('CustomerID');
        const idNumberInput = document.getElementById('IDNumber');
        const emailInput = document.getElementById('Email');
        customerSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
        id: selectedOption.getAttribute('data-idnumber'),
        email: selectedOption.getAttribute('data-email')
});

            idNumberInput.value = selectedOption.getAttribute('data-idnumber') || '';
            emailInput.value = selectedOption.getAttribute('data-email') || '';
        });
    });

</script>
@endsection

@section('styles')
<style>
    :root { --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif; }
    body, .card, .table, input, select, textarea { font-family: var(--font-sans); }
</style>
@endsection

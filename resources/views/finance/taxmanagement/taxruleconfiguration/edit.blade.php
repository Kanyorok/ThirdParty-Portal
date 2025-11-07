@extends('layouts.app')
@section('title', 'Edit Tax Rule')

@section('content')
    <div class="container mt-3">
        <div class="card shadow-sm rounded-4 border-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info">
                    <i class="fas fa-edit me-2"></i> Edit Tax Rule
                </h5>
            </div>

            <div class="card-body">
                <p class="text-muted mb-4">
                    Update the tax rule details below. Fields marked with <span class="text-danger">*</span> are
                    required.
                </p>

                <form method="POST" action="{{ route('taxruleconfig.update', $taxRule->Id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Tax Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tax Type <span class="text-danger">*</span></label>
                            <select name="TaxTypeId" class="form-select shadow-sm">
                                <option disabled value="">Select Tax Type</option>
                                @foreach ($taxTypes as $item)
                                    <option
                                        value="{{ $item->Id }}" {{ $taxRule->TaxTypeId == $item->Id ? 'selected' : '' }}>
                                        {{ $item->TaxTypeName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Jurisdiction -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jurisdiction <span
                                    class="text-danger">*</span></label>
                            <select name="JurisdictionId" class="form-select shadow-sm">
                                <option disabled value="">Select Jurisdiction</option>
                                @foreach ($jurisdictions as $jurisdiction)
                                    <option
                                        value="{{ $jurisdiction->Id }}" {{ $taxRule->JurisdictionId == $jurisdiction->Id ? 'selected' : '' }}>
                                        {{ $jurisdiction->JurisdictionName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Rate -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rate (%)</label>
                            <input type="number" step="0.01" name="Rate" class="form-control shadow-sm"
                                   value="{{ old('Rate', $taxRule->Rate) }}" placeholder="e.g., 16.00">
                        </div>

                        <!-- Applies To -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Applies To</label>
                            <select name="AppliesTo" class="form-select shadow-sm">
                                <option value="Sales" {{ $taxRule->AppliesTo == 'Sales' ? 'selected' : '' }}>Sales
                                </option>
                                <option value="Purchases" {{ $taxRule->AppliesTo == 'Purchases' ? 'selected' : '' }}>
                                    Purchases
                                </option>
                                <option value="Both" {{ $taxRule->AppliesTo == 'Both' ? 'selected' : '' }}>Both</option>
                            </select>
                        </div>

                        <!-- Threshold -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Threshold Amount</label>
                            <input type="number" step="0.01" name="ThresholdAmount" class="form-control shadow-sm"
                                   value="{{ old('ThresholdAmount', $taxRule->ThresholdAmount) }}"
                                   placeholder="e.g., 10000.00">
                        </div>

                        <!-- Apply Tax Per -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Apply Tax Per</label>
                            <select name="ApplyTaxPer" class="form-select shadow-sm">
                                <option value="Invoice" {{ $taxRule->ApplyTaxPer == 'Invoice' ? 'selected' : '' }}>Per
                                    Invoice
                                </option>
                                <option value="LineItem" {{ $taxRule->ApplyTaxPer == 'LineItem' ? 'selected' : '' }}>Per
                                    Line Item
                                </option>
                            </select>
                        </div>

                        <!-- Effective Dates -->
                        <div class="col-md-6">
                            <label for="EffectiveFrom" class="form-label fw-semibold">Effective From</label>
                            <input type="date" name="EffectiveFrom" id="EffectiveFrom" class="form-control shadow-sm"
                                   value="{{ old('EffectiveFrom', $taxRule->EffectiveFrom ?? '') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="EffectiveTo" class="form-label fw-semibold">Effective To</label>
                            <input type="date" name="EffectiveTo" id="EffectiveTo" class="form-control shadow-sm"
                                   value="{{ old('EffectiveTo', $taxRule->EffectiveTo ?? '') }}">
                        </div>

                        <div id="dateError"
                             class="alert alert-danger d-none alert-dismissible fade show py-2 px-3 small">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Effective To date cannot be earlier than Effective From date.
                        </div>

                        <!-- GL Accounts -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tax Payable GL</label>
                            <select name="TaxPayableGLID" class="form-select shadow-sm">
                                <option disabled value="">Select GL Account</option>
                                @foreach ($glAccounts as $account)
                                    <option
                                        value="{{ $account->Id }}" {{ $taxRule->TaxPayableGLID == $account->Id ? 'selected' : '' }}>
                                        {{ $account->GLName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tax Receivable GL</label>
                            <select name="TaxReceivableGLID" class="form-select shadow-sm">
                                <option disabled value="">Select GL Account</option>
                                @foreach ($glAccounts as $account)
                                    <option
                                        value="{{ $account->Id }}" {{ $taxRule->TaxReceivableGLID == $account->Id ? 'selected' : '' }}>
                                        {{ $account->GLName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-12">
                            <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="Status" value="1" {{ $taxRule->Status ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">Active</label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('taxruleconfig.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success px-4"
                                onclick="if(this.form.checkValidity()){this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin me-2\'></i> Updating...'; this.form.submit();}">
                            <i class="fas fa-save me-1"></i> Update Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const from = document.getElementById('EffectiveFrom');
            const to = document.getElementById('EffectiveTo');
            const errorDiv = document.getElementById('dateError');

            to.addEventListener('change', function () {
                if (new Date(to.value) < new Date(from.value)) {
                    errorDiv.classList.remove('d-none');
                    to.value = '';
                } else {
                    errorDiv.classList.add('d-none');
                }
            });
        });
</script>
@endsection

@extends('layouts.app')
@section('title', 'Tax Rule Setup')

@section('content')
<div class="container">
    <div class="card p-2 shadow rounded-4 mb-0">
        <div class="card-body mb-0">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <p class="text-muted">
                Use this form to set up a new tax rule. Ensure all fields are filled out correctly to avoid issues with tax calculations.
            </p>

            <form method="POST" action="{{ route('taxruleconfig.store') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tax Type</label>
                        <select name="TaxTypeId" class="form-select" required>
                            <option disabled selected value="">Select Tax Type</option>
                            @foreach ($taxTypes as $item)
                                <option value="{{ $item->Id }}">{{ $item->TaxTypeName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jurisdiction</label>
                        <select name="JurisdictionId" class="form-select" required>
                            <option disabled selected value="">Select Jurisdiction</option>
                            @foreach ($jurisdictions as $jurisdiction)
                                <option value="{{ $jurisdiction->Id }}">{{ $jurisdiction->JurisdictionName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Rate (%)</label>
                        <input type="number" step="0.01" name="Rate" class="form-control" value="0.00" placeholder="e.g., 16.00" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Applies To</label>
                        <select name="AppliesTo" class="form-select" required>
                            <option value="Sales">Sales</option>
                            <option value="Purchases">Purchases</option>
                            <option value="Both">Both</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Threshold Amount</label>
                        <input type="number" step="0.01" name="ThresholdAmount" class="form-control" placeholder="e.g., 10000.00">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Apply Tax Per</label>
                        <select name="ApplyTaxPer" class="form-select" required>
                            <option value="Invoice">Per Invoice</option>
                            <option value="LineItem">Per Line Item</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Effective From</label>
                        <input type="date" id="EffectiveFrom" name="EffectiveFrom" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Effective To</label>
                        <input type="date" id="EffectiveTo" name="EffectiveTo" class="form-control">
                    </div>
                </div>

                <div id="dateError" class="alert alert-danger d-none" role="alert">
                    Effective To date cannot be earlier than Effective From date.
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tax Payable GL</label>
                        <select name="TaxPayableGLID" class="form-select" required>
                            <option disabled selected value="">Select GL Account</option>
                            @foreach ($glAccounts as $account)
                                <option value="{{ $account->Id }}">{{ $account->GLName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tax Receivable GL</label>
                        <select name="TaxReceivableGLID" class="form-select" required>
                            <option disabled selected value="">Select GL Account</option>
                            @foreach ($glAccounts as $account)
                                <option value="{{ $account->Id }}">{{ $account->GLName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('taxruleconfig.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-long-arrow-alt-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-info"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">
                        <i class="fas fa-save"></i> Save Rule
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
            if (from.value && new Date(to.value) < new Date(from.value)) {
                errorDiv.classList.remove('d-none');
                to.value = '';
            } else {
                errorDiv.classList.add('d-none');
            }
        });
    });
</script>
@endsection

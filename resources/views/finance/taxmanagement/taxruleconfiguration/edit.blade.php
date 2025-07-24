@extends('layouts.app')
@section('title', 'Edit Tax Rule')

@section('content')
    <div class="container mt-1">
        <div class="card p-2">
{{--            <div class="card-header bg-dark text-white">--}}
{{--                ✏️ Edit Tax Rule--}}
{{--            </div>--}}
            <div class="card-body">
                <p class="text-muted">
                    Update the tax rule details below.
                </p>

                <form method="POST" action="{{ route('taxruleconfig.update', $taxRule->Id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Type</label>
                            <select name="TaxTypeId" class="form-select">
                                <option disabled value="">Select Tax Type</option>
                                @foreach ($taxTypes as $item)
                                    <option value="{{ $item->Id }}" {{ $taxRule->TaxTypeId == $item->Id ? 'selected' : '' }}>
                                        {{ $item->TaxTypeName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jurisdiction</label>
                            <select name="JurisdictionId" class="form-select">
                                <option disabled value="">Select Jurisdiction</option>
                                @foreach ($jurisdictions as $jurisdiction)
                                    <option value="{{ $jurisdiction->Id }}" {{ $taxRule->JurisdictionId == $jurisdiction->Id ? 'selected' : '' }}>
                                        {{ $jurisdiction->JurisdictionName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rate (%)</label>
                            <input type="number" step="0.01" name="Rate" class="form-control"
                                value="{{ old('Rate', $taxRule->Rate) }}" placeholder="e.g., 16.00">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Applies To</label>
                            <select name="AppliesTo" class="form-select">
                                <option value="Sales" {{ $taxRule->AppliesTo == 'Sales' ? 'selected' : '' }}>Sales</option>
                                <option value="Purchases" {{ $taxRule->AppliesTo == 'Purchases' ? 'selected' : '' }}>Purchases</option>
                                <option value="Both" {{ $taxRule->AppliesTo == 'Both' ? 'selected' : '' }}>Both</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Threshold Amount</label>
                            <input type="number" step="0.01" name="ThresholdAmount" class="form-control"
                                value="{{ old('ThresholdAmount', $taxRule->ThresholdAmount) }}" placeholder="e.g., 10000.00">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apply Tax Per</label>
                            <select name="ApplyTaxPer" class="form-select">
                                <option value="Invoice" {{ $taxRule->ApplyTaxPer == 'Invoice' ? 'selected' : '' }}>Per Invoice</option>
                                <option value="LineItem" {{ $taxRule->ApplyTaxPer == 'LineItem' ? 'selected' : '' }}>Per Line Item</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="EffectiveFrom">Effective From</label>
                            <input type="date" name="EffectiveFrom" id="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom', $taxRule->EffectiveFrom ?? '') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="EffectiveTo">Effective To</label>
                            <input type="date" name="EffectiveTo" id="EffectiveTo" class="form-control" value="{{ old('EffectiveTo', $taxRule->EffectiveTo ?? '') }}">
                        </div>

                        <div id="dateError" class="alert alert-danger d-none alert-dismissible fade show alert-sm">
                            Effective To date cannot be earlier than Effective From date.
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Payable GL</label>
                            <select name="TaxPayableGLID" class="form-select">
                                <option disabled value="">Select GL Account</option>
                                @foreach ($glAccounts as $account)
                                    <option value="{{ $account->Id }}" {{ $taxRule->TaxPayableGLID == $account->Id ? 'selected' : '' }}>
                                        {{ $account->GLName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Receivable GL</label>
                            <select name="TaxReceivableGLID" class="form-select">
                                <option disabled value="">Select GL Account</option>
                                @foreach ($glAccounts as $account)
                                    <option value="{{ $account->Id }}" {{ $taxRule->TaxReceivableGLID == $account->Id ? 'selected' : '' }}>
                                        {{ $account->GLName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="Status" value="1" {{ $taxRule->Status ? 'checked' : '' }}>
                            <label class="form-check-label">Status</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-content-centre mt-4">
                        <a href="{{ route('taxruleconfig.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='🔄 Updating...'; this.form.submit();}">🔄  Update Rule</button>
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

@extends('layouts.app')
@section('title', 'Tax Rule Setup')

@section('content')
    <div class="container mt-2">
        <div class="card p-2">
{{--            <div class="card-header bg-dark text-white">--}}
{{--                ⚙️ Configure Tax Rule--}}
{{--            </div>--}}
            <div class="card-body">
                <p class="text-muted">
                    Use this form to set up a new tax rule. Ensure all fields are filled out correctly to avoid issues with tax calculations.
                </p>

                <form method="POST" action="{{ route('taxruleconfig.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Type</label>
                            <select name="TaxTypeId" class="form-select">
                            <option disabled selected value="">Select Tax Type</option>
                                @foreach ($taxTypes as $item)
                                    <option value="{{ $item->Id }}">{{ $item->TaxTypeName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jurisdiction</label>
                            <select name="JurisdictionId" class="form-select">
                                <option disabled selected value="">Select Jurisdiction</option>
                                @foreach ($jurisdictions as $jurisdiction)
                                    <option value="{{ $jurisdiction->Id }}">{{ $jurisdiction->JurisdictionName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rate (%)</label>
                            <input type="number" step="0.01" name="Rate" class="form-control" value="0.00" placeholder="e.g., 16.00">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Applies To</label>
                            <select name="AppliesTo" class="form-select">
                                <option value="Sales">Sales</option>
                                <option value="Purchases">Purchases</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Threshold Amount</label>
                            <input type="number" step="0.01" name="ThresholdAmount" class="form-control"
                                placeholder="e.g., 10000.00">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apply Tax Per</label>
                            <select name="ApplyTaxPer" class="form-select">
                                <option value="Invoice">Per Invoice</option>
                                <option value="LineItem">Per Line Item</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="EffectiveFrom">Effective From</label>
                            <input type="date" id="EffectiveFrom" name="EffectiveFrom" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="EffectiveTo">Effective To</label>
                            <input type="date" id="EffectiveTo" name="EffectiveTo" class="form-control">
                        </div>

                        <div id="dateError" class="alert alert-danger d-none" role="alert">
                            Effective To date cannot be earlier than Effective From date.
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Payable GL</label>
                            <select name="TaxPayableGLID" class="form-select">
                            <option disabled selected value="">Select GL Account</option>
                                @foreach ($glAccounts as $account)
                                    <option value="{{ $account->Id }}">{{ $account->GLName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Receivable GL</label>
                            <select name="TaxReceivableGLID" class="form-select">
                                <option disabled selected value="">Select GL Account</option>
                                @foreach ($glAccounts as $account)
                                    <option value="{{ $account->Id }}">{{ $account->GLName }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="IsActive" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div> --}}
                    </div>

                    <div class="d-flex justify-content-between align-content-centre mt-4">
                        <a href="{{ route('taxruleconfig.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success" onclick="if(this.checkValidity()){this.disabled=true; this.innerText='💾Saving...'; this.form.submit();}">💾 Save Rule</button>
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

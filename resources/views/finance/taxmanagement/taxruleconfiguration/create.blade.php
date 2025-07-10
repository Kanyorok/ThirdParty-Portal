@extends('layouts.app')
@section('title', 'Tax Rule Setup')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">⚙️ Configure Tax Rule</h4>

        <form method="POST" action="#">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tax Type</label>
                    <select name="TaxTypeID" class="form-select">
                        <option value="1">VAT</option>
                        <option value="2">Withholding Tax</option>
                        <option value="3">GST</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Jurisdiction</label>
                    <select name="JurisdictionID" class="form-select">
                        <option value="1">Kenya</option>
                        <option value="2">Uganda</option>
                        <option value="3">Tanzania</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Rate (%)</label>
                    <input type="number" step="0.01" name="Rate" class="form-control" placeholder="e.g., 16.00">
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
                    <select name="ApplicationLevel" class="form-select">
                        <option value="Invoice">Per Invoice</option>
                        <option value="LineItem">Per Line Item</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Effective From</label>
                    <input type="date" name="EffectiveFrom" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Effective To</label>
                    <input type="date" name="EffectiveTo" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tax Payable GL</label>
                    <select name="TaxPayableGLID" class="form-select">
                        <option value="1010">WHT Payable - 1010</option>
                        <option value="1020">VAT Payable - 1020</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tax Receivable GL</label>
                    <select name="TaxReceivableGLID" class="form-select">
                        <option value="2010">VAT Input - 2010</option>
                        <option value="2020">GST Input - 2020</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="IsActive" value="1" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">💾 Save Rule</button>
                <a href="/finance/taxrules" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

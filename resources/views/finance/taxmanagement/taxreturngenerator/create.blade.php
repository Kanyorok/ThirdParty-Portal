@extends('layouts.app')
@section('title', 'Generate Tax Return')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">🧾 Generate Tax Return</h4>

        <form method="POST" action="#">
            @csrf
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Tax Type</label>
                    <select name="TaxType" class="form-select">
                        <option value="VAT">VAT</option>
                        <option value="WHT">WHT</option>
                        <option value="GST">GST</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filing Period</label>
                    <input type="month" name="Period" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jurisdiction</label>
                    <select name="JurisdictionID" class="form-select">
                        <option value="1">Kenya</option>
                        <option value="2">Uganda</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Include Transactions?</label>
                <select name="IncludeDetails" class="form-select">
                    <option value="1">Yes – Include Detailed Line Items</option>
                    <option value="0">No – Summary Only</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Generate Return</button>
            <a href="{{ route('taxreturngenerator.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection

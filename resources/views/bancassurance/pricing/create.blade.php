@extends('layouts.app')
@section('title', 'Add Pricing Rule')

@section('content')
<div class="container mt-4">
    <h4>➕ Add Pricing Rule</h4>

    <form method="POST" action="{{ route('bancassurance.pricing.store') }}">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="ProviderProductID" class="form-label">Insurance Provider Product</label>
                <select name="ProviderProductID" id="ProviderProductID" class="form-select" required>
                    <option value="">-- Select Provider Product --</option>
                    @foreach($providerProducts as $mapping)
                        <option value="{{ $mapping->Id }}">
                            {{ $mapping->ProviderName }} – {{ $mapping->ProductName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="MinCoverageAmount" class="form-label">Min Coverage (KES)</label>
                <input type="number" name="MinCoverageAmount" class="form-control" required step="0.01">
            </div>

            <div class="col-md-3 mb-3">
                <label for="MaxCoverageAmount" class="form-label">Max Coverage (KES)</label>
                <input type="number" name="MaxCoverageAmount" class="form-control" required step="0.01">
            </div>

            <div class="col-md-3 mb-3">
                <label for="MinAge" class="form-label">Min Age</label>
                <input type="number" name="MinAge" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="MaxAge" class="form-label">Max Age</label>
                <input type="number" name="MaxAge" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="MinTenureMonths" class="form-label">Min Tenure (Months)</label>
                <input type="number" name="MinTenureMonths" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="MaxTenureMonths" class="form-label">Max Tenure (Months)</label>
                <input type="number" name="MaxTenureMonths" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="PremiumRate" class="form-label">Premium Rate (%)</label>
                <input type="number" name="PremiumRate" class="form-control" step="0.01" required>
            </div>

            <div class="col-12 mb-3">
                <label for="Remarks" class="form-label">Remarks</label>
                <textarea name="Remarks" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">💾 Save Pricing Rule</button>
        </div>
    </form>
</div>
@endsection

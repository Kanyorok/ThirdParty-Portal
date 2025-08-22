@extends('layouts.app')
@section('title', 'Edit Pricing Rule')

@section('content')
    <div class="container mt-4">
        <h4>✏️ Edit Pricing Rule</h4>

        <form method="POST" action="{{ route('bancassurance.pricing.update', $rule->Id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Provider Product</label>
                <select name="ProviderProductID" class="form-select" required>
                    <option value="">-- Select Product --</option>
                    @foreach($providerProducts as $pp)
                        <option value="{{ $pp->Id }}" {{ $pp->Id == $rule->ProviderProductID ? 'selected' : '' }}>
                            {{ $pp->CustomName ?? 'Product ' . $pp->Id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Min Age</label>
                    <input type="number" name="MinAge" class="form-control" value="{{ $rule->MinAge }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Max Age</label>
                    <input type="number" name="MaxAge" class="form-control" value="{{ $rule->MaxAge }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Min Coverage</label>
                    <input type="number" name="MinCoverage" class="form-control" step="0.01"
                           value="{{ $rule->MinCoverage }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Max Coverage</label>
                    <input type="number" name="MaxCoverage" class="form-control" step="0.01"
                           value="{{ $rule->MaxCoverage }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Min Tenure (Years)</label>
                    <input type="number" name="MinTenure" class="form-control" value="{{ $rule->MinTenure }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Max Tenure (Years)</label>
                    <input type="number" name="MaxTenure" class="form-control" value="{{ $rule->MaxTenure }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Premium Rate (%)</label>
                <input type="number" name="PremiumRate" class="form-control" step="0.01"
                       value="{{ $rule->PremiumRate }}" required>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success">💾 Update Rule</button>
                <a href="{{ route('bancassurance.pricing.index') }}" class="btn btn-secondary">🔙 Cancel</a>
            </div>
        </form>
    </div>
@endsection

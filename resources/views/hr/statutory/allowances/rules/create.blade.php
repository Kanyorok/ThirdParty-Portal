@extends('layouts.app')

@section('title', 'New Allowance Rule')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Create Allowance Rule</h2>
            <div class="text-muted">Allowance: {{ $allowance->Name }} ({{ $allowance->Code }})</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('hr.statutory.allowances.rules.index', $allowance->Id) }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.statutory.allowances.rules.store', $allowance->Id) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Calculation Method *</label>
                        <select name="CalcMethod" class="form-select" required>
                            @foreach(['PercentageOnBasic','PercentageOnGross','Flat','PercentageOnBand','FlatOnBand'] as $method)
                                <option value="{{ $method }}" @selected(old('CalcMethod')==$method)>{{ $method }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Most allowances are a percentage of basic pay; choose PercentageOnBasic for that.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rate (%)</label>
                        <input type="number" step="0.01" name="Rate" class="form-control" value="{{ old('Rate') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Band From</label>
                        <input type="number" step="0.01" name="IncomeFrom" class="form-control" value="{{ old('IncomeFrom', 0) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Band To</label>
                        <input type="number" step="0.01" name="IncomeTo" class="form-control" value="{{ old('IncomeTo') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Amount</label>
                        <input type="number" step="0.01" name="MinAmount" class="form-control" value="{{ old('MinAmount') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maximum Amount</label>
                        <input type="number" step="0.01" name="MaxAmount" class="form-control" value="{{ old('MaxAmount') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Formula (optional)</label>
                        <textarea name="FormulaText" rows="3" class="form-control" placeholder="Custom expression if needed">{{ old('FormulaText') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective From *</label>
                        <input type="date" name="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="EffectiveTo" class="form-control" value="{{ old('EffectiveTo') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description') }}">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

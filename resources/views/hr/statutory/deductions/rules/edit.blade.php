@extends('layouts.app')

@section('title', 'Edit Deduction Rule')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Edit Rule</h2>
            <div class="text-muted">Deduction: {{ $deduction->Name }} ({{ $deduction->Code }})</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('hr.statutory.deductions.rules.index', $deduction->Id) }}">Back</a>
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
            <form method="POST" action="{{ route('hr.statutory.deductions.rules.update', [$deduction->Id, $rule->Id]) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Calculation Method *</label>
                        <select name="CalcMethod" class="form-select" required>
                            @foreach(['PercentageOnGross','Flat','PercentageOnBand','FlatOnBand'] as $method)
                                <option value="{{ $method }}" @selected(old('CalcMethod', $rule->CalcMethod)==$method)>{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rate (%)</label>
                        <input type="number" step="0.01" name="Rate" class="form-control" value="{{ old('Rate', $rule->Rate) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount', $rule->Amount) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Band From</label>
                        <input type="number" step="0.01" name="IncomeFrom" class="form-control" value="{{ old('IncomeFrom', $rule->IncomeFrom) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Band To</label>
                        <input type="number" step="0.01" name="IncomeTo" class="form-control" value="{{ old('IncomeTo', $rule->IncomeTo) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Amount</label>
                        <input type="number" step="0.01" name="MinAmount" class="form-control" value="{{ old('MinAmount', $rule->MinAmount) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maximum Amount</label>
                        <input type="number" step="0.01" name="MaxAmount" class="form-control" value="{{ old('MaxAmount', $rule->MaxAmount) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="HasRelief" value="1" id="HasRelief" @checked(old('HasRelief', $rule->HasRelief))>
                            <label for="HasRelief" class="form-check-label">Has Relief</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Relief Type</label>
                        <select name="ReliefType" class="form-select">
                            <option value="">None</option>
                            <option value="Percentage" @selected(old('ReliefType', $rule->ReliefType)=='Percentage')>Percentage</option>
                            <option value="Fixed" @selected(old('ReliefType', $rule->ReliefType)=='Fixed')>Fixed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Relief Rate (%)</label>
                        <input type="number" step="0.01" name="ReliefRate" class="form-control" value="{{ old('ReliefRate', $rule->ReliefRate) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Relief Amount</label>
                        <input type="number" step="0.01" name="ReliefAmount" class="form-control" value="{{ old('ReliefAmount', $rule->ReliefAmount) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Formula (optional)</label>
                        <textarea name="FormulaText" rows="3" class="form-control" placeholder="Custom expression if needed">{{ old('FormulaText', $rule->FormulaText) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective From *</label>
                        <input type="date" name="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom', $rule->EffectiveFrom) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="EffectiveTo" class="form-control" value="{{ old('EffectiveTo', $rule->EffectiveTo) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $rule->IsActive))>
                            <label for="IsActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description', $rule->Description) }}">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

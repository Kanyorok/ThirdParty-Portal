@extends('layouts.app')
@section('title', 'Set Commission Rule')

@section('content')
    <div class="container mt-4">
        <h4>➕ Set New Commission Rule</h4>

        <form method="POST" action="{{ route('commissions.rules.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Policy Type</label>
                <select name="PolicyTypeID" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($policyTypes as $id => $type)
                        <option value="{{ $id }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Insurance Provider</label>
                <select name="InsuranceProviderID" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($providers as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Commission Type</label>
                <select name="CommissionType" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="Flat %">Flat %</option>
                    <option value="Tiered">Tiered</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Commission Value (%)</label>
                <input type="number" step="0.01" name="CommissionValue" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea name="Remarks" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-success">💾 Save Rule</button>
        </form>
    </div>
@endsection

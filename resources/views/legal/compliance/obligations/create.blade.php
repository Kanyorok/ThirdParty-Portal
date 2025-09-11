@extends('layouts.app')
@section('title', 'Add Obligation')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">➕ Add Compliance Obligation</h4>

    <form method="POST" action="{{ route('legal.compliance.obligations.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="Title" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Regulator *</label>
                <select name="RegulatorID" class="form-select" required>
                    <option value="">-- Select Regulator --</option>
                    @foreach($regulators as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Compliance Area *</label>
                <select name="ComplianceAreaID" class="form-select" required>
                    <option value="">-- Select Area --</option>
                    @foreach($areas as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Effective Date</label>
                <input type="date" name="EffectiveDate" class="form-control">
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <button class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.compliance.obligations.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

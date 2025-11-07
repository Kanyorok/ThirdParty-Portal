@extends('layouts.app')
@section('title', 'Edit Obligation')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <h4 class="mb-3">✏️ Edit Compliance Obligation</h4>

        <form method="POST" action="{{ route('legal.compliance.obligations.update', $obligation->Id) }}">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="Title" value="{{ $obligation->Title }}" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Regulator *</label>
                    <select name="RegulatorID" class="form-select" required>
                        @foreach($regulators as $id => $name)
                            <option value="{{ $id }}" {{ $obligation->RegulatorID == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Compliance Area *</label>
                    <select name="ComplianceAreaID" class="form-select" required>
                        @foreach($areas as $id => $name)
                            <option value="{{ $id }}" {{ $obligation->ComplianceAreaID == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Effective Date</label>
                    <input type="date" name="EffectiveDate" value="{{ $obligation->EffectiveDate }}"
                           class="form-control">
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="Description" class="form-control" rows="3">{{ $obligation->Description }}</textarea>
                </div>

                <div class="col-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="IsActive" class="form-check-input"
                               value="1" {{ $obligation->IsActive ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>

            <button class="btn btn-success">💾 Update</button>
            <a href="{{ route('legal.compliance.obligations.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection

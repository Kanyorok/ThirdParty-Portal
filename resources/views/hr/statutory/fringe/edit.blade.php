@extends('layouts.app')

@section('title', 'Edit Fringe Benefit Rule')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Fringe Benefit Rule</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.statutory.fringe.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.statutory.fringe.update', $benefit->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code', $benefit->Code) }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $benefit->Name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rate Type *</label>
                        <select name="RateType" class="form-select" required>
                            <option value="Percentage" @selected(old('RateType', $benefit->RateType)=='Percentage')>Percentage</option>
                            <option value="Fixed" @selected(old('RateType', $benefit->RateType)=='Fixed')>Fixed Amount</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rate *</label>
                        <input type="number" step="0.01" name="Rate" class="form-control" value="{{ old('Rate', $benefit->Rate) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cap Amount</label>
                        <input type="number" step="0.01" name="CapAmount" class="form-control" value="{{ old('CapAmount', $benefit->CapAmount) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective From *</label>
                        <input type="date" name="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom', $benefit->EffectiveFrom) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="EffectiveTo" class="form-control" value="{{ old('EffectiveTo', $benefit->EffectiveTo) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $benefit->IsActive))>
                            <label for="IsActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description', $benefit->Description) }}">
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

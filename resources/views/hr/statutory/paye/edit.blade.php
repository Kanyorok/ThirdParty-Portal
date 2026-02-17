@extends('layouts.app')

@section('title', 'Edit PAYE Band')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit PAYE Band</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.statutory.paye.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.statutory.paye.update', $band->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Lower Limit *</label>
                        <input type="number" step="0.01" name="LowerLimit" class="form-control" value="{{ old('LowerLimit', $band->LowerLimit) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Upper Limit</label>
                        <input type="number" step="0.01" name="UpperLimit" class="form-control" value="{{ old('UpperLimit', $band->UpperLimit) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rate (%) *</label>
                        <input type="number" step="0.01" name="Rate" class="form-control" value="{{ old('Rate', $band->Rate) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fixed Amount</label>
                        <input type="number" step="0.01" name="FixedAmount" class="form-control" value="{{ old('FixedAmount', $band->FixedAmount) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective From *</label>
                        <input type="date" name="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom', $band->EffectiveFrom) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="EffectiveTo" class="form-control" value="{{ old('EffectiveTo', $band->EffectiveTo) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $band->IsActive))>
                            <label for="IsActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description', $band->Description) }}">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update Band</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

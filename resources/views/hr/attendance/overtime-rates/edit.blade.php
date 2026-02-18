@extends('layouts.app')

@section('title', 'Edit Overtime Rate')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Overtime Rate</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.attendance.overtime-rates.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.attendance.overtime-rates.update', $rate->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Job Grade *</label>
                        <select name="GradeID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}" @selected(old('GradeID', $rate->GradeID) == $grade->Id)>{{ $grade->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Overtime Rate Multiplier *</label>
                        <input type="number" step="0.01" min="0" name="RateMultiplier" class="form-control" value="{{ old('RateMultiplier', $rate->RateMultiplier) }}" required>
                        <div class="text-muted small">Applied to the hourly rate (e.g. 1.5 = 150%).</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom', optional($rate->EffectiveFrom)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="EffectiveTo" class="form-control" value="{{ old('EffectiveTo', optional($rate->EffectiveTo)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" @checked(old('IsActive', $rate->IsActive))>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Update Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

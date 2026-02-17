@extends('layouts.app')

@section('title', 'New NHIF / SHIF Rate')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create NHIF / SHIF Rate</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.statutory.nhif.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.statutory.nhif.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee Rate *</label>
                        <input type="number" step="0.01" name="EmployeeRate" class="form-control" value="{{ old('EmployeeRate', 0) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Amount *</label>
                        <input type="number" step="0.01" name="MinAmount" class="form-control" value="{{ old('MinAmount', 300) }}" required>
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
                    <button type="submit" class="btn btn-primary">Save Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

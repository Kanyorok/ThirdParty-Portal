@extends('layouts.app')

@section('title', 'New PAYE Band')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create PAYE Band</h2>
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
            <form method="POST" action="{{ route('hr.statutory.paye.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Lower Limit *</label>
                        <input type="number" step="0.01" name="LowerLimit" class="form-control" value="{{ old('LowerLimit', 0) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Upper Limit</label>
                        <input type="number" step="0.01" name="UpperLimit" class="form-control" value="{{ old('UpperLimit') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rate (%) *</label>
                        <input type="number" step="0.01" name="Rate" class="form-control" value="{{ old('Rate', 0) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fixed Amount</label>
                        <input type="number" step="0.01" name="FixedAmount" class="form-control" value="{{ old('FixedAmount', 0) }}">
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
                    <button type="submit" class="btn btn-primary">Save Band</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

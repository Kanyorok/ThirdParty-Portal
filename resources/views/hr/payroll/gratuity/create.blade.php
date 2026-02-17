@extends('layouts.app')

@section('title', 'Compute Gratuity')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Compute Gratuity</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.gratuity.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.payroll.gratuity.store') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Year *</label>
                    <input type="number" name="Year" class="form-control" value="{{ old('Year', $year) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rate (%) *</label>
                    <input type="number" step="0.01" name="RatePercent" class="form-control" value="{{ old('RatePercent', 5) }}" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <input type="text" name="Notes" class="form-control" value="{{ old('Notes') }}" placeholder="Optional note">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Compute</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

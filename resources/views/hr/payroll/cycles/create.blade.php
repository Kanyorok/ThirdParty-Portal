@extends('layouts.app')

@section('title', 'Open Payroll Cycle')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Open Payroll Cycle</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.cycles.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.payroll.cycles.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Year *</label>
                        <input type="number" name="Year" class="form-control" value="{{ old('Year', now()->year) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Month *</label>
                        <input type="number" name="Month" class="form-control" value="{{ old('Month', now()->month) }}" min="1" max="12" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notes</label>
                        <input type="text" name="Notes" class="form-control" value="{{ old('Notes') }}" placeholder="Optional note">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Open Cycle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

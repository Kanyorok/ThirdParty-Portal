@extends('layouts.app')

@section('title', 'New Perspective Weight')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Perspective Weight</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.kpi.perspective-weights.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.config.kpi.perspective-weights.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Perspective *</label>
                        <select name="PerspectiveID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($perspectives as $perspective)
                                <option value="{{ $perspective->Id }}" @selected(old('PerspectiveID') == $perspective->Id)>{{ $perspective->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Period *</label>
                        <select name="PeriodID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($periods as $period)
                                <option value="{{ $period->Id }}" @selected(old('PeriodID') == $period->Id)>{{ $period->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Weight (0 - 1) *</label>
                        <input type="number" step="0.0001" name="Weight" class="form-control" value="{{ old('Weight') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Grade (optional)</label>
                        <select name="GradeID" class="form-select">
                            <option value="">All Grades</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}" @selected(old('GradeID') == $grade->Id)>{{ $grade->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role (optional)</label>
                        <select name="RoleID" class="form-select">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(old('RoleID') == $role->Id)>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Weight</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

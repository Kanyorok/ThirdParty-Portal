@extends('layouts.app')

@section('title', 'New Weighting Rule')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Weighting Rule</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.kpi.weighting.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.config.kpi.weighting.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Grade</label>
                        <select name="GradeID" class="form-select">
                            <option value="">Any</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}" @selected(old('GradeID') == $grade->Id)>{{ $grade->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role</label>
                        <select name="RoleID" class="form-select">
                            <option value="">Any</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(old('RoleID') == $role->Id)>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Weight *</label>
                        <input type="number" min="0" max="100" name="TotalWeight" class="form-control" value="{{ old('TotalWeight', 100) }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description') }}">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

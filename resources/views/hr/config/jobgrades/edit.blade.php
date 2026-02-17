@extends('layouts.app')

@section('title', 'Edit Job Grade')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Job Grade</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.jobgrades.index') }}">Back</a>
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
            <form action="{{ route('hr.config.jobgrades.update', $grade->Id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code', $grade->Code) }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $grade->Name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Min Salary</label>
                        <input type="number" step="0.01" name="MinSalary" class="form-control" value="{{ old('MinSalary', $grade->MinSalary) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max Salary</label>
                        <input type="number" step="0.01" name="MaxSalary" class="form-control" value="{{ old('MaxSalary', $grade->MaxSalary) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description', $grade->Description) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $grade->IsActive))>
                            <label for="IsActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.config.jobgrades.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

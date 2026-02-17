@extends('layouts.app')

@section('title', 'Edit Rating Scale')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Rating Scale</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.kpi.ratingscales.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.config.kpi.ratingscales.update', $scale->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code', $scale->Code) }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $scale->Name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Min Score *</label>
                        <input type="number" step="0.01" name="MinScore" class="form-control" value="{{ old('MinScore', $scale->MinScore) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max Score *</label>
                        <input type="number" step="0.01" name="MaxScore" class="form-control" value="{{ old('MaxScore', $scale->MaxScore) }}" required>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $scale->IsActive))>
                            <label for="IsActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description', $scale->Description) }}">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update Scale</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

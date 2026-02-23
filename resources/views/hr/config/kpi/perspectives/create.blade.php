@extends('layouts.app')

@section('title', 'New KPI Perspective')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Perspective</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.kpi.perspectives.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.config.kpi.perspectives.store') }}">
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
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="Description" rows="3" class="form-control">{{ old('Description') }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Perspective</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

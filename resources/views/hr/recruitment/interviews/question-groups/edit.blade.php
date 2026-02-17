@extends('layouts.app')

@section('title', 'Edit Interview Question Group')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Question Group</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.interview-question-groups.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.recruitment.interview-question-groups.update', $group->Id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="Name" class="form-control" value="{{ old('Name', $group->Name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="Description" class="form-control" rows="3">{{ old('Description', $group->Description) }}</textarea>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="IsActive" id="IsActive" {{ old('IsActive', $group->IsActive) ? 'checked' : '' }}>
                    <label class="form-check-label" for="IsActive">Active</label>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Update Group</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

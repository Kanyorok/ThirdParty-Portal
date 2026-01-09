@extends('layouts.app')

@section('title', 'New Interview Question')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Question</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.interview-questions.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.recruitment.interview-questions.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Group</label>
                    <select name="GroupID" class="form-select">
                        <option value="">Select</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->Id }}" @selected(old('GroupID') == $group->Id)>{{ $group->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Question *</label>
                    <input type="text" name="Title" class="form-control" value="{{ old('Title') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Guidance</label>
                    <textarea name="Guidance" class="form-control" rows="3">{{ old('Guidance') }}</textarea>
                </div>
                <div class="form-check mb-3">
                    <input type="hidden" name="IsActive" value="0">
                    <input class="form-check-input" type="checkbox" name="IsActive" id="IsActive" value="1" {{ old('IsActive', 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="IsActive">Active</label>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Question</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

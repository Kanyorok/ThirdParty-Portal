@extends('layouts.app')

@section('title', 'New Document Category')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Document Category</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.shared-docs.categories.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.shared-docs.categories.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code') }}">
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="3">{{ old('Description') }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

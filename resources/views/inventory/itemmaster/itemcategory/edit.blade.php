@extends('layouts.app')

@section('title', 'Edit Category')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@section('content')
<div class="container bg-white shadow-sm rounded p-4">
    <h4>Edit Category: {{ $category->Name }}</h4>

    <form action="{{ route('itemcategory.update', $category->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="CategoryCode" class="form-label">Category Code</label>
                <input type="text" name="CategoryCode" value="{{ $category->CategoryCode }}" class="form-control" readonly>
            </div>
            <div class="col-md-4">
                <label for="Name" class="form-label">Category Name</label>
                <input type="text" name="Name" value="{{ $category->Name }}" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="Description" class="form-label">Description</label>
                <input type="text" name="Description" value="{{ $category->Description }}" class="form-control" required>
            </div>

            <div class="col-md-4">
            <label for="ParentId" class="form-label">Parent Category:</label>
            <select class="form-control" name="ParentId">
                <option value="">None (Top-Level Category)</option>
                @foreach($categories as $category)
                    <option value="{{ $category->Id }}">{{ $category->Name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Auto-assign ModifiedBy -->
        <div class="d-flex justify-content-start mt-4">
        <input type="hidden" name="ModifiedBy" value="{{ auth()->id() }}">
        <button type="submit" class="btn btn-primary">✅ Save Changes</button>
        <a href="{{ route('itemcategory.index') }}" class="btn btn-secondary">🔙 Cancel</a>
</div>
    </form>
</div>
@endsection

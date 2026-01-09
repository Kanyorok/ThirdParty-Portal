@extends('layouts.app')
@section('title', 'Edit Property Category')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('propertycategories.update', $category->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="Name" class="form-label">Category Name:<span class="text-danger">*</span></label>
            <input type="text" name="Name" class="form-control" value="{{ old('Name', $category->Name) }}" readonly>
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Description:<span class="text-danger">*</span></label>
            <textarea name="Description" class="form-control" required
                      rows="4">{{ old('Description', $category->Description) }}</textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a href="{{ route('propertycategory.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Update Category</button>
        </div>
    </form>
@endsection

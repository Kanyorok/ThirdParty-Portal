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
            <label for="Name" class="form-label">Category Name:</label>
            <input type="text" name="Name" class="form-control" value="{{ old('Name', $category->Name) }}" required>
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Description (optional):</label>
            <textarea name="Description" class="form-control"
                      rows="4">{{ old('Description', $category->Description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update Category</button>
        <a href="{{ route('propertycategory.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection

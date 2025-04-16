@extends('layouts.app')
@section('title', 'Edit Category')

@section('content')
    <h1>Edit Item Category</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please fix the following issues:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Category Name:</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $category->Name) }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description (optional):</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $category->Description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update Category</button>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection

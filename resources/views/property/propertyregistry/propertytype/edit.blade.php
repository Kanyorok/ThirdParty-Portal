@extends('layouts.app')
@section('title', 'Edit Property Type')
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
    <form action="{{ route('propertytype.update', $type->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="PropertyTypeName" class="form-label">Type Name:</label>
            <input type="text" name="PropertyTypeName" class="form-control"
                   value="{{ old('PropertyTypeName', $type->PropertyTypeName) }}" required>
        </div>

        <div class="mb-3">
            <label for="PropertyCategoryId" class="form-label">Category</label>
            <select name="PropertyCategoryId" class="form-select" required>
                @foreach ($categories as $category)
                    <option
                        value="{{ $category->Id }}" {{ $type->PropertyCategoryId == $category->Id ? 'selected' : '' }}>
                        {{ $category->Name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="Description" class="form-label">Description (optional):</label>
            <textarea name="Description" class="form-control"
                      rows="4">{{ old('Description', $type->Description) }}</textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a href="{{ route('propertytype.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Update Type</button>
        </div>
    </form>
@endsection

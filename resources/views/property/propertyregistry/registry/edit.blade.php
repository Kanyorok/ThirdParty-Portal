@extends('layouts.app')
@section('title', 'Edit Category')
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
    <h1>Edit Item Category</h1>
    <form action="{{ route('PropertyRegistry.update', $property->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="PropertyName" class="form-label">Property Name:</label>
            <input type="text" name="PropertyName" class="form-control" value="{{ old('PropertyName', $property->PropertyName) }}" required>
        </div>

        <div class="mb-3">
            <label for="PropertyType" class="form-label">Type</label>
                <select name="PropertyType" class="form-select" required>
                    @foreach ($types as $type)
                        <option value="{{ $type->Id }}" {{ $property->PropertyType == $type->Id ? 'selected' : '' }}>
                            {{ $type->PropertyTypeName }}
                        </option>
                    @endforeach
                </select>
        </div>

        <div class="mb-3">
            <label for="Category" class="form-label">Category</label>
                <select name="Category" class="form-select" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->Id }}" {{ $property->PropertyCategoryId == $category->Id ? 'selected' : '' }}>
                            {{ $category->Name }}
                        </option>
                    @endforeach
                </select>
        </div>
        <div class="mb-3">
            <label for="TownCity" class="form-label">Town/City</label>
                <select name="TownCity" class="form-select" required>
                    @foreach ($localities as $locality)
                        <option value="{{ $locality->ID }}" {{ $locality->TownCity == $locality->ID ? 'selected' : '' }}>
                            {{ $locality->Name }}
                        </option>
                    @endforeach
                </select>
        <div class="mb-3">
            <label for="PropertyDescription" class="form-label">Description </label>
            <textarea name="PropertyDescription" class="form-control" rows="4">{{ old('PropertyDescription', $property->PropertyDescription) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update Category</button>
        <a href="{{ route('propertytype.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection

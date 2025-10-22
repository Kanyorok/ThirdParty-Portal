@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Edit Supplier Category: {{ $category->CategoryName }}</h1>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Category Detail</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('proc.supplier-cat.update', ['supplier_cat' => $category]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="CategoryName" class="form-label">Category Name</label>
                    <input type="text" name="CategoryName" id="CategoryName" class="form-control @error('CategoryName') is-invalid @enderror" value="{{ old('CategoryName', $category->CategoryName) }}" required>
                    @error('CategoryName')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="Description" class="form-label">Descriptions</label>
                    <textarea name="Description" id="Description" rows="4" class="form-control @error('Description') is-invalid @enderror">{{ old('Description', $category->Description) }}</textarea>
                    @error('Description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="IsActive" id="IsActive" value="1" class="form-check-input" {{ old('IsActive', $category->IsActive) ? 'checked' : '' }}>
                    <label for="IsActive" class="form-check-label">Is Active</label>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('proc.supplier-cat.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
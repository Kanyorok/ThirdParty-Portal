@extends('layouts.app')
@section('title', 'Supplier Classifications')
@section('content')

<div class="container mt-5">

    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">New Classification Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('proc.supplier-cat.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="CategoryName" class="form-label">Category Name</label>
                    <input type="text" name="CategoryName" id="CategoryName"
                        class="form-control @error('CategoryName') is-invalid @enderror"
                        value="{{ old('CategoryName') }}" required>
                    @error('CategoryName')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="item_category_ids" class="form-label">Top-level Item Categories (multi-select)</label>
                    <select id="item_category_ids" name="item_category_ids[]" class="form-control" multiple size="6">
                        @foreach(($itemCategories ?? []) as $cat)
                        <option
                            value="{{ $cat->Id }}" @selected(collect(old('item_category_ids', []))->contains($cat->Id))>{{ $cat->Name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl / Cmd to select multiple. Only parent categories (no ParentId)
                        listed.</small>
                </div>

                <div class="mb-3">
                    <label for="Description" class="form-label">Description</label>
                    <textarea name="Description" id="Description" rows="4"
                        class="form-control @error('Description') is-invalid @enderror">{{ old('Description') }}</textarea>
                    @error('Description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="IsActive" id="IsActive" value="1"
                        class="form-check-input" {{ old('IsActive', true) ? 'checked' : '' }}>
                    <label for="IsActive" class="form-check-label">Is Active</label>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('proc.supplier-cat.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
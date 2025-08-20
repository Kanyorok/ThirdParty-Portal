@extends('layouts.app')
@section('title', isset($codeDetail) ? 'Edit Setting' : 'Add New Setting')

@section('content')
<div class="container mt-4">
    <h4>{{ isset($codeDetail) ? '✏️ Edit Setting' : '➕ Add New Setting' }}</h4>

    <form method="POST" action="{{ isset($codeDetail) ? route('bancassurance.settings.update', $codeDetail->ID) : route('bancassurance.settings.store') }}">
        @csrf
        @if(isset($codeDetail))
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="CodeID" class="form-label">Code Category</label>
                <select name="CodeID" id="CodeID" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    @foreach($codeCategories as $category)
                        <option value="{{ $category }}" {{ (old('CodeID', $codeDetail->CodeID ?? '') == $category) ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="Description" class="form-label">Description</label>
                <input type="text" name="Description" id="Description" class="form-control" required
                       value="{{ old('Description', $codeDetail->Description ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label for="DisplayOrder" class="form-label">Display Order</label>
                <input type="number" name="DisplayOrder" id="DisplayOrder" class="form-control"
                       value="{{ old('DisplayOrder', $codeDetail->DisplayOrder ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label for="IsActive" class="form-label">Status</label>
                <select name="IsActive" id="IsActive" class="form-select">
                    <option value="1" {{ old('IsActive', $codeDetail->IsActive ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('IsActive', $codeDetail->IsActive ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">💾 Save</button>
            <a href="{{ route('bancassurance.settings.index') }}" class="btn btn-secondary">↩️ Back</a>
        </div>
    </form>
</div>
@endsection

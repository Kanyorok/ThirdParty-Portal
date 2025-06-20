@extends('layouts.app')

@section('title', 'Create Budget Line Category')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm rounded-4">
        <div class="card-header bg-dark text-white mb-0">
            ➕ Create Budget Line Category
        </div>
        <div class="card-body">

            {{-- Display validation errors --}}
            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('budgetlinecategories.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="CategoryCode" class="form-label">Category Code <span class="text-danger">*</span></label>
                    <input type="text" name="CategoryCode" id="CategoryCode" class="form-control" value="{{ old('CategoryCode') }}" required maxlength="20">
                </div>

                <div class="mb-3">
                    <label for="CategoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="CategoryName" id="CategoryName" class="form-control" value="{{ old('CategoryName') }}" required maxlength="255">
                </div>

                <div class="mb-3">
                    <label for="Description" class="form-label">Description</label>
                    <textarea name="Description" id="Description" class="form-control" rows="3">{{ old('Description') }}</textarea>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="IsActive" id="IsActive" value="1" {{ old('IsActive', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="IsActive">Active</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('budgetlinecategories.index') }}" class="btn btn-outline-secondary">
                        ⬅ Back
                    </a>
                    <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                        💾 Save Category
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit Sub Category')

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
    <h4>✏️ Edit Sub Category: {{ $item->SubCategoryName }}</h4>

    <form action="{{ route('itemsubcategory.update', $item->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="SubCategoryCode" class="form-label">Sub Category Code</label>
                <input type="text" name="SubCategoryCode" value="{{ $item->SubCategoryCode }}" class="form-control" readonly>
            </div>
            <div class="col-md-4">
                <label for="SubCategoryName" class="form-label">Sub Category Name</label>
                <input type="text" name="SubCategoryName" value="{{ $item->SubCategoryName }}" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
        <div class="col-md-4">
        <label for="categoryID" class="form-label">Parent Category</label>
        <select class="form-select" name="ParentCategory" id="parentCategoryCode" required>
          <option value="">-- Select Category --</option>
          @foreach($categories as $category)
          <option value="{{ $category->id }}">{{ $category->Name }}</option>
          @endforeach
        </select>

            </div>
            <div class="col-md-4">
                <label for="Description" class="form-label">Description</label>
                <input type="text" name="Description" value="{{ $item->Description }}" class="form-control">
            </div>
        </div>

            </div>
            <div class="col-md-4">
                <label for="Status" class="form-label">Status</label>
                <div class="form-check">
                    <input type="hidden" name="Status" value="0"> <!-- Fix checkbox issue -->
                    <input class="form-check-input" type="checkbox" name="Status" id="Status" value="1" {{ $item->Status ? 'checked' : '' }}>
                    <label class="form-check-label" for="Status">Active</label>
                </div>
            </div>
        </div>

        <input type="hidden" name="ModifiedBy" value="{{ auth()->id() }}">

        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">✅ Save Changes</button>
        <a href="{{ route('itemsubcategory.index') }}" class="btn btn-secondary">🔙 Cancel</a>
    </form>
</div>
@endsection

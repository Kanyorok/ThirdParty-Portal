@extends('layouts.app')
@section('title', 'Create Sub Category')
@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-info text-white rounded-top-4">
      <h4 class="mb-0">➕ Add Item Sub Category</h4>
    </div>
    <div class="card-body">

      <form action="{{ route('itemsubcategory.store') }}" method="POST">
          @csrf
        <div class="mb-3">
          <label for="categoryID" class="form-label">Parent Category</label>
          <select class="form-select" name="ParentCategory" required>
            <option value="">-- Select Category --</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}">{{ $category->Name }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label for="subCategoryCode" class="form-label">Sub Category Code</label>
          <input type="text" name= "SubCategoryCode" class="form-control" id="subCategoryCode" placeholder="e.g., SUB-CAT-001" required>
        </div>

        <div class="mb-3">
          <label for="subCategoryName" class="form-label">Sub Category Name</label>
          <input type="text" name= "SubCategoryName" class="form-control" id="subCategoryName" placeholder="e.g., Toners & Ink" required>
        </div>

        <div class="mb-3">
          <label for="subCategoryDescription" class="form-label">Description</label>
          <textarea name= "SubCategoryDescription" class="form-control" id="subCategoryDescription" rows="3" placeholder="Optional description..."></textarea>
        </div>

        <div class="form-check mb-4">
          <input type="hidden" name="Status" value="0">
          <input class="form-check-input" type="checkbox" name="Status" value="1" checked>
          <label class="form-check-label">Is Active</label>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success"
                    onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Save Sub Category
            </button>
        </div>

      </form>

    </div>
  </div>
</div>
@endsection

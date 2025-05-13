@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')

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
            @foreach($items as $item)
              <option value="{{ $item->Id }}">{{ $item->Name }}</option>
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
          <button type="submit" class="btn btn-success px-4">Save Sub Category</button>
        </div>

      </form>

    </div>
  </div>
</div>
@endsection
@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-info text-white rounded-top-4">
      <h4 class="mb-0">➕ Add Item Sub Category</h4>
    </div>
    <div class="card-body">

      <form>

        <div class="mb-3">
          <label for="categoryID" class="form-label">Parent Category</label>
          <select class="form-select" id="categoryID" required>
            <option value="">-- Select Category --</option>
            <!-- Populate dynamically -->
          </select>
        </div>

        <div class="mb-3">
          <label for="subCategoryCode" class="form-label">Sub Category Code</label>
          <input type="text" class="form-control" id="subCategoryCode" placeholder="e.g., SUB-CAT-001" required>
        </div>

        <div class="mb-3">
          <label for="subCategoryName" class="form-label">Sub Category Name</label>
          <input type="text" class="form-control" id="subCategoryName" placeholder="e.g., Toners & Ink" required>
        </div>

        <div class="mb-3">
          <label for="subCategoryDescription" class="form-label">Description</label>
          <textarea class="form-control" id="subCategoryDescription" rows="3" placeholder="Optional description..."></textarea>
        </div>

        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="isActive" checked>
          <label class="form-check-label" for="isActive">Is Active</label>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-success px-4">Save Sub Category</button>
        </div>

      </form>

    </div>
  </div>
</div>
@endsection
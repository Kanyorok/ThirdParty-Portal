@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-secondary text-white rounded-top-4">
      <h4 class="mb-0">➕ Add Item Category</h4>
    </div>
    <div class="card-body">

      <form>

        <div class="mb-3">
          <label for="categoryCode" class="form-label">Category Code</label>
          <input type="text" class="form-control" id="categoryCode" placeholder="e.g., CAT-001" required>
        </div>

        <div class="mb-3">
          <label for="categoryName" class="form-label">Category Name</label>
          <input type="text" class="form-control" id="categoryName" placeholder="e.g., Office Supplies" required>
        </div>

        <div class="mb-3">
          <label for="categoryDescription" class="form-label">Description</label>
          <textarea class="form-control" id="categoryDescription" rows="3" placeholder="Optional description..."></textarea>
        </div>

        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="isActive" checked>
          <label class="form-check-label" for="isActive">Is Active</label>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-success px-4">Save Category</button>
        </div>

      </form>

    </div>
  </div>
</div>
@endsection
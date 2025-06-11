@extends('layouts.app')
@section('title', 'Property Management')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🏷️ Add Property Category</h4>

  <form action="{{ route('propertycategory.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card shadow">
      <div class="card-header bg-light fw-bold">➕ Property Details</div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="PropertyCategoryName" class="form-label">Category Name</label>
            <input type="text" name="Name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label for="Description" class="form-label">Description</label>
            <textarea name="Description" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
            ➕ Add CATEGORY
        </button>
      </div>
  </form>
</div>
  </div>
@endsection
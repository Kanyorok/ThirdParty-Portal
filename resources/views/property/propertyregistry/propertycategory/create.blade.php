@extends('layouts.app')
@section('title', 'Add Property Category')
@section('content')
<div class="container mt-4">
  <form action="{{ route('propertycategory.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card shadow">
      <div class="card-header bg-light fw-bold">➕ Property Details</div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="PropertyCategoryName" class="form-label">Category Name<span class="text-danger">*</span></label>
            <input type="text" name="Name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label for="Description" class="form-label">Description<span class="text-danger">*</span></label>
            <textarea name="Description" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
            ➕ Add CATEGORY
        </button>
          <a href="{{ route('propertycategory.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
  </form>
</div>
  </div>
@endsection

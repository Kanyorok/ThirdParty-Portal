@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🏷️ Add Property Type</h4>

  <form action="{{ route('propertytype.store') }}" method="POST">
    @csrf
    <div class="card shadow">
      <div class="card-header bg-light fw-bold">➕ Property Details</div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="PropertyTypeName" class="form-label">Type Name</label>
            <input type="text" name="PropertyTypeName" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label for="PropertyCategoryId" class="form-label">Category</label>
            <select name="PropertyCategoryId" class="form-select" required>
              @foreach ($categories as $category)
                <option value="{{ $category->PropertyCategoryName }}">{{ $category->PropertyCategoryName }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="Description" class="form-label">Description</label>
            <textarea name="Description" class="form-control" rows="3"></textarea>
          </div>
          <button type="submit" class="btn btn-success">➕ Add Type</button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection

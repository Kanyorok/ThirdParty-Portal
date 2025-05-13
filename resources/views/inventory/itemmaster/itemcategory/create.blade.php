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
    <div class="card-header bg-secondary text-white rounded-top-4">
      <h4 class="mb-0">➕ Add Item Category</h4>
    </div>
    <div class="card-body">

    <form action="{{ route('itemcategory.store') }}" method="POST">
        @csrf

        <!-- Auto-assign CreatedBy -->
        <input type="hidden" name="CreatedBy" value="{{ auth()->id() }}">
        <input type="hidden" name="ModifiedBy" value="{{ auth()->id() }}">

        <div class="mb-3">
          <label for="CategoryCode" class="form-label">Category Code</label>
          <input type="text" name="CategoryCode" id="CategoryCode" class="form-control" placeholder="e.g., CAT-001" required>
        </div>

        <div class="mb-3">
          <label for="Name" class="form-label">Category Name</label>
          <input type="text" name="Name" id="Name" class="form-control" placeholder="e.g., Office Supplies" required>
        </div>

        <div class="mb-3">
          <label for="Description" class="form-label">Description</label>
          <textarea name="Description" id="Description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
        </div>

        <div class="form-check mb-4">
            <input type="hidden" name="Status" value="0"> 
            <input class="form-check-input" type="checkbox" name="Status" id="isActive" value="1" checked>
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

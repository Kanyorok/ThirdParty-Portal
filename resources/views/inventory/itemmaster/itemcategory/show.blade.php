@extends('layouts.app')

@section('title', 'Category Details')

@section('content')

<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-secondary text-white rounded-top-4">
      <h4 class="mb-0">📂 Category Details: {{ $item->Name }}</h4>
    </div>
    <div class="card-body">

      <!-- Category Code & Name -->
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Category Code:</strong>
          <p>{{ $item->CategoryCode }}</p>
        </div>
        <div class="col-md-6">
          <strong>Category Name:</strong>
          <p>{{ $item->Name }}</p>
        </div>
      </div>

      <!-- Description & Status -->
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Description:</strong>
          <p>{{ $item->Description }}</p>
        </div>
        <div class="col-md-6">
          <strong>Status:</strong>
          <span class="badge {{ $item->Status ? 'bg-success' : 'bg-warning' }}">
            {{ $item->Status ? 'Active' : 'Inactive' }}
          </span>
        </div>
      </div>

      <!-- Created By & Modified By -->
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Created By:</strong>
          <p>{{ optional($item->creator)->name ?? 'Unknown' }}</p>
        </div>
        <div class="col-md-6">
          <strong>Modified By:</strong>
          <p>{{ optional($item->modifier)->name ?? 'Unknown' }}</p>
        </div>
      </div>

<div class="d-flex justify-content-start gap-3 mt-4">
    <a href="{{ route('itemcategory.index') }}" class="btn btn-secondary">🔙 Back</a>
    <a href="{{ route('itemcategory.edit', $item->id) }}" class="btn btn-warning">✏️ Edit Category</a>
    
    <form action="{{ route('itemcategory.destroy', $item->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger delete-button">🗑️ Delete Category</button>
    </form>
</div>


    </div>
  </div>
</div>

<script>
document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', function(event) {
        if (!confirm('⚠️ Are you sure you want to delete this category?')) {
            event.preventDefault();
        }
    });
});
</script>

@endsection

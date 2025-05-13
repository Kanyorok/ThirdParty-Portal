@extends('layouts.app')

@section('title', 'Subcategory Details')

@section('content')

<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-secondary text-white rounded-top-4">
      <h4 class="mb-0">📂 Subcategory Details: {{ $item->SubCategoryName }}</h4>
    </div>
    <div class="card-body">

      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Subcategory Code:</strong>
          <p>{{ $item->SubCategoryCode }}</p>
        </div>
        <div class="col-md-6">
          <strong>Subcategory Name:</strong>
          <p>{{ $item->SubCategoryName }}</p>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Parent Category:</strong>
          <p>{{ optional($item->ParentCategory)->Name ?? 'Unknown' }}</p>
        </div>
        <div class="col-md-6">
          <strong>Description:</strong>
          <p>{{ $item->Description }}</p>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Status:</strong>
          <span class="badge {{ $item->Status ? 'bg-success' : 'bg-warning' }}">
            {{ $item->Status ? 'Active' : 'Inactive' }}
          </span>
        </div>
        <div class="col-md-6">
          <strong>Created By:</strong>
          <p>{{ optional($item->creator)->name ?? 'Unknown' }}</p>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Modified By:</strong>
          <p>{{ optional($item->modifier)->name ?? 'Unknown' }}</p>
        </div>
      </div>

      <div class="d-flex justify-content-start gap-3 mt-4">
        <a href="{{ route('itemsubcategory.index') }}" class="btn btn-secondary">🔙 Back</a>
        <a href="{{ route('itemsubcategory.edit', $item->Id) }}" class="btn btn-warning">✏️ Edit Subcategory</a>
        
        <form action="{{ route('itemsubcategory.destroy', $item->Id) }}" method="POST" style="display:inline;">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger delete-button">🗑️ Delete Subcategory</button>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', function(event) {
        if (!confirm('⚠️ Are you sure you want to delete this subcategory?')) {
            event.preventDefault();
        }
    });
});
</script>

@endsection

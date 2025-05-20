@extends('layouts.app')
@section('title', 'drepositorymanagement')
@section('content')
<div class="container mt-5" style="max-width: 800px;">
  <!-- Page Header with link -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Create Document Type</h3>
    <a href="{{ route('dtypessetupmanagement.index') }}" class="btn btn-outline-secondary">← Back to List</a>
  </div>

  <!-- Add Document Type Form -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      ➕ Add Document Type
    </div>
    <div class="card-body">
      <form method="post" action="store.php">
        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input type="text" id="name" name="name" class="form-control" placeholder="e.g., Contract, ID Copy, Invoice" required>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea id="description" name="description" class="form-control" placeholder="Enter a brief description" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Add Type</button>
      </form>
    </div>
  </div>
</div>
@endsection
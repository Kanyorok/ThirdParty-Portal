@extends('layouts.app')
@section('title', 'categoriesmanagement')
@section('content')
<div class="container mt-5" style="max-width: 800px;">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3>Add Folder / Category</h3>
      <p class="text-muted mb-0">📁 Logical organization of documents</p>
    </div>
    <a href="{{ route('categoriesmanagement.index') }}"class="btn btn-success">← Back to List</a>
  </div>

  <!-- Form Card -->
  <div class="card">
    <div class="card-header bg-primary text-white">
      ➕ Add Folder / Category
    </div>
    <div class="card-body">
      <form method="post" action="store.php">
        <!-- Parent Folder Dropdown -->
        <div class="mb-3">
          <label for="parent_id" class="form-label">Parent Folder (optional)</label>
          <select id="parent_id" name="parent_id" class="form-select">
            <option value="">None (Top Level)</option>
            <!-- Populate dynamically in PHP -->
            <option value="1">HR Documents</option>
            <option value="2">Finance</option>
          </select>
        </div>

        <!-- Folder Name -->
        <div class="mb-3">
          <label for="folder_name" class="form-label">Folder Name</label>
          <input type="text" id="folder_name" name="folder_name" class="form-control" placeholder="e.g., Payroll, Legal, Admin" required>
        </div>

        <!-- Visibility -->
        <div class="mb-3">
          <label for="visibility" class="form-label">Visibility</label>
          <select id="visibility" name="visibility" class="form-select">
            <option value="public">Public</option>
            <option value="private">Private</option>
            <option value="restricted">Restricted</option>
          </select>
        </div>

        <button type="submit" class="btn btn-success">Create Folder</button>
      </form>
    </div>
  </div>
</div>
@endsection
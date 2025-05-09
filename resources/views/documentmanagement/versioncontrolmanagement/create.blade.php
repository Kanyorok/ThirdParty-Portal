@extends('layouts.app')
@section('title', 'versioncontrolmanagement')
@section('content')
<div class="container mt-5" style="max-width: 900px;">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3>Document Version Control</h3>
      <p class="text-muted mb-0">📑 Track changes to document versions</p>
    </div>
    <a href="{{ route('versioncontrolmanagement.index') }}"class="btn btn-success">← Back to Version List</a>
  </div>

  <!-- Upload New Version -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      ➕ Upload New Document Version
    </div>
    <div class="card-body">
      <form method="post" action="store_version.php" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="document_id" class="form-label">Select Document</label>
          <select id="document_id" name="document_id" class="form-select" required>
            <option value="">-- Choose Document --</option>
            <!-- Populate dynamically with PHP -->
            <option value="1">Fire Insurance Policy</option>
            <option value="2">Contract Agreement</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="version_file" class="form-label">Upload New Version</label>
          <input type="file" id="version_file" name="version_file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Upload Version</button>
      </form>
    </div>
  </div>

  <!-- Version History Table -->
  <div class="card">
    <div class="card-header bg-secondary text-white">
      📜 Version History
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Document</th>
              <th>Version</th>
              <th>Uploaded At</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Static Sample Data -->
            <tr>
              <td>1</td>
              <td>Fire Insurance Policy</td>
              <td>v1.2</td>
              <td>2025-04-20</td>
              <td>
                <button class="btn btn-sm btn-info">Compare</button>
                <button class="btn btn-sm btn-warning">Restore</button>
                <button class="btn btn-sm btn-danger">Delete</button>
              </td>
            </tr>
            <tr>
              <td>2</td>
              <td>Contract Agreement</td>
              <td>v2.0</td>
              <td>2025-04-10</td>
              <td>
                <button class="btn btn-sm btn-info">Compare</button>
                <button class="btn btn-sm btn-warning">Restore</button>
                <button class="btn btn-sm btn-danger">Delete</button>
              </td>
            </tr>
            <!-- Use PHP to loop through actual version records -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
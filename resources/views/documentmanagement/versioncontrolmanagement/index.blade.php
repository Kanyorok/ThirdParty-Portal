@extends('layouts.app')
@section('title', 'versioncontrolmanagement')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Document List with Version Control</h3>
    </div>
    <a href="{{ route('versioncontrolmanagement.create') }}"class="btn btn-success">← Add New Document Version</a>
  </div>

  <!-- Document Table -->
  <div class="card">
    <div class="card-header bg-secondary text-white">📁 Documents</div>
    <div class="card-body p-0">
      <table class="table table-bordered table-striped mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Type</th>
            <th>Uploaded</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample Row -->
          <tr>
            <td>1</td>
            <td>Fire Insurance Policy</td>
            <td>Policy</td>
            <td>2025-04-10</td>
            <td>
              <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#versionModal">View Versions</button>
            </td>
          </tr>
          <!-- Add more rows dynamically -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Version Control Modal -->
<div class="modal fade" id="versionModal" tabindex="-1" aria-labelledby="versionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="versionModalLabel">Document Versions – Fire Insurance Policy</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Upload New Version -->
        <form class="mb-4" method="post" enctype="multipart/form-data" action="upload_version.php">
          <div class="row g-2 align-items-end">
            <div class="col-md-6">
              <label class="form-label">Upload New Version</label>
              <input type="file" name="version_file" class="form-control" required>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-success w-100">Upload</button>
            </div>
          </div>
        </form>

        <!-- Version History Table -->
        <div class="table-responsive">
          <table class="table table-bordered table-sm">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Version</th>
                <th>Uploaded By</th>
                <th>Date</th>
                <th>Notes</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Sample Versions -->
              <tr>
                <td>1</td>
                <td>v1.0</td>
                <td>Admin</td>
                <td>2025-04-10</td>
                <td>Initial version</td>
                <td>
                  <button class="btn btn-sm btn-info">Compare</button>
                  <button class="btn btn-sm btn-warning">Restore</button>
                  <button class="btn btn-sm btn-danger">Delete</button>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>v1.1</td>
                <td>Admin</td>
                <td>2025-04-15</td>
                <td>Updated coverage section</td>
                <td>
                  <button class="btn btn-sm btn-info">Compare</button>
                  <button class="btn btn-sm btn-warning">Restore</button>
                  <button class="btn btn-sm btn-danger">Delete</button>
                </td>
              </tr>
              <!-- Use PHP to loop through versions dynamically -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

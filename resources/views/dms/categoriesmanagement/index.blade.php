@extends('layouts.app')
@section('title', 'categoriesmanagement')
@section('content')
<div class="container mt-5" style="max-width: 900px;">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3>Folders / Categories Management</h3>
      <p class="text-muted mb-0">📁 Logical organization of documents</p>
    </div>
    <a href="{{ route('categoriesmanagement.create') }}"class="btn btn-success">+ Add Folder</a>
  </div>

  <!-- Folder List Table -->
  <div class="card">
    <div class="card-header bg-secondary text-white">
      📋 Folder List
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Folder Name</th>
              <th>Description</th>
              <th>Created At</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Static sample rows -->
            <tr>
              <td>1</td>
              <td>HR Documents</td>
              <td>Human resource related files</td>
              <td>2025-04-01</td>
              <td>
                <button class="btn btn-sm btn-primary">Edit</button>
                <button class="btn btn-sm btn-danger">Delete</button>
              </td>
            </tr>
            <tr>
              <td>2</td>
              <td>Finance</td>
              <td>Invoices and receipts</td>
              <td>2025-04-10</td>
              <td>
                <button class="btn btn-sm btn-primary">Edit</button>
                <button class="btn btn-sm btn-danger">Delete</button>
              </td>
            </tr>
            <!-- Replace with dynamic PHP rows as needed -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
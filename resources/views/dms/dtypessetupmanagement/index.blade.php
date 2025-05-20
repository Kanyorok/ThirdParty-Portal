@extends('layouts.app')
@section('title', 'dtypessetupmanagement')
@section('content')
<div class="container mt-5" style="max-width: 800px;">
  <!-- Page Header with link -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Document Types Setup</h3>
    <a href="{{ route('dtypessetupmanagement.create') }}" class="btn btn-outline-secondary">← Add Document Type</a>
  </div>

  <!-- Document Type List -->
  <div class="card">
    <div class="card-header bg-secondary text-white">
      📃 Document Type List
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Description</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Static Sample Rows -->
            <tr>
              <td>1</td>
              <td>Contract</td>
              <td>Legal agreements and contracts</td>
              <td>
                <button class="btn btn-sm btn-primary">Edit</button>
                <button class="btn btn-sm btn-danger">Delete</button>
              </td>
            </tr>
            <tr>
              <td>2</td>
              <td>ID Copy</td>
              <td>Identification documents</td>
              <td>
                <button class="btn btn-sm btn-primary">Edit</button>
                <button class="btn btn-sm btn-danger">Delete</button>
              </td>
            </tr>
            <!-- Add dynamic PHP rows here if needed -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
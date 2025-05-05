@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-secondary text-white rounded-top-4 d-flex justify-content-between align-items-center">
      <h4 class="mb-0">📂 Item Category List</h4>
      <a href="{{ route('itemcategory.create') }}" class="btn btn-sm btn-light">➕ Add New</a>
    </div>
    <div class="card-body">

      <table class="table table-striped table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Category Code</th>
            <th>Category Name</th>
            <th>Description</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Example row -->
          <tr>
            <td>1</td>
            <td>CAT-001</td>
            <td>Office Supplies</td>
            <td>Includes all stationary and desk items</td>
            <td><span class="badge bg-success">Active</span></td>
            <td>
              <button class="btn btn-sm btn-primary">Edit</button>
              <button class="btn btn-sm btn-danger">Delete</button>
            </td>
          </tr>
          <!-- Repeat rows dynamically -->
        </tbody>
      </table>

    </div>
  </div>
</div>
@endsection
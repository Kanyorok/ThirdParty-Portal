@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-info text-white rounded-top-4 d-flex justify-content-between align-items-center">
      <h4 class="mb-0">📁 Item Sub Category List</h4>
      <a href="{{ route('itemsubcategory.create') }}" class="btn btn-sm btn-light">➕ Add New</a>
    </div>
    <div class="card-body">

      <table class="table table-striped table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Sub Category Code</th>
            <th>Sub Category Name</th>
            <th>Parent Category</th>
            <th>Description</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample Row -->
          <tr>
            <td>1</td>
            <td>SUB-CAT-001</td>
            <td>Toners & Ink</td>
            <td>Office Supplies</td>
            <td>All printer consumables</td>
            <td><span class="badge bg-success">Active</span></td>
            <td>
              <button class="btn btn-sm btn-primary">Edit</button>
              <button class="btn btn-sm btn-danger">Delete</button>
            </td>
          </tr>
          <!-- Add more rows dynamically -->
        </tbody>
      </table>

    </div>
  </div>
</div>
@endsection
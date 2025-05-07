@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-dark text-white rounded-top-4">
      <h4 class="mb-0">📦 SKU Master List</h4>
      <a href="{{ route('sku.create') }}" class="btn btn-success">➕ Add New Stock Item</a>
    </div>
    <div class="card-body">

      <table class="table table-striped table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>SKU Code</th>
            <th>Batch</th>
            <th>Serial</th>
            <th>Perishable</th>
            <th>Saleable</th>
            <th>Purchasable</th>
            <th>Store</th>
            <th>Branch</th>
            <th>Current Qty</th>
            <th>Min</th>
            <th>Reorder</th>
            <th>Max</th>
            <th>Last Received</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Example Row -->
          <tr>
            <td>1</td>
            <td>SKU-000123</td>
            <td>✅</td>
            <td>❌</td>
            <td>✅</td>
            <td>✅</td>
            <td>❌</td>
            <td>Central Store</td>
            <td>Branch A</td>
            <td>120</td>
            <td>10</td>
            <td>50</td>
            <td>200</td>
            <td>2025-04-25</td>
            <td><span class="badge bg-success">Active</span></td>
            <td>
              <a href="{{ route('sku.create') }}" class="btn btn-sm btn-primary">Edit</a>
              <a class="btn btn-sm btn-danger">Delete</a>
            </td>
          </tr>
          <!-- Repeat rows dynamically -->
        </tbody>
      </table>

    </div>
  </div>
</div>
@endsection
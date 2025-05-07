@extends('layouts.app')
@section('title', 'Create Tranfer')
@section('content')
<body class="bg-light p-4">

  <div class="container bg-white shadow-sm rounded p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>🛠️ Stock Adjustment List</h4>
      <a href="stock-adjustment-form.html" class="btn btn-success">➕ New Adjustment</a>
    </div>

    <div class="mb-3">
      <input type="text" class="form-control" placeholder="🔍 Search by Store, Reason, or Adjusted By">
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Store</th>
            <th>Reason</th>
            <th>Adjusted By</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>2025-05-02</td>
            <td>Central Warehouse</td>
            <td>Damage</td>
            <td>Daniel Mbugua</td>
            <td><span class="badge bg-success">Approved</span></td>
            <td>
              <a href="#" class="btn btn-sm btn-primary">🔍 View</a>
              <a href="#" class="btn btn-sm btn-secondary">✏️ Edit</a>
            </td>
          </tr>
          <tr>
            <td>2</td>
            <td>2025-04-28</td>
            <td>Branch A</td>
            <td>Expired</td>
            <td>Jane Njeri</td>
            <td><span class="badge bg-warning text-dark">Pending</span></td>
            <td>
              <a href="#" class="btn btn-sm btn-primary">🔍 View</a>
              <a href="#" class="btn btn-sm btn-secondary">✏️ Edit</a>
            </td>
          </tr>
          <!-- More rows as needed -->
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  @endSection
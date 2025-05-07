@extends('layouts.app')
@section('title', 'View Transfer')
@section('content')
<div class="container bg-white shadow-sm rounded p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>🔁 Goods Transfer List</h4>
      <a href="stock-transfer-form.html" class="btn btn-success">➕ New Transfer</a>
    </div>

    <div class="mb-3">
      <input type="text" class="form-control" placeholder="🔍 Search by Store, Date or Transfer ID">
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Transfer ID</th>
            <th>Date</th>
            <th>From Store</th>
            <th>To Store</th>
            <th>Transferred By</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>TRF-1001</td>
            <td>2025-05-02</td>
            <td>Central Warehouse</td>
            <td>Branch A</td>
            <td>Daniel Mbugua</td>
            <td><span class="badge bg-success">Completed</span></td>
            <td>
              <a href="#" class="btn btn-sm btn-primary">🔍 View</a>
              <a href="#" class="btn btn-sm btn-secondary">✏️ Edit</a>
            </td>
          </tr>
          <tr>
            <td>2</td>
            <td>TRF-1002</td>
            <td>2025-04-30</td>
            <td>Branch A</td>
            <td>Branch B</td>
            <td>Jane Njeri</td>
            <td><span class="badge bg-warning text-dark">Pending</span></td>
            <td>
              <a href="#" class="btn btn-sm btn-primary">🔍 View</a>
              <a href="#" class="btn btn-sm btn-secondary">✏️ Edit</a>
            </td>
          </tr>
          <!-- Add more rows as needed -->
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endSection
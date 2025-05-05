@extends('layouts.app')
@section('title', 'Goods Receipt')
@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-success text-white rounded-top-4 d-flex justify-content-between align-items-center">
      <h4 class="mb-0">📑 GRN Posting List</h4>
      <a href="{{ route('procurementreceipts.create') }}" class="btn btn-light btn-sm">➕ Add GRN</a>
    </div>
    <div class="card-body">

      <table class="table table-bordered table-hover table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>GRN No</th>
            <th>Date</th>
            <th>Supplier</th>
            <th>Store</th>
            <th>Posted By</th>
            <th>Total (Ksh)</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample Row -->
          <tr>
            <td>1</td>
            <td>GRN-00125</td>
            <td>2025-05-01</td>
            <td>Office Suppliers Ltd.</td>
            <td>Central Warehouse</td>
            <td>Moses K.</td>
            <td>15,000.00</td>
            <td><span class="badge bg-success">Posted</span></td>
            <td>
              <button class="btn btn-sm btn-primary">View</button>
              <button class="btn btn-sm btn-secondary">Print</button>
              <button class="btn btn-sm btn-danger">Delete</button>
            </td>
          </tr>
          <tr>
            <td>2</td>
            <td>GRN-00126</td>
            <td>2025-05-02</td>
            <td>Furniture Masters</td>
            <td>Branch A</td>
            <td>Jane N.</td>
            <td>42,500.00</td>
            <td><span class="badge bg-warning text-dark">Pending</span></td>
            <td>
              <button class="btn btn-sm btn-primary">View</button>
              <button class="btn btn-sm btn-secondary">Print</button>
              <button class="btn btn-sm btn-danger">Delete</button>
            </td>
          </tr>
          <!-- Add more rows dynamically -->
        </tbody>
      </table>

    </div>
  </div>
</div>

</body>
@endsection
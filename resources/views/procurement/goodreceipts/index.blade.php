@extends('layouts.app')
@section('title', 'Goods Receipt')
@section('content')
<!-- GRNIndex.html -->
<div class="container mt-4">
  <h4 class="mb-3">📑 GRN Listing – Goods Receipt Notes</h4>

  <!-- Search/Filter -->
  <div class="row mb-3">
    <div class="col-md-3">
      <input type="text" class="form-control" placeholder="Search by GRN No / PO No">
    </div>
    <div class="col-md-3">
      <select class="form-select">
        <option>Filter by Status</option>
        <option>Draft</option>
        <option>Posted</option>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Search</button>
    </div>
    <div class="col-md-4 text-end">
      <a href="{{ route('procurementreceipts.create') }}" class="btn btn-success">+ New GRN</a>
    </div>
  </div>

  <!-- GRN Table -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>GRN No</th>
          <th>PO No</th>
          <th>Supplier</th>
          <th>Date</th>
          <th>Status</th>
          <th>Received By</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>GRN-2025-001</td>
          <td>PO-1001</td>
          <td>ABC Suppliers</td>
          <td>2025-05-07</td>
          <td><span class="badge bg-warning">Draft</span></td>
          <td>Moses K.</td>
          <td>
            <a href="#">View</a> | <a href="#">Edit</a> | <a href="#">Print</a>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td>GRN-2025-002</td>
          <td>PO-1002</td>
          <td>XYZ Limited</td>
          <td>2025-05-06</td>
          <td><span class="badge bg-success">Posted</span></td>
          <td>Jane D.</td>
          <td>
            <a href="#">View</a> | <a href="#">Print</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
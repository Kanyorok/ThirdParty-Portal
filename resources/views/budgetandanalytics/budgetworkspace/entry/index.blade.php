@extends('layouts.app')
@section('title', 'Budget Entries by Product')
@section('content')
<div class="card p-3">
  <h5>📋 Budget Entries by Product</h5>
    <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('entrybyproduct.create') }}" class="btn btn-success">➕ Add Entries</a>
    
  </div>
  <table class="table table-bordered table-hover">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Branch</th>
        <th>Scenario</th>
        <th>Product</th>
        <th>Period</th>
        <th>Volume</th>
        <th>Projected Value (KES)</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Central Branch</td>
        <td>Base Case</td>
        <td>Personal Loan</td>
        <td>Jan-2025</td>
        <td>120</td>
        <td>12,000,000</td>
        <td><span class="badge bg-warning">Pending</span></td>
        <td>
          <button class="btn btn-sm btn-outline-info">👁 View</button>
          <button class="btn btn-sm btn-outline-danger">🗑 Delete</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection

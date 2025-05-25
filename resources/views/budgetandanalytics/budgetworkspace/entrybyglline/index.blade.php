@extends('layouts.app')
@section('title', 'Budget Entries by GL Line')
@section('content')
<div class="card p-3">
      <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('entrybyglline.create') }}" class="btn btn-success">➕ Add Entry</a>
    
  </div>
  <h5>📋 Budget Entries by GL Line</h5>
  <table class="table table-striped table-hover">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Branch</th>
        <th>Scenario</th>
        <th>Budget Line</th>
        <th>Period</th>
        <th>Amount (KES)</th>
        <th>Entry Mode</th>
        <th>Driver Used</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Central Branch</td>
        <td>Base Case</td>
        <td>Interest Income – Loans</td>
        <td>Jan-2025</td>
        <td>1,200,000</td>
        <td>Driver-Based</td>
        <td>Loan Book Growth</td>
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
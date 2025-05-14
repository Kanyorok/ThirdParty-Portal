@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="card p-4 shadow rounded-4">
  <h4 class="mb-4">✅ Departmental/Branch Needs - Approval Queue</h4>

  <table class="table table-hover table-bordered">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Item Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Est. Cost</th>
        <th>Submitted By</th>
        <th>Submitted On</th>
        <th>Required By</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Sample Row -->
      <tr>
        <td>1</td>
        <td>Office Chairs</td>
        <td>Furniture</td>
        <td>10</td>
        <td>100,000</td>
        <td>Jane Mwangi</td>
        <td>2025-05-10</td>
        <td>2025-06-15</td>
        <td>
          <a href="{{ route('needsapproval.create') }}"  class="btn btn-sm btn-info">View</a>
          <button class="btn btn-sm btn-success">Approve</button>
          <button class="btn btn-sm btn-danger">Reject</button>
        </td>
      </tr>
      <!-- Loop rows dynamically -->
    </tbody>
  </table>
</div>

@endsection
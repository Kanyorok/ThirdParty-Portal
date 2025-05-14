@extends('layouts.app')
@section('title', 'Raise Need')
@section('content')
<div class="card p-4 shadow rounded-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-4">📂 My Department's Procurement Needs</h4>
    <a href="{{ route('procurementdepartmentalplan.create') }}" class="btn btn-success">+ Add Need</a>
    </div>   

  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Item Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Est. Cost</th>
        <th>Status</th>
        <th>Required By</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <!-- Sample Row -->
      <tr>
        <td>1</td>
        <td>Printer</td>
        <td>IT Equipment</td>
        <td>2</td>
        <td>30000</td>
        <td><span class="badge bg-warning">Submitted</span></td>
        <td>2025-07-15</td>
        <td>
          <a href="#" class="btn btn-sm btn-outline-info">View</a>
          <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
        </td>
      </tr>
      <!-- Load dynamically -->
    </tbody>
  </table>
</div>

@endsection

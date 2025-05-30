@extends('layouts.app')
@section('title', 'Inter-Branch Requisition List')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📋 Inter-Branch Requisition List</h4>
  <a href="{{ route('interbranchrequisition.create') }}" class="btn btn-sm btn-light">➕ Add Requisition</a>
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>From Branch</th>
        <th>To Branch</th>
        <th>Date</th>
        <th>Status</th>
        <th>Items</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Branch A</td>
        <td>Branch B</td>
        <td>2025-05-01</td>
        <td><span class="badge bg-warning">Pending Approval</span></td>
        <td>2</td>
        <td><button class="btn btn-sm btn-outline-primary">👁 View</button></td>
      </tr>
      <tr>
        <td>2</td>
        <td>Branch B</td>
        <td>Branch A</td>
        <td>2025-04-30</td>
        <td><span class="badge bg-success">Issued</span></td>
        <td>1</td>
        <td><button class="btn btn-sm btn-outline-primary">👁 View</button></td>
      </tr>
    </tbody>
  </table>
</div>
@endsection
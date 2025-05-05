@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📋 Stock Take Records</h4>
  <a href="{{ route('stocktake.create') }}" class="btn btn-success">➕ Add New Stock Take</a>
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Branch</th>
        <th>Store</th>
        <th>Counted By</th>
        <th>Date</th>
        <th>Status</th>
        <th>Posted By</th>
        <th>Posted Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Branch A</td>
        <td>Main Store</td>
        <td>Moses K.</td>
        <td>2025-05-02</td>
        <td><span class="badge bg-warning text-dark">Pending</span></td>
        <td>–</td>
        <td>–</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-success">📌 Post</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Branch B</td>
        <td>Back Store</td>
        <td>Jane M.</td>
        <td>2025-04-30</td>
        <td><span class="badge bg-success">Posted</span></td>
        <td>Admin</td>
        <td>2025-04-30 11:42 AM</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection
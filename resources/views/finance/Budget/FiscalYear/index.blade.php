@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📘 Budget Fiscal Years</h4>

  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Fiscal Year</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Current</th>
        <th>Locked</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>FY2024/25</td>
        <td>2024-07-01</td>
        <td>2025-06-30</td>
        <td>✅</td>
        <td>❌</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">✏️ Edit</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection
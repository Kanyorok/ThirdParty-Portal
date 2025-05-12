@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
<a href="{{ route('workcompletion.create') }}" class="btn btn-primary mb-3">Log Completion</a>
  <h4 class="fw-bold mb-3">📋 Maintenance Completion Records</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Request</th>
        <th>Unit</th>
        <th>Type</th>
        <th>Completed By</th>
        <th>Completion Date</th>
        <th>Status</th>
        <th>Remarks</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>REQ-2025-001</td>
        <td>Unit 101 - Sunset Plaza</td>
        <td>Plumbing</td>
        <td>Mary N.</td>
        <td>2025-05-04</td>
        <td><span class="badge bg-success">Completed</span></td>
        <td>Pipe replaced</td>
        <td>
          <button class="btn btn-sm btn-outline-secondary">📄 View</button>
          <button class="btn btn-sm btn-outline-primary">⬇ Download</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>REQ-2025-002</td>
        <td>Unit 204 - Mountain View</td>
        <td>Electrical</td>
        <td>BrightElectric Co.</td>
        <td>2025-05-03</td>
        <td><span class="badge bg-warning text-dark">Delayed</span></td>
        <td>Awaiting breaker delivery</td>
        <td>
          <button class="btn btn-sm btn-outline-secondary">📄 View</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection

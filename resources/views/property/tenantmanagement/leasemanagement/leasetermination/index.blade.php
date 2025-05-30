@extends('layouts.app')
@section('title', 'Lease Terminations')
@section('content')
<div class="container mt-4">
<a href="{{ route('terminatelease.create') }}" class="btn btn-primary mb-3">Terminate Lease</a>
  <h4 class="fw-bold mb-3">📋 Lease Terminations</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Tenant</th>
        <th>Lease</th>
        <th>Unit</th>
        <th>Termination Date</th>
        <th>Reason</th>
        <th>Remarks</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Moses K.</td>
        <td>#L-2025-001</td>
        <td>Unit 101 - Sunset Plaza</td>
        <td>2025-08-31</td>
        <td>Normal Expiry</td>
        <td>Cleared on time</td>
        <td><button class="btn btn-sm btn-outline-secondary">📄 View</button></td>
      </tr>
      <tr>
        <td>2</td>
        <td>Jane W.</td>
        <td>#L-2025-004</td>
        <td>Unit 203 - Green Court</td>
        <td>2025-05-15</td>
        <td>Voluntary Exit</td>
        <td>Moved to another town</td>
        <td><button class="btn btn-sm btn-outline-secondary">📄 View</button></td>
      </tr>
    </tbody>
  </table>
</div>
@endsection
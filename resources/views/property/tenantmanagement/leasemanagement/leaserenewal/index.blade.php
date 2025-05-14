@extends('layouts.app')
@section('title', 'Lease Renewals')
@section('content')
<div class="container mt-4">
<a href="{{ route('renewlease.create') }}" class="btn btn-primary mb-3">Renew Lease</a>
  <h4 class="fw-bold mb-3">📋 Lease Renewals</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Tenant</th>
        <th>Old Lease</th>
        <th>New Lease</th>
        <th>Unit</th>
        <th>New Period</th>
        <th>New Rent</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Moses K.</td>
        <td>#L-2025-001</td>
        <td>#L-2025-008</td>
        <td>Unit 101 - Sunset Plaza</td>
        <td>Sep 2025 – Aug 2026</td>
        <td>KES 27,500</td>
        <td><button class="btn btn-sm btn-outline-secondary">📄 View</button></td>
      </tr>
    </tbody>
  </table>
</div>
@endsection
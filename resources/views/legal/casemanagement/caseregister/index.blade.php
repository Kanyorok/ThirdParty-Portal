@extends('layouts.app')
@section('title', 'caseregister')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4>📋 Case Register</h4>
    <a href="{{ route('caseregister.create') }}" class="btn btn-primary">+ Add New Case</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Case Number</th>
          <th>Title</th>
          <th>Status</th>
          <th>Jurisdiction</th>
          <th>Court</th>
          <th>Next Hearing</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Static Row -->
        <tr>
          <td>1</td>
          <td>LC/2025/015</td>
          <td>Land Dispute Case</td>
          <td><span class="badge bg-warning text-dark">Pending</span></td>
          <td>Kirinyaga County</td>
          <td>Kutus Law Courts</td>
          <td>2025-06-10</td>
          <td>
            <a href="#" class="btn btn-sm btn-info">View</a>
            <a href="#" class="btn btn-sm btn-secondary">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
          </td>
        </tr>
        <!-- Dynamically populate rows from database -->
      </tbody>
    </table>
  </div>
</div>
@endsection
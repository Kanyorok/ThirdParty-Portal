@extends('layouts.app')
@section('title', 'casedetails')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📋 Case Details</h3>
    <a href="{{ route('casedetails.create') }}" class="btn btn-primary">+ Add Case</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Case Number</th>
          <th>Parties</th>
          <th>Jurisdiction</th>
          <th>Court</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample static row -->
        <tr>
          <td>1</td>
          <td>2023/LC/045</td>
          <td>ABC Ltd vs XYZ Co</td>
          <td>Nairobi</td>
          <td>High Court</td>
          <td><span class="badge bg-warning text-dark">Ongoing</span></td>
          <td>
            <button class="btn btn-sm btn-info">View</button>
            <button class="btn btn-sm btn-secondary">Edit</button>
            <button class="btn btn-sm btn-danger">Delete</button>
          </td>
        </tr>
        <!-- Add dynamic PHP rows here -->
      </tbody>
    </table>
  </div>
</div>
@endsection
@extends('layouts.app')
@section('title', 'contractdrafting')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📄 Drafted Contracts</h3>
    <a href="{{ route('contractdrafting.create') }}" class="btn btn-primary">+ New Contract</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Contract Title</th>
          <th>Template Used</th>
          <th>Parties Involved</th>
          <th>Initiated By</th>
          <th>Date Initiated</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample static row -->
        <tr>
          <td>1</td>
          <td>Vendor NDA</td>
          <td>NDA</td>
          <td>Company A, Vendor B</td>
          <td>Jane Smith</td>
          <td>2025-05-10</td>
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
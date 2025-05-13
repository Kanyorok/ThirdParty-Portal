@extends('layouts.app')
@section('title', 'contractrepository')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📄 Contract Repository</h3>
    <a href="{{ route('contractrepository.create') }}" class="btn btn-primary">+ Upload New Contract</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Type</th>
          <th>Parties</th>
          <th>Effective Date</th>
          <th>Expiry Date</th>
          <th>Document</th>
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
          <td>2025-01-15</td>
          <td>2026-01-15</td>
          <td><a href="uploads/vendor_nda.pdf" target="_blank">Download</a></td>
          <td>
            <button class="btn btn-sm btn-info">View</button>
            <button class="btn btn-sm btn-danger">Delete</button>
          </td>
        </tr>
        <!-- Add dynamic PHP rows here -->
      </tbody>
    </table>
  </div>
</div>
@endsection
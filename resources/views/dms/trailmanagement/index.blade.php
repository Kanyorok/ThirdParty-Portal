@extends('layouts.app')
@section('title', 'trailmanagement')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4>📜 Document Audit Trail</h4>
    <a href="{{ route('trailmanagement.create') }}" class="btn btn-outline-secondary">+ Log Action</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped table-sm">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Action</th>
          <th>Document</th>
          <th>Timestamp</th>
          <th>IP Address</th>
          <th>User Agent</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Static Row -->
        <tr>
          <td>1</td>
          <td>johndoe</td>
          <td>Viewed</td>
          <td>Policy.pdf</td>
          <td>2025-05-08 14:25</td>
          <td>192.168.0.105</td>
          <td>Mozilla/5.0 (Windows NT 10.0)</td>
        </tr>
        <tr>
          <td>2</td>
          <td>admin</td>
          <td>Deleted</td>
          <td>Old_Contract.docx</td>
          <td>2025-05-07 09:10</td>
          <td>192.168.0.21</td>
          <td>Chrome/120.0.0</td>
        </tr>
        <!-- Populate dynamically with PHP from DB -->
      </tbody>
    </table>
  </div>
</div>

@endsection
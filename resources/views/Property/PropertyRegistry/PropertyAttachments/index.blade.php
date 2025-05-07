@extends('layouts.app')
@section('title', 'Property Documents')
@section('content')
<div class="container mt-4">

<a href="{{ route('attachments.create') }}" class="btn btn-primary mb-3">Attach Document</a>
  <h4 class="fw-bold mb-3">📋 Property Documents</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Property</th>
        <th>Document Title</th>
        <th>Type</th>
        <th>Uploaded By</th>
        <th>Uploaded On</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Sunset Plaza</td>
        <td>Title Deed</td>
        <td>Ownership</td>
        <td>Moses K.</td>
        <td>2025-05-01</td>
        <td>
          <a href="#" class="btn btn-sm btn-outline-primary">⬇ Download</a>
          <button class="btn btn-sm btn-outline-danger">🗑 Delete</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Mountain View Estate</td>
        <td>Site Blueprint</td>
        <td>Architectural Plan</td>
        <td>Admin</td>
        <td>2025-04-28</td>
        <td>
          <a href="#" class="btn btn-sm btn-outline-primary">⬇ Download</a>
          <button class="btn btn-sm btn-outline-danger">🗑 Delete</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection
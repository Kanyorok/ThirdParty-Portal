@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
<a href="{{ route('addtenant.create') }}" class="btn btn-primary mb-3">Add Tenant</a>
  <h4 class="fw-bold mb-3">📋 Registered Tenants</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Type</th>
        <th>ID / Reg No.</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Moses K.</td>
        <td>Individual</td>
        <td>ID12345678</td>
        <td>+254712345678</td>
        <td>moses@example.com</td>
        <td><span class="badge bg-success">Active</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <a href="{{ route('addtenant.create') }}" class="btn btn-sm btn-outline-warning">✏️ Edit</a>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Acme Ltd.</td>
        <td>Corporate</td>
        <td>BRN0021</td>
        <td>+254701234567</td>
        <td>info@acme.com</td>
        <td><span class="badge bg-secondary">Inactive</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <a href="{{ route('addtenant.create') }}" class="btn btn-sm btn-outline-warning">✏️ Edit</a>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection
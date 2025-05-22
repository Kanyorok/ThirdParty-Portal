@extends('layouts.app')
@section('title', 'accessmanagement')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4>📋 Current Access Permissions</h4>
    <a href="{{ route('accessmanagement.create') }}" class="btn btn-success">🔐 Assign New Permission</a>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <table class="table table-bordered table-striped mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>User/Role</th>
            <th>Level</th>
            <th>Target</th>
            <th>Permission</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample Row -->
          <tr>
            <td>1</td>
            <td>Role: Admin</td>
            <td>Module</td>
            <td>Finance</td>
            <td>Full Access</td>
            <td>
              <button class="btn btn-sm btn-primary">Edit</button>
              <button class="btn btn-sm btn-danger">Revoke</button>
            </td>
          </tr>
          <tr>
            <td>2</td>
            <td>User: John Doe</td>
            <td>Document</td>
            <td>Doc ID 101</td>
            <td>View Only</td>
            <td>
              <button class="btn btn-sm btn-primary">Edit</button>
              <button class="btn btn-sm btn-danger">Revoke</button>
            </td>
          </tr>
          <!-- Add more dynamically from backend -->
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
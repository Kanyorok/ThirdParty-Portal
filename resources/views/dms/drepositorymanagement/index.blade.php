@extends('layouts.app')
@section('title', 'drepositorymanagement')
@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Document Repository</h4>
    <a href="{{ route('drepositorymanagement.create') }}" class="btn btn-sm btn-success">+ Add Document</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Type</th>
          <th>Department/Module</th>
          <th>Linked Entity</th>
          <th>Tags</th>
          <th>Confidentiality</th>
          <th>Description</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Row -->
        <tr>
          <td>1</td>
          <td>Fleet Insurance Contract</td>
          <td>Contract</td>
          <td>Fleet Management</td>
          <td>Asset ID #345</td>
          <td>insurance, fleet</td>
          <td><span class="badge bg-warning text-dark">Restricted</span></td>
          <td>Annual fleet insurance contract with APA.</td>
          <td>
            <button class="btn btn-sm btn-primary">View</button>
            <button class="btn btn-sm btn-danger">Delete</button>
          </td>
        </tr>
        <!-- Repeat rows dynamically -->
      </tbody>
    </table>
  </div>
</div>
@endsection
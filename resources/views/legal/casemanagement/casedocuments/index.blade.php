@extends('layouts.app')
@section('title', 'casedocuments')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📁 Case Documents</h3>
    <a href="{{ route('casedocuments.create') }}" class="btn btn-primary">+ Upload Document</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Case Number</th>
          <th>Document Type</th>
          <th>File Name</th>
          <th>Uploaded On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample static row -->
        <tr>
          <td>1</td>
          <td>2023/LC/012</td>
          <td>Contract</td>
          <td>contract_ABC.pdf</td>
          <td>2025-05-08</td>
          <td>
            <button class="btn btn-sm btn-info">View</button>
            <button class="btn btn-sm btn-danger">Delete</button>
          </td>
        </tr>
        <!-- Replace with dynamic PHP rows from database -->
      </tbody>
    </table>
  </div>
</div>
@endsection
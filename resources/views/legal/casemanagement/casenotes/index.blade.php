@extends('layouts.app')
@section('title', 'casenotes')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📄 Case Notes & Comments</h3>
    <a href="{{ route('casenotes.create') }}" class="btn btn-primary">+ Add Note</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Case Number</th>
          <th>Note</th>
          <th>Added By</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Example static row -->
        <tr>
          <td>1</td>
          <td>2024/ARB/101</td>
          <td>Ensure submission before next hearing.</td>
          <td>Legal Officer</td>
          <td>2025-05-08</td>
          <td>
            <button class="btn btn-sm btn-secondary">Edit</button>
            <button class="btn btn-sm btn-danger">Delete</button>
          </td>
        </tr>
        <!-- Replace with dynamic PHP rows if using a database -->
      </tbody>
    </table>
  </div>
</div>
@endsection
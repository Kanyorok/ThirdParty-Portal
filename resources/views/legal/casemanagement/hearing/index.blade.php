@extends('layouts.app')
@section('title', 'hearing')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📋 Hearing Schedule</h3>
    <a href="{{ route('hearing.create') }}" class="btn btn-primary">+ Add Hearing</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Case Number</th>
          <th>Court Date</th>
          <th>Next Hearing</th>
          <th>Outcome</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample static row -->
        <tr>
          <td>1</td>
          <td>2024/LIT/200</td>
          <td>2025-04-10</td>
          <td>2025-06-12</td>
          <td>Adjourned — Awaiting new evidence</td>
          <td>
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
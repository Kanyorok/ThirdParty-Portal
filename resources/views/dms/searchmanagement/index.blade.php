@extends('layouts.app')
@section('title', 'searchmanagement')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Search Results</h4>
    <a href="{{ route('searchmanagement.create') }}" class="btn btn-outline-secondary">🔍 Modify Filters</a>
  </div>

  <!-- Placeholder for dynamic results -->
  <div class="card">
    <div class="card-body p-0">
      <table class="table table-bordered table-striped mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Type</th>
            <th>Tags</th>
            <th>Uploader</th>
            <th>Date</th>
            <th>Shape</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample Result Row -->
          <tr>
            <td>1</td>
            <td>Annual Tax Invoice</td>
            <td>Invoice</td>
            <td>finance, tax</td>
            <td>Admin</td>
            <td>2025-04-12</td>
            <td>PDF</td>
          </tr>
          <!-- Use PHP to display actual search results dynamically -->
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
@endsection
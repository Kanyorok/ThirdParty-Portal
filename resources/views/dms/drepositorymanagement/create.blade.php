@extends('layouts.app')
@section('title', 'drepositorymanagement')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4>📄 Recent Bulk Uploads</h4>
    <a href="create.php" class="btn btn-success">📤 New Bulk Upload</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Filename</th>
          <th>Uploaded By</th>
          <th>Upload Time</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Example Static Rows -->
        <tr>
          <td>1</td>
          <td>contract_A.pdf</td>
          <td>John Doe</td>
          <td>2025-05-08 14:30</td>
          <td>
            <a href="#" class="btn btn-sm btn-info">Download</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td>invoice_0425.xlsx</td>
          <td>Jane Smith</td>
          <td>2025-05-08 09:10</td>
          <td>
            <a href="#" class="btn btn-sm btn-info">Download</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
          </td>
        </tr>
        <!-- Replace with dynamic PHP content if needed -->
      </tbody>
    </table>
  </div>
</div>
@endsection
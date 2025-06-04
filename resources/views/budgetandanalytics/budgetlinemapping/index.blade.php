@extends('layouts.app')
@section('title', 'New Budget Line & GL Mapping')
@section('content')
<<<<<<< HEAD
<div class="card mb-4">

  <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('budgetlinemapping.create') }}" class="btn btn-success btn-sm">+ New Budget Line</a>
   </div>
<div class="card-header bg-secondary text-white">📄 Budget Lines List</div>
  <div class="card-body">
    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Line Name</th>
          <th>Description</th>
          <th>CBS GLs Mapped</th>
          <th>ERP GLs Mapped</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Loan Interest</td>
          <td>Projected income from interest on loans</td>
          <td>GL1001</td>
          <td>ERP1001</td>
          <td>
            <button class="btn btn-sm btn-warning">✏ Edit</button>
            <button class="btn btn-sm btn-danger">🗑 Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

@endsection
=======
    <div class="card p-3">
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('budgetlinemapping.create') }}" class="btn btn-success btn-sm">+ New Mapping</a>
            </div>
            <h5>📋 Budget Line Mapping List</h5>
            <table class="table table-bordered">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Budget Line</th>
                    <th>Product</th>
                    <th>Primary</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Interest Income - Loans</td>
                    <td>Personal Loan</td>
                    <td><span class="badge bg-success">Yes</span></td>
                    <td>
                        <button class="btn btn-sm btn-info">✏️ Edit</button>
                        <button class="btn btn-sm btn-danger">🗑️ Remove</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
>>>>>>> feature/newMenu-dev

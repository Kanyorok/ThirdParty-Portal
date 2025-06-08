@extends('layouts.app')
@section('title', 'New Budget Line & GL Mapping')
@section('content')
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
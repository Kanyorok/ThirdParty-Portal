@extends('layouts.app')
@section('title', 'Product Type List')
@section('content')
<div class="card p-3">
  <h5>📋 Product Type List</h5>
  <table class="table table-striped table-hover">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Type Name</th>
        <th>Description</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Loan</td>
        <td>All credit products including personal and SME loans</td>
        <td>
          <button class="btn btn-sm btn-info">✏️ Edit</button>
          <button class="btn btn-sm btn-danger">🗑️ Delete</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection

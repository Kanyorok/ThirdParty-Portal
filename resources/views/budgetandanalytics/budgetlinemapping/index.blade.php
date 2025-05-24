@extends('layouts.app')
@section('title', 'Budget Line Mapping')
@section('content')
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
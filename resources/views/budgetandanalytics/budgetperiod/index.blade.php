@extends('layouts.app')
@section('title', 'Budget Periods List')
@section('content')

     <div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
 <a href="{{ route('budgetperiod.create') }}" class="btn btn-success btn-sm">+ New Period</a>
 </div>
  <h5>📋 Budget Periods List</h5>
  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Fiscal Year</th>
        <th>Period Type</th>
        <th>Locked</th>
        <th>Notes</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>2025</td>
        <td>Monthly</td>
        <td>Yes</td>
        <td>Regular Year</td>
        <td>
          <button class="btn btn-sm btn-info">✏️ Edit</button>
          <button class="btn btn-sm btn-danger">🗑️ Delete</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

@endsection
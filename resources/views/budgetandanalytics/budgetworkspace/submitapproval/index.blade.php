@extends('layouts.app')
@section('title', 'Submit Budget For review')
@section('content')

<div class="card p-3">
  <h5>📋 Submitted Budgets</h5>
  <table class="table table-bordered table-hover">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Branch</th>
        <th>Scenario</th>
        <th>Period</th>
        <th>Submitted On</th>
        <th>Status</th>
        <th>Remarks</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Central Branch</td>
        <td>Base Case</td>
        <td>2025</td>
        <td>2024-11-15</td>
        <td><span class="badge bg-warning">Pending Review</span></td>
        <td>Initial estimates ready for review</td>
        <td>
          <button class="btn btn-sm btn-outline-info">👁 View</button>
          <button class="btn btn-sm btn-outline-secondary">🔁 Reopen</button>
          <button class="btn btn-sm btn-outline-primary">🔁 Submit</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

@endsection

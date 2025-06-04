@extends('layouts.app')
@section('title', 'Budget Approval Inbox')
@section('content')
<div class="card p-3">
  <h5>✅ Budget Approval Inbox</h5>
  <table class="table table-hover table-bordered">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Branch</th>
        <th>Scenario</th>
        <th>Period</th>
        <th>Submitted On</th>
        <th>Status</th>
        <th>Last Action</th>
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
        <td>Submitted by Branch Manager</td>
        <td>
        <a href="{{ route('budgetapproval.create') }}" class="btn btn-sm btn-outline-primary">🔍 Review</a>
        </td>
      </tr>
    </tbody>
  </table>
</div>

@endsection

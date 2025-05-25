@extends('layouts.app')
@section('title', 'Top-Down Budget Allocations')
@section('content')
<div class="card p-3">
    
<div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('topdownallocation.create') }}" class="btn btn-success">➕ New Budget</a>
    
  </div>
  <h5>📋 Top-Down Budget Allocations</h5>
  
  <table class="table table-hover table-bordered">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Scenario</th>
        <th>Budget Period</th>
        <th>Budget Line</th>
        <th>Total Target (KES)</th>
        <th>Status</th>
        <th>Last Modified</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Base Case</td>
        <td>2025</td>
        <td>Interest Income – Loans</td>
        <td>10,000,000</td>
        <td><span class="badge bg-success">Finalized</span></td>
        <td>2024-11-16</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-secondary">🔁 Reopen</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Budget Scenarios')
@section('content')
<div class="card p-3">

  <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('budgetscenerios.create') }}" class="btn btn-success">➕ New Scenerio</a>
    
  </div>
  <h5>📋 Budget Scenarios</h5>
  <table class="table table-hover table-bordered">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Scenario Name</th>
        <th>Description</th>
        <th>Budget Period</th>
        <th>Planning Method</th>
        <th>Default?</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Base Case</td>
        <td>Standard conservative growth assumptions</td>
        <td>2025</td>
        <td>Bottom-Up</td>
        <td><span class="badge bg-success">Yes</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary">✏️ Edit</button>
          <button class="btn btn-sm btn-outline-danger">🗑 Delete</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection

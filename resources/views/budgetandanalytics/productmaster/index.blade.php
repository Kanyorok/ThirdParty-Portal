@extends('layouts.app')
@section('title', 'Product Master')
@section('content')
<div class="card p-3">
  <h5>📦 Product Master (CBS Synced)</h5>
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Product Code</th>
        <th>Product Name</th>
        <th>Product Type</th>
        <th>GL Code (CBS)</th>
        <th>Is Budgeted</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>P001</td>
        <td>Personal Loan</td>
        <td>Loan</td>
        <td>401001</td>
        <td><span class="badge bg-success">Yes</span></td>
        <td><span class="badge bg-primary">Active</span></td>
        <td>
          <button class="btn btn-sm btn-outline-info">👁 View</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

@endsection

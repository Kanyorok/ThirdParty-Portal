@extends('layouts.app')
@section('title', 'Supplier Applications')
@section('content')

<div class="card">
  <div class="card-header bg-primary text-white">
    📋 Supplier Applications — <strong>2025 General Prequalification</strong>
  </div>

  <div class="card-body">
    <!-- Round selection filter -->
    <div class="mb-3">
      <label>Select Prequalification Round:</label>
      <select class="form-select w-50">
        <option value="1">2025 General Suppliers Prequalification</option>
        <option value="2">2025 Works Prequalification</option>
      </select>
    </div>

    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th>Supplier</th>
          <th>Category</th>
          <th>Application Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>ABC Engineering Ltd</td>
          <td>Electrical Works</td>
          <td>2025-06-10</td>
          <td><span class="badge bg-warning text-dark">Pending Review</span></td>
          <td>
           <a href="{{ route('preqapplications.show',1) }}" class="btn btn-sm btn-info">🔍 View #1</a>
          <a href="{{ route('preqevaluation.index') }}" class="btn btn-sm btn-primary">🧮 Evaluate</a>
            <a href="#" class="btn btn-sm btn-danger">❌ Reject</a>
          </td>
        </tr>
        <tr>
          <td>GreenTech Supplies</td>
          <td>Office Stationery</td>
          <td>2025-06-08</td>
          <td><span class="badge bg-success">Approved</span></td>
          <td>
            <a href="#" class="btn btn-sm btn-info">🔍 View</a>
            <a href="#" class="btn btn-sm btn-secondary disabled">🧮 Evaluate</a>
            <a href="#" class="btn btn-sm btn-danger">❌ Reject</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

@endsection

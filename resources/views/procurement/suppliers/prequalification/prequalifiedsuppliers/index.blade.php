@extends('layouts.app')
@section('title', 'Prequalified Suppliers')

@section('content')
<div class="card">
  <div class="card-header bg-success text-white">✅ Prequalified Suppliers</div>
  <div class="card-body">

    <form method="GET" class="row mb-3">
      <div class="col-md-4">
        <label>Filter by Period</label>
        <select name="round" class="form-select">
          <option value="">-- All Periods --</option>
          <option>2025 General Procurement</option>
          <option>2025 ICT Suppliers</option>
        </select>
      </div>

      <div class="col-md-4">
        <label>Filter by Category</label>
        <select name="category" class="form-select">
          <option value="">-- All Categories --</option>
          <option>Construction</option>
          <option>ICT</option>
          <option>Transport</option>
        </select>
      </div>

      <div class="col-md-4 mt-4">
        <button type="submit" class="btn btn-primary mt-2">Filter</button>
      </div>
    </form>

    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th>Supplier</th>
          <th>Period</th>
          <th>Category</th>
          <th>Score</th>
          <th>Status</th>
          <th>Approval Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>TechLink Solutions Ltd.</td>
          <td>2025 ICT Suppliers</td>
          <td>ICT Services</td>
          <td>88%</td>
          <td><span class="badge bg-success">Prequalified</span></td>
          <td>2025-06-08</td>
          <td>
            <a href="{{ route('preqsuppliers.show', 1) }}" class="btn btn-sm btn-info">View</a>
          </td>
        </tr>
        <tr>
          <td>BuildRight Contractors</td>
          <td>2025 General Procurement</td>
          <td>Construction</td>
          <td>90%</td>
          <td><span class="badge bg-success">Prequalified</span></td>
          <td>2025-06-08</td>
          <td>
            <a href="{{ route('preqsuppliers.show', 2) }}" class="btn btn-sm btn-info">View</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
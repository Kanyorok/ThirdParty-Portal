@extends('layouts.app')
@section('title', 'Manual Entry – Procurement Plan Items')
@section('content')

<div class="card p-4 shadow rounded-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4>📋 Manual Entry – Procurement Plan Items</h4>
    <a href="{{ route('planmanualinput.create') }}" class="btn btn-primary">
      ➕ Add Item
    </a>
  </div>

  <!-- Plan Filter -->
  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label">Select Procurement Plan</label>
      <select class="form-select">
        <option selected disabled>Select Plan</option>
        <option value="1">Annual Procurement Plan - 2025</option>
        <option value="2">Mid-Year Supplementary - 2025</option>
        <!-- Loaded dynamically -->
      </select>
    </div>
  </div>

  <!-- Items Table -->
  <table class="table table-bordered table-striped mt-3">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Item</th>
        <th>Category</th>
        <th>Qty</th>
        <th>UOM</th>
        <th>Est. Cost</th>
        <th>Planned Quarter</th>
        <th>Expected Delivery</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <!-- Sample Row -->
      <tr>
        <td>1</td>
        <td>Desktop Computers</td>
        <td>IT Equipment</td>
        <td>5</td>
        <td>Pcs</td>
        <td>150,000</td>
        <td>Q2</td>
        <td>2025-06-10</td>
        <td>
          <a href="/planning/manual-entry/edit/101" class="btn btn-sm btn-outline-primary">Edit</a>
          <button class="btn btn-sm btn-outline-danger">Delete</button>
        </td>
      </tr>
      <!-- Load other rows dynamically -->
    </tbody>
  </table>
</div>

@endsection
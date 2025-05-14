@extends('layouts.app')
@section('title', 'Select Approved Needs')
@section('content')
<div class="card p-4 shadow rounded-4">
  <h4 class="mb-4">📥 Select Approved Needs to Include in Draft Plan</h4>

  <!-- Plan Selection -->
  <div class="row mb-4">
    <div class="col-md-6">
      <label class="form-label">Target Plan</label>
      <select class="form-select">
        <option selected disabled>Select Draft Plan</option>
        <option value="1">Annual Procurement Plan - 2025</option>
        <option value="2">Mid-Year Supplementary - 2025</option>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Planning Period</label>
      <select class="form-select">
        <option>2025</option>
        <option>2026</option>
        <option>2027</option>
      </select>
    </div>
  </div>

  <!-- Filter Options -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">Branch</label>
      <select class="form-select">
        <option>All</option>
        <option>Nairobi Branch</option>
        <option>Mombasa Branch</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Department</label>
      <select class="form-select">
        <option>All</option>
        <option>ICT</option>
        <option>Finance</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Category</label>
      <select class="form-select">
        <option>All</option>
        <option>IT Equipment</option>
        <option>Stationery</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-outline-primary w-100">Apply Filters</button>
    </div>
  </div>

  <!-- List of Needs -->
  <form>
    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th><input type="checkbox" id="selectAll"></th>
          <th>Item</th>
          <th>Branch</th>
          <th>Dept</th>
          <th>Qty</th>
          <th>Est. Cost</th>
          <th>Required By</th>
          <th>Justification</th>
          <th>Budget Line</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Row -->
        <tr>
          <td><input type="checkbox" class="need-checkbox" value="101"></td>
          <td>Desktop Computers</td>
          <td>Nairobi</td>
          <td>ICT</td>
          <td>4</td>
          <td>120,000</td>
          <td>2025-06-01</td>
          <td>To upgrade old machines</td>
          <td>
            <select class="form-select" name="budgetLine_101">
              <option selected disabled>Select</option>
              <option value="201">Nairobi - ICT Equipment (KES 1,000,000)</option>
              <option value="202">Nairobi - General Supplies (KES 300,000)</option>
            </select>
          </td>
        </tr>

        <!-- More rows dynamically added -->
      </tbody>
    </table>

    <!-- Submission -->
    <div class="d-flex justify-content-end mt-3">
      <button type="submit" class="btn btn-success">
        ➕ Include Selected Items in Draft Plan
      </button>
    </div>
  </form>
</div>


@endsection
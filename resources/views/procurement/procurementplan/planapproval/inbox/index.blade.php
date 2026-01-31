@extends('layouts.app')
@section('title', 'Approval Inbox')
@section('content')

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📥 Approval Inbox</h4>
    <span class="badge bg-primary">Logged in as: Budget Officer</span>
  </div>

  <!-- Optional Filters -->
  <div class="row mb-3">
    <div class="col-md-3">
      <select class="form-select">
        <option selected>All Years</option>
        <option>2025</option>
        <option>2026</option>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select">
        <option selected>Status: Pending</option>
        <option>Approved</option>
        <option>Rejected</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-outline-primary w-100">Apply Filters</button>
    </div>
  </div>

  <!-- Approval List Table -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle table-hover">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Plan Ref</th>
          <th>Title</th>
          <th>Year</th>
          <th>Requested By</th>
          <th>Submitted On</th>
          <th>Approval Level</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Row -->
        <tr>
          <td>1</td>
          <td>PLAN/2025/001</td>
          <td>Annual Procurement Plan – 2025</td>
          <td>2025</td>
          <td>Procurement Admin</td>
          <td>2025-05-13</td>
          <td>Level 1 – Budget Review</td>
          <td>
            <a href="/approval/review/1" class="btn btn-sm btn-outline-primary">Review & Approve</a>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td>PLAN/2025/002</td>
          <td>Supplementary Q2 – Health Projects</td>
          <td>2025</td>
          <td>Head of Procurement</td>
          <td>2025-05-10</td>
          <td>Level 2 – Procurement Review</td>
          <td>
            <a href="/approval/review/2" class="btn btn-sm btn-outline-primary">Review & Approve</a>
          </td>
        </tr>
        <!-- More dynamic rows -->
      </tbody>
    </table>
  </div>
</div>

@endsection
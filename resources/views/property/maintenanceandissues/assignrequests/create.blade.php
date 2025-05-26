@extends('layouts.app')
@section('title', 'Assign Technician')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🛠️ Assign Technician / Vendor</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🔧 Assignment Details</div>
    <div class="card-body">

      <!-- Maintenance Request Reference -->
      <div class="row g-3 mb-3">
        <div class="col-md-8">
          <label class="form-label">Select Maintenance Request</label>
          <select class="form-select">
            <option>REQ-2025-001 – Plumbing – Unit 101 – Sunset Plaza</option>
            <option>REQ-2025-002 – Electrical – Unit 204 – Mountain View</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Assignment Date</label>
          <input type="date" class="form-control" value="2025-05-03">
        </div>
      </div>

      <!-- Assignment Type -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Assign To</label>
          <select class="form-select" id="assignmentTypeSelect">
            <option value="internal">Internal Staff</option>
            <option value="vendor">Prequalified Vendor</option>
          </select>
        </div>

        <!-- Internal Staff Dropdown -->
        <div class="col-md-4">
          <label class="form-label">Internal Technician</label>
          <select class="form-select">
            <option>John Doe – Electrician</option>
            <option>Mary N. – Plumber</option>
            <option>David K. – General Maintenance</option>
          </select>
        </div>

        <!-- Vendor Dropdown -->
        <div class="col-md-4">
          <label class="form-label">Prequalified Vendor</label>
          <select class="form-select">
            <option>FixPro Services Ltd – Plumbing</option>
            <option>BrightElectric Co. – Electrical</option>
            <option>CleanForce Ltd – Sanitation</option>
          </select>
        </div>
      </div>

      <!-- Scheduling -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Expected Start Date</label>
          <input type="date" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Expected Completion</label>
          <input type="date" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Priority Level</label>
          <input type="text" class="form-control" value="High" readonly>
        </div>
      </div>

      <!-- Assignment Notes -->
      <div class="mb-3">
        <label class="form-label">Instructions / Notes</label>
        <textarea class="form-control" rows="2" placeholder="Describe what needs to be done..."></textarea>
      </div>

      <div class="text-end">
        <button class="btn btn-success">🔧 Assign Task</button>
      </div>
    </div>
  </div>
</div>
@endsection
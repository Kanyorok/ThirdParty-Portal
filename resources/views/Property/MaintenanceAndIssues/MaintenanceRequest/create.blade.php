@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🛠️ New Maintenance Request</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📋 Report Maintenance Issue</div>
    <div class="card-body">

      <!-- Property Drill-down -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Property</label>
          <select class="form-select">
            <option>Sunset Plaza</option>
            <option>Mountain View Estate</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Block</label>
          <select class="form-select">
            <option>Block A</option>
            <option>Block B</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Floor</label>
          <select class="form-select">
            <option>Ground Floor</option>
            <option>1st Floor</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Unit</label>
          <select class="form-select">
            <option>Unit 101</option>
            <option>Unit 204</option>
          </select>
        </div>
      </div>

      <!-- Request Details -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Reported By</label>
          <input type="text" class="form-control" placeholder="e.g. Moses K. / Caretaker">
        </div>
        <div class="col-md-4">
          <label class="form-label">Issue Type</label>
          <select class="form-select">
            <option>Plumbing</option>
            <option>Electrical</option>
            <option>Cleaning</option>
            <option>Pest Control</option>
            <option>Security</option>
            <option>Other</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Priority</label>
          <select class="form-select">
            <option>Low</option>
            <option>Medium</option>
            <option>High</option>
            <option>Emergency</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Issue Description</label>
        <textarea class="form-control" rows="3" placeholder="Describe the issue..."></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Image / Document (optional)</label>
        <input type="file" class="form-control">
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Submit Request</button>
      </div>
    </div>
  </div>
</div>

@endsection
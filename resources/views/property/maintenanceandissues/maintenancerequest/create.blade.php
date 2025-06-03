@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
@if ($errors->any())
  <div class="alert alert-danger">
    <h6 class="mb-2">⚠️ Please fix the following errors:</h6>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🛠️ New Maintenance Request</h4>

<form action="{{ route('maintenancerequest.store') }}" method="POST">
   @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📋 Report Maintenance Issue</div>
    <div class="card-body">
      <!-- Property Drill-down -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Property</label>
            <select name="Property" class="form-select" required>
              @foreach ($properties as $property)
                <option value="{{ $property->PropertyName }}">{{ $property->PropertyName }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Block</label>
            <select name="Block" class="form-select" required>
              @foreach ($blocks as $block)
                <option value="{{ $block->BlockName }}">{{ $block->BlockName }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Floor</label>
          <select name="Floor" class="form-select" required>
            @foreach ($floors as $floor)
              <option value="{{ $floor->FloorLabel }}">{{ $floor->FloorLabel }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Unit</label>
            <select name="Unit" class="form-select" required>
              @foreach ($units as $unit)
                <option value="{{ $unit->UnitCode }}">{{ $unit->UnitCode }}</option>
              @endforeach
            </select>
        </div>
      </div>

      <!-- Request Details -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Reported By</label>
          <input type="text" class="form-control" placeholder="e.g. Moses K. / Caretaker"name="ReportedBy">
        </div>
        <div class="col-md-4">
          <label class="form-label">Issue Type</label>
          <select class="form-select"name="IssueType">
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
          <select class="form-select"name="Priority">
            <option>Low</option>
            <option>Medium</option>
            <option>High</option>
            <option>Emergency</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Issue Description</label>
        <textarea class="form-control" rows="3" placeholder="Describe the issue..."name="IssueDescription"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Image / Document (optional)</label>
        <input type="file" class="form-control">
      </div>
      <button class="btn btn-success">💾 Submit Request</button>
      </form>
    </div>
  </div>
</div>

@endsection
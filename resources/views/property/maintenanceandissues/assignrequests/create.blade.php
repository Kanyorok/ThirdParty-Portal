@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🛠️ Assign Technician / Vendor</h4>

<form action="{{ route('assignrequest.store') }}" method="POST">
   @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🔧 Assignment Details</div>
    <div class="card-body">
      <!-- Maintenance Request Reference -->
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Property</label>
            <select name="Property" class="form-select" required>
              @foreach ($maintenancerequests as $maintenancerequest)
                <option value="{{ $maintenancerequest->Property }}">{{ $maintenancerequest->Property }}</option>
              @endforeach
            </select>
          </div>
        </div>
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Block</label>
            <select name="Block" class="form-select" required>
               @foreach ($maintenancerequests as $maintenancerequest)
                <option value="{{ $maintenancerequest->Block }}">{{ $maintenancerequest->Block }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Floor</label>
          <select name="Floor" class="form-select" required>
             @foreach ($maintenancerequests as $maintenancerequest)
              <option value="{{ $maintenancerequest->Floor }}">{{ $maintenancerequest->Floor }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Unit</label>
            <select name="Unit" class="form-select" required>
              @foreach ($maintenancerequests as $maintenancerequest)
                <option value="{{ $maintenancerequest->Unit }}">{{ $maintenancerequest->Unit }}</option>
              @endforeach
            </select>
        </div>
      </div>
        <div class="col-md-8">
          <label class="form-label">Select Maintenance Request</label>
            <select name="IssueDescription" class="form-select" required>
              @foreach ($maintenancerequests as $maintenancerequest)
                <option value="{{ $maintenancerequest->IssueDescription }}">{{ $maintenancerequest->IssueDescription }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Assignment Date</label>
          <input type="date" class="form-control" value="2025-05-03"name="AssignmentDate">
        </div>
      </div>

      <!-- Assignment Type -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Assign To</label>
          <select class="form-select" id="assignmentTypeSelect"name="AssignmentType">
            <option value="internal">Internal Staff</option>
            <option value="vendor">Prequalified Vendor</option>
          </select>
        </div>

        <!-- Internal Staff Dropdown -->
        <div class="col-md-4">
          <label class="form-label">Internal Technician</label>
          <select class="form-select"name="InternalTechnician">
            <option>John Doe – Electrician</option>
            <option>Mary N. – Plumber</option>
            <option>David K. – General Maintenance</option>
          </select>
        </div>

        <!-- Vendor Dropdown -->
        <div class="col-md-4">
          <label class="form-label">Prequalified Vendor</label>
          <select class="form-select"name="PrequalifiedVendor">
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
          <input type="date" class="form-control" name="ExpectedStartDate">
        </div>
        <div class="col-md-4">
          <label class="form-label">Expected Completion</label>
          <input type="date" class="form-control" name="ExpectedCompletion">
        </div>
        <div class="col-md-4">
          <label class="form-label">Priority Level</label>
          <input type="text" class="form-control" value="High"  name="PriorityLevel">
        </div>
      </div>

      <!-- Assignment Notes -->
      <div class="mb-3">
        <label class="form-label">Instructions / Notes</label>
        <textarea class="form-control" rows="2" placeholder="Describe what needs to be done..." name="InstructionNotes"></textarea>
      </div>
      <button class="btn btn-success">🔧 Assign Task</button>
    </form>
  </div>
</div>
</div>
@endsection
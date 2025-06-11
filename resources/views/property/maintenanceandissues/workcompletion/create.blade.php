@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">✅ Complete Maintenance Request</h4>

<form action="{{ route('workcompletion.store') }}" method="POST">
   @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🧰 Work Execution & Resolution</div>
    <div class="card-body">
      <!-- Request Details -->
       <!-- Maintenance Request Reference -->
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Property</label>
            <select name="Property" class="form-select" required>
              @foreach ($maintenancerequests as $maintenancerequest)
                <option value="{{ $maintenancerequest->id }}">{{ $maintenancerequest->Property }}</option>
              @endforeach
            </select>
          </div>
        </div>
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Block</label>
            <select name="Block" class="form-select" required>
               @foreach ($maintenancerequests as $maintenancerequest)
                <option value="{{ $maintenancerequest->id }}">{{ $maintenancerequest->Block }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Floor</label>
          <select name="Floor" class="form-select" required>
             @foreach ($maintenancerequests as $maintenancerequest)
              <option value="{{ $maintenancerequest->id }}">{{ $maintenancerequest->Floor }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Unit</label>
            <select name="Unit" class="form-select" required>
              @foreach ($maintenancerequests as $maintenancerequest)
                <option value="{{ $maintenancerequest->id }}">{{ $maintenancerequest->Unit }}</option>
              @endforeach
            </select>
        </div>
      </div>
        <div class="col-md-8">
          <label class="form-label">Select Maintenance Request</label>
            <select name="IssueDescription" class="form-select" required>
              @foreach ($maintenancerequests as $maintenancerequest)
                <option value="{{ $maintenancerequest->id }}">{{ $maintenancerequest->IssueDescription }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Completion Date</label>
          <input type="date" class="form-control" value="2025-05-04"name="CompletionDate">
        </div>
      </div>

      <!-- Work Details -->
      <div class="mb-3">
        <label class="form-label">Work Done Summary</label>
        <textarea class="form-control" rows="3" placeholder="e.g. Replaced leaking pipe and sealed joints." name="WorkDoneSummary"></textarea>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Parts Used (Optional)</label>
          <input type="text" class="form-control" placeholder="e.g. 3/4” Pipe, Valve"name="PartsUsed">
        </div>
        <div class="col-md-4">
          <label class="form-label">Cost (KES)</label>
          <input type="number" class="form-control" placeholder="e.g. 1500"name="Cost">
        </div>
        <div class="col-md-4">
          <label class="form-label">Final Status</label>
          <select class="form-select" name="FinalStatus">
            <option>Completed</option>
            <option>Delayed – Awaiting Part</option>
            <option>Not Fixed – Reassign</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Resolution Evidence (Photos/Invoice)</label>
        <input type="file" class="form-control" multiple>
      </div>
        <button class="btn btn-success">✔ Mark as Completed</button>
        </form>
      </div>
    </div>
</div>
@endsection

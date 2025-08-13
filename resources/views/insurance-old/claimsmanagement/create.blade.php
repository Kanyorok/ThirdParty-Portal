@extends('layouts.app')
@section('title', 'claimsmanagement')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>Create Insurance Claim</h4>
    </div>

    <form>
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="policy" class="form-label">Policy</label>
          <input type="text" class="form-control" id="policy" placeholder="Enter policy number or name" required />
        </div>
        <div class="col-md-6">
          <label for="reportedBy" class="form-label">Reported By</label>
          <input type="text" class="form-control" id="reportedBy" placeholder="Reported by" required />
        </div>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Incident Description</label>
        <textarea class="form-control" id="description" rows="3" placeholder="Describe the incident" required></textarea>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label for="incidentDate" class="form-label">Date of Incident</label>
          <input type="date" class="form-control" id="incidentDate" required />
        </div>
        <div class="col-md-4">
          <label for="estimatedLoss" class="form-label">Estimated Loss</label>
          <input type="number" class="form-control" id="estimatedLoss" placeholder="KES" required />
        </div>
        <div class="col-md-4">
          <label for="claimAmount" class="form-label">Claim Amount</label>
          <input type="number" class="form-control" id="claimAmount" placeholder="KES" required />
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="claimStatus" class="form-label">Claim Status</label>
          <select class="form-select" id="claimStatus" required>
            <option value="">Select status</option>
            <option value="submitted">Submitted</option>
            <option value="under_review">Under Review</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="supportingDocs" class="form-label">Upload Supporting Documents</label>
          <input class="form-control" type="file" id="supportingDocs" multiple />
        </div>
      </div>

      <div class="text-end">
        <button type="submit" class="btn btn-primary">Submit Claim</button>
      </div>
    </form>
  </div>

 @endsection
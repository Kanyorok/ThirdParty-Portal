@extends('layouts.app')
@section('title', 'provider management')
@section('content')
<div class="container mt-4">
  <h4 class="mb-4">Add Insurance Policy</h4>
  <form>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label for="policyNo" class="form-label">Policy Number</label>
        <input type="text" class="form-control" id="policyNo" placeholder="Enter Policy Number">
      </div>
      <div class="col-md-4 mb-3">
        <label for="policyName" class="form-label">Policy Name</label>
        <input type="text" class="form-control" id="policyName" placeholder="Enter Policy Name">
      </div>
      <div class="col-md-4 mb-3">
        <label for="insuranceType" class="form-label">Insurance Type</label>
        <select class="form-select" id="insuranceType">
          <option selected disabled>Choose...</option>
          <option>Asset</option>
          <option>Property</option>
          <option>Fleet</option>
          <option>Health</option>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label for="provider" class="form-label">Insurance Provider</label>
        <input type="text" class="form-control" id="provider" placeholder="Enter Provider Name">
      </div>
      <div class="col-md-4 mb-3">
        <label for="premiumAmount" class="form-label">Premium Amount</label>
        <input type="number" class="form-control" id="premiumAmount" placeholder="Enter Premium">
      </div>
      <div class="col-md-4 mb-3">
        <label for="currency" class="form-label">Currency</label>
        <select class="form-select" id="currency">
          <option>USD</option>
          <option>KES</option>
          <option>EUR</option>
          <option>Other</option>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label for="startDate" class="form-label">Start Date</label>
        <input type="date" class="form-control" id="startDate">
      </div>
      <div class="col-md-4 mb-3">
        <label for="endDate" class="form-label">End Date</label>
        <input type="date" class="form-control" id="endDate">
      </div>
      <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status">
          <option>Active</option>
          <option>Expired</option>
          <option>Terminated</option>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="contactInfo" class="form-label">Contact Information</label>
        <input type="text" class="form-control" id="contactInfo" placeholder="Enter Email or Phone Number">
      </div>
      <div class="col-md-6 mb-3">
        <label for="activeStatus" class="form-label">Active Status</label>
        <select class="form-select" id="activeStatus">
          <option selected disabled>Choose...</option>
          <option>Yes</option>
          <option>No</option>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label for="remarks" class="form-label">Remarks</label>
      <textarea class="form-control" id="remarks" rows="2"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Save Policy</button>
    <button type="reset" class="btn btn-secondary">Cancel</button>
  </form>
</div>
@endsection
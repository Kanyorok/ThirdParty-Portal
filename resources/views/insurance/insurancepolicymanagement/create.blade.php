@extends('layouts.app')
@section('title', 'insurancepolicymanagement')
@section('content')
<div class="container">
  <div class="header-text">Register New Insurance Policy</div>

  <!-- Form to Register a New Insurance Policy -->
  <form>
    <div class="form-group">
      <label for="provider" class="form-label">Provider</label>
      <input type="text" class="form-control" id="provider" placeholder="Enter insurance provider" required>
    </div>

    <div class="form-group">
      <label for="policyNo" class="form-label">Policy No.</label>
      <input type="text" class="form-control" id="policyNo" placeholder="Enter policy number" required>
    </div>

    <div class="form-group">
      <label for="policyType" class="form-label">Policy Type</label>
      <select class="form-select" id="policyType" required>
        <option selected disabled>Choose policy type</option>
        <option>Property</option>
        <option>Motor</option>
        <option>Health</option>
        <option>Life</option>
        <option>Other</option>
      </select>
    </div>

    <div class="form-group">
      <label for="coveredAsset" class="form-label">Covered Asset</label>
      <select class="form-select" id="coveredAsset" required>
        <option selected disabled>Choose covered asset</option>
        <option>Asset 1 (ERP)</option>
        <option>Asset 2 (ERP)</option>
        <option>Vehicle 1</option>
        <option>Vehicle 2</option>
        <option>Property 1</option>
      </select>
    </div>

    <div class="form-group">
      <label for="coverageStart" class="form-label">Coverage Start Date</label>
      <input type="date" class="form-control" id="coverageStart" required>
    </div>

    <div class="form-group">
      <label for="coverageEnd" class="form-label">Coverage End Date</label>
      <input type="date" class="form-control" id="coverageEnd" required>
    </div>

    <div class="form-group">
      <label for="premiumAmount" class="form-label">Premium Amount</label>
      <input type="number" class="form-control" id="premiumAmount" placeholder="Enter premium amount" required>
    </div>

    <div class="form-group">
      <label for="premiumFrequency" class="form-label">Premium Frequency</label>
      <select class="form-select" id="premiumFrequency" required>
        <option selected disabled>Choose frequency</option>
        <option>Monthly</option>
        <option>Quarterly</option>
        <option>Yearly</option>
      </select>
    </div>

    <div class="form-group">
      <label for="renewalTerms" class="form-label">Renewal Terms</label>
      <textarea class="form-control" id="renewalTerms" rows="3" placeholder="Enter renewal terms" required></textarea>
    </div>

    <div class="form-group">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" required>
        <option selected disabled>Choose policy status</option>
        <option>Active</option>
        <option>Expired</option>
        <option>Cancelled</option>
      </select>
    </div>

    <div class="form-group text-center">
      <button type="submit" class="btn btn-success">Register Policy</button>
      <a href="index.html" class="btn btn-outline-secondary ms-3">
        <i class="fas fa-list icon"></i> View Registered Policies
      </a>
    </div>
  </form>
</div>
@endsection
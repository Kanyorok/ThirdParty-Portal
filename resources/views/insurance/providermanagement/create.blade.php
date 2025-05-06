@extends('layouts.app')
@section('title', 'provider management')
@section('content')
<div class="container my-5">

    <!-- Section 1: Insurance Provider Management -->
    <h2 class="mb-4">1. Manage Insurance Providers</h2>
    <form>
      <div class="mb-3">
        <label for="providerName" class="form-label">Insurance Provider Name</label>
        <input type="text" class="form-control" id="providerName" placeholder="Enter provider name">
      </div>

      <div class="mb-3">
        <label for="insuranceTypes" class="form-label">Insurance Types Supported</label>
        <select multiple class="form-select" id="insuranceTypes">
          <option>Property</option>
          <option>Vehicle</option>
          <option>Health</option>
          <option>Life</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="contactInfo" class="form-label">Contact Information</label>
        <textarea class="form-control" id="contactInfo" rows="3" placeholder="Enter contact details"></textarea>
      </div>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="activeStatus">
        <label class="form-check-label" for="activeStatus">Active Status</label>
      </div>

      <button type="submit" class="btn btn-primary">Add Insurance Provider</button>
    </form>

    <hr class="my-5">

    <!-- Section 2: Policy Registration -->
    <h2 class="mb-4">2. Register Insurance Policy</h2>
    <form>
      <div class="mb-3">
        <label for="policyProvider" class="form-label">Provider</label>
        <input type="text" class="form-control" id="policyProvider" placeholder="Enter provider name">
      </div>

      <div class="mb-3">
        <label for="policyNo" class="form-label">Policy Number</label>
        <input type="text" class="form-control" id="policyNo" placeholder="Enter policy number">
      </div>

      <div class="mb-3">
        <label for="policyType" class="form-label">Policy Type</label>
        <select class="form-select" id="policyType">
          <option>Property</option>
          <option>Motor</option>
          <option>Health</option>
          <option>Life</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="coveredAsset" class="form-label">Covered Asset</label>
        <select class="form-select" id="coveredAsset">
          <option>Asset 1</option>
          <option>Vehicle A</option>
          <option>Property X</option>
        </select>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="startDate" class="form-label">Coverage Start Date</label>
          <input type="date" class="form-control" id="startDate">
        </div>
        <div class="col-md-6 mb-3">
          <label for="endDate" class="form-label">Coverage End Date</label>
          <input type="date" class="form-control" id="endDate">
        </div>
      </div>

      <div class="mb-3">
        <label for="premiumAmount" class="form-label">Premium Amount</label>
        <input type="number" class="form-control" id="premiumAmount" placeholder="Enter amount">
      </div>

      <div class="mb-3">
        <label for="premiumFrequency" class="form-label">Premium Frequency</label>
        <select class="form-select" id="premiumFrequency">
          <option>Monthly</option>
          <option>Quarterly</option>
          <option>Annually</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="renewalTerms" class="form-label">Renewal Terms</label>
        <textarea class="form-control" id="renewalTerms" rows="2"></textarea>
      </div>

      <div class="mb-3">
        <label for="policyStatus" class="form-label">Policy Status</label>
        <select class="form-select" id="policyStatus">
          <option>Active</option>
          <option>Expired</option>
          <option>Cancelled</option>
        </select>
      </div>

      <button type="submit" class="btn btn-success">Register Policy</button>
    </form>

  </div>
@endsection
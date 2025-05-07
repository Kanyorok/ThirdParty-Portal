@extends('layouts.app')
@section('title', 'provider management')
@section('content')
 <!-- Insurance Provider Form -->
 <div class="card mb-5">
    <div class="card-header">Add Insurance Provider</div>
    <div class="card-body">
      <form method="POST" action="add_provider.php">
        <div class="mb-3">
          <label for="providerName" class="form-label">Provider Name</label>
          <input type="text" class="form-control" id="providerName" name="provider_name" required>
        </div>

        <div class="mb-3">
          <label for="insuranceTypes" class="form-label">Insurance Types Supported</label>
          <select multiple class="form-select" id="insuranceTypes" name="insurance_types[]">
            <option>Property</option>
            <option>Vehicle</option>
            <option>Health</option>
            <option>Life</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="contactInfo" class="form-label">Contact Information</label>
          <textarea class="form-control" id="contactInfo" name="contact_info" rows="3" required></textarea>
        </div>

        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="activeStatus" name="active_status" checked>
          <label class="form-check-label" for="activeStatus">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Add Provider</button>
      </form>
      
      <!-- Link to view the list of insurance providers -->
      <div class="mt-3">
        <a href="{{ route('providermanagement.create') }}" class="btn btn-link">View Insurance Providers</a>
      </div>
    </div>
  </div>

  <!-- Insurance Policy Form -->
  <div class="card">
    <div class="card-header">Register Insurance Policy</div>
    <div class="card-body">
      <form method="POST" action="add_policy.php">
        <div class="mb-3">
          <label for="policyProvider" class="form-label">Provider</label>
          <input type="text" class="form-control" id="policyProvider" name="provider" required>
        </div>

        <div class="mb-3">
          <label for="policyNo" class="form-label">Policy Number</label>
          <input type="text" class="form-control" id="policyNo" name="policy_no" required>
        </div>

        <div class="mb-3">
          <label for="policyType" class="form-label">Policy Type</label>
          <select class="form-select" id="policyType" name="policy_type">
            <option>Property</option>
            <option>Motor</option>
            <option>Health</option>
            <option>Life</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="coveredAsset" class="form-label">Covered Asset</label>
          <input type="text" class="form-control" id="coveredAsset" name="covered_asset" placeholder="e.g. Vehicle ABC123" required>
        </div>

        <div class="row mb-3">
          <div class="col">
            <label for="startDate" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="startDate" name="start_date" required>
          </div>
          <div class="col">
            <label for="endDate" class="form-label">End Date</label>
            <input type="date" class="form-control" id="endDate" name="end_date" required>
          </div>
        </div>

        <div class="mb-3">
          <label for="premiumAmount" class="form-label">Premium Amount</label>
          <input type="number" class="form-control" id="premiumAmount" name="premium_amount" required>
        </div>

        <div class="mb-3">
          <label for="premiumFrequency" class="form-label">Premium Frequency</label>
          <select class="form-select" id="premiumFrequency" name="premium_frequency">
            <option>Monthly</option>
            <option>Quarterly</option>
            <option>Annually</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="renewalTerms" class="form-label">Renewal Terms</label>
          <textarea class="form-control" id="renewalTerms" name="renewal_terms"></textarea>
        </div>

        <div class="mb-3">
          <label for="policyStatus" class="form-label">Status</label>
          <select class="form-select" id="policyStatus" name="status">
            <option>Active</option>
            <option>Expired</option>
            <option>Cancelled</option>
          </select>
        </div>

        <button type="submit" class="btn btn-success">Register Policy</button>
      </form>

      <!-- Link to view the list of insurance policies -->
      <div class="mt-3">
        <a href="{{ route('providermanagement.create') }}" class="btn btn-link">View Insurance Policies</a>
      </div>
    </div>
  </div>
</div>

@endsection
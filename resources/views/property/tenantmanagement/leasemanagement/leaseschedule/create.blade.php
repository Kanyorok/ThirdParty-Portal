@extends('layouts.app')
@section('title', 'Lease Schedule Generator')

@section('content')
  @if (session('error'))
    <script>
      alert("{{ session('error') }}");
    </script>
  @endif

  <div class="container mt-4">
    <h4 class="fw-bold mb-3">📆 Lease Schedule Generator</h4>

    <form action="{{ route('schedulelease.store') }}" method="POST">
      @csrf
      <div class="card shadow">
        <div class="card-header bg-light fw-bold">🧮 Generate Billing Periods</div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <!-- Lease Number Dropdown -->
            <div class="col-md-6">
              <label class="form-label">Select Lease Number<span class="text-danger">*</span></label>
              <select id="lease-select" name="LeaseId" class="form-select" required>
                <option value="">-- Select Lease --</option>
                @foreach ($newleases as $lease)
                  <option value="{{ $lease->Id }}" data-leasenumber="{{ $lease->LeaseNumber }}"
                    data-tenant-id="{{ $lease->Tenant }}" data-tenant-name="{{ $lease->tenant->TenantName ?? 'N/A' }}"
                    data-property-id="{{ $lease->PropertyID }}"
                    data-property-name="{{ $lease->property->PropertyName ?? 'N/A' }}"
                    data-frequency-id="{{ $lease->PaymentFrequency }}"
                    data-frequency-name="{{ $lease->code->Description ?? 'N/A' }}">
                    {{ $lease->LeaseNumber }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Display Selected Lease Number -->
            <div class="col-md-6">
              <label class="form-label">Lease Number<span class="text-danger">*</span></label>
              <input type="text" id="lease-display" class="form-control" readonly>
            </div>

            <!-- Payment Frequency -->
            <div class="col-md-6">
              <label class="form-label">Payment Frequency<span class="text-danger">*</span></label>
              <input type="text" id="frequency-display" class="form-control" readonly>
              <input type="hidden" name="PaymentFrequency" id="frequency-id">
            </div>

            <!-- Tenant Display -->
            <div class="col-md-6">
              <label class="form-label">Tenant<span class="text-danger">*</span></label>
              <input type="text" id="tenant-display" class="form-control" readonly>
              <input type="hidden" name="TenantId" id="tenant-id">
            </div>

            <!-- Property Display -->
            <div class="col-md-6">
              <label class="form-label">Property<span class="text-danger">*</span></label>
              <input type="text" id="property-display" class="form-control" readonly>
              <input type="hidden" name="PropertyId" id="property-id">
            </div>
          </div>

          <!-- Financial and Date Inputs -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Start Date<span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="StartDate" value="2025-05-01" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">End Date<span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="EndDate" value="2026-04-30" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Base Rent (KES)<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="BaseRent" value="25000" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Service Charge (KES)<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="ServiceCharge" value="15000" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Parking Fee (KES)<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="ParkingFee" value="2000" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Other Charges (KES)<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="OtherCharges" value="0" required>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="text-end">
            <button type="submit" class="btn btn-success"
              onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">🧾 Generate
              Schedule</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Auto-fill script -->
  <script>
    document.getElementById('lease-select').addEventListener('change', function() {
      const selected = this.options[this.selectedIndex];

      document.getElementById('lease-display').value = selected.getAttribute('data-leasenumber') || '';
      document.getElementById('tenant-id').value = selected.getAttribute('data-tenant-id') || '';
      document.getElementById('tenant-display').value = selected.getAttribute('data-tenant-name') || '';
      document.getElementById('property-id').value = selected.getAttribute('data-property-id') || '';
      document.getElementById('property-display').value = selected.getAttribute('data-property-name') || '';
      document.getElementById('frequency-id').value = selected.getAttribute('data-frequency-id') || '';
      document.getElementById('frequency-display').value = selected.getAttribute('data-frequency-name') || '';
    });
  </script>
@endsection

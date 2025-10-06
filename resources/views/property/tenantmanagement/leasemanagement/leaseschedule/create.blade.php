@extends('layouts.app')
@section('title', 'Lease Schedule Generator')

@section('content')
  @if (session('error'))
    <script>
      alert("{{ session('error') }}");
    </script>
  @endif

  <div class="container mt-4">
    <form action="{{ route('schedulelease.store') }}" method="POST">
      @csrf
      <div class="card shadow">
        <div class="card-header bg-light fw-bold">Generate Billing Periods</div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <!-- Lease Number Dropdown -->
            <div class="col-md-6">
              <label class="form-label">Select Lease Number<span class="text-danger">*</span></label>
              <select id="lease-select" name="LeaseId" class="form-select" required>
                <option value="">-- Select Lease --</option>
                @foreach ($newleases as $lease)
                  <option value="{{ $lease->Id }}" 
                    data-leasenumber="{{ $lease->LeaseNumber }}"
                    data-tenant-id="{{ $lease->Tenant }}" 
                    data-tenant-name="{{ $lease->tenant->thirdParty->ThirdPartyName ?? 'N/A' }}"
                    data-property-id="{{ $lease->PropertyID }}"
                    data-property-name="{{ $lease->property->PropertyName ?? 'N/A' }}"
                    data-frequency-id="{{ $lease->PaymentFrequency }}"
                    data-frequency-name="{{ $lease->code->Description ?? 'N/A' }}"
                    data-baserent="{{ $lease->MonthlyRent ?? 0 }}"
                    data-servicecharge="{{ $lease->ServiceCharge ?? 0 }}"
                    data-parkingfee="{{ $lease->ParkingFee ?? 0 }}"
                    data-othercharges="{{ $lease->OtherCharges ?? 0 }}">
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
              <label class="form-label">Base Rent<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="BaseRent" id="baserent" value="25000" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Service Charge<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="ServiceCharge" id="servicecharge" value="15000" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Parking Fee<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="ParkingFee" id="parkingfee" value="2000" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Other Charges<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="OtherCharges" id="othercharges" value="0" required>
            </div>
          </div>

          <!-- Total Amount -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Total Amount</label>
              <input type="number" class="form-control" id="total-amount" name="TotalAmount" value="0" readonly>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="text-end">
            <a href="{{ route('schedulelease.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success"
              onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();"> Generate Schedule </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Scripts -->
  <script>
    // Auto-fill lease details
    document.getElementById('lease-select').addEventListener('change', function() {
      const selected = this.options[this.selectedIndex];

      // Lease details
      document.getElementById('lease-display').value = selected.getAttribute('data-leasenumber') || '';
      document.getElementById('tenant-id').value = selected.getAttribute('data-tenant-id') || '';
      document.getElementById('tenant-display').value = selected.getAttribute('data-tenant-name') || '';
      document.getElementById('property-id').value = selected.getAttribute('data-property-id') || '';
      document.getElementById('property-display').value = selected.getAttribute('data-property-name') || '';
      document.getElementById('frequency-id').value = selected.getAttribute('data-frequency-id') || '';
      document.getElementById('frequency-display').value = selected.getAttribute('data-frequency-name') || '';

      // Charges
      document.getElementById('baserent').value = selected.getAttribute('data-baserent') || 0;
      document.getElementById('servicecharge').value = selected.getAttribute('data-servicecharge') || 0;
      document.getElementById('parkingfee').value = selected.getAttribute('data-parkingfee') || 0;
      document.getElementById('othercharges').value = selected.getAttribute('data-othercharges') || 0;

      // Recalculate total
      calculateTotal();
    });

    // Calculate total amount
    function calculateTotal() {
      const baseRent = parseFloat(document.getElementById('baserent').value) || 0;
      const serviceCharge = parseFloat(document.getElementById('servicecharge').value) || 0;
      const parkingFee = parseFloat(document.getElementById('parkingfee').value) || 0;
      const otherCharges = parseFloat(document.getElementById('othercharges').value) || 0;

      const total = baseRent + serviceCharge + parkingFee + otherCharges;
      document.getElementById('total-amount').value = total;
    }

    // Trigger calculation on input change
    document.querySelectorAll('#baserent, #servicecharge, #parkingfee, #othercharges')
      .forEach(input => input.addEventListener('input', calculateTotal));

    // Initial calculation on page load
    window.addEventListener('DOMContentLoaded', calculateTotal);
  </script>
@endsection

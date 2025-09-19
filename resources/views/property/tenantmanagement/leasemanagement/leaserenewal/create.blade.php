@extends('layouts.app')
@section('title', 'Renew Lease Agreement')

@section('content')
  {{-- Error Messages --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Flash Messages --}}
  @if (session('error'))
    <script>alert("{{ session('error') }}");</script>
  @endif

  @if (session('success'))
    <script>alert("{{ session('success') }}");</script>
  @endif

  <div class="container mt-4">
    <form action="{{ route('renewlease.store') }}" method="POST">
      @csrf
      <div class="card shadow">
        <div class="card-header bg-light fw-bold">New Lease Terms</div>
        <div class="card-body">

          {{-- Lease Selection --}}
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Select Lease Number<span class="text-danger">*</span></label>
              <select id="lease-select" name="LeaseId" class="form-select" required>
                <option value="">-- Select Lease --</option>
                @foreach ($newleases as $lease)
                  <option value="{{ $lease->Id }}"
                    data-leasenumber="{{ $lease->LeaseNumber }}"
                    data-tenant-id="{{ $lease->Tenant }}"
                    data-tenant-name="{{ $lease->tenant->thirdParty->TradingName ?? 'N/A' }}"
                    data-property-id="{{ $lease->PropertyID }}"
                    data-property-name="{{ $lease->property->PropertyName ?? 'N/A' }}"
                    data-frequency-id="{{ $lease->PaymentFrequency }}"
                    data-frequency-name="{{ $lease->code->Description ?? 'N/A' }}"
                    data-enddate="{{ $lease->EndDate }}"
                    data-rent="{{ $lease->MonthlyRent }}"
                    data-service="{{ $lease->ServiceCharge }}"
                    data-parking="{{ $lease->ParkingFee }}"
                    data-other="{{ $lease->OtherCharges }}">
                    {{ $lease->LeaseNumber }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Lease Number<span class="text-danger">*</span></label>
              <input type="text" id="lease-display" class="form-control" readonly>
            </div>

            <div class="col-md-6">
              <label class="form-label">Tenant<span class="text-danger">*</span></label>
              <input type="text" id="tenant-display" class="form-control" readonly>
              <input type="hidden" name="TenantId" id="tenant-id">
            </div>

            <div class="col-md-6">
              <label class="form-label">Property<span class="text-danger">*</span></label>
              <input type="text" id="property-display" class="form-control" readonly>
              <input type="hidden" name="PropertyId" id="property-id">
            </div>

            <div class="col-md-6">
              <label class="form-label">Payment Frequency<span class="text-danger">*</span></label>
              <input type="text" id="frequency-display" class="form-control" readonly>
              <input type="hidden" name="PaymentFrequency" id="frequency-id">
            </div>
          </div>

          {{-- Dates --}}
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">End Date of Current Lease<span class="text-danger">*</span></label>
              <input type="date" id="enddate-current" class="form-control"
                name="EndDateCurrentLease"
                value="{{ old('EndDateCurrentLease') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">New Start Date<span class="text-danger">*</span></label>
              <input type="date" class="form-control"
                name="NewStartDate"
                value="{{ old('NewStartDate') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">New End Date<span class="text-danger">*</span></label>
              <input type="date" class="form-control"
                name="NewEndDate"
                value="{{ old('NewEndDate') }}">
            </div>
          </div>

          {{-- Financial Terms --}}
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label">Rent <span class="text-danger">*</span></label>
              <input type="number" id="rent" class="form-control"
                name="NewMonthlyRent"
                value="{{ old('NewMonthlyRent') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Service Charge<span class="text-danger">*</span></label>
              <input type="number" id="service" class="form-control"
                name="ServiceCharge"
                value="{{ old('ServiceCharge') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Parking Fee<span class="text-danger">*</span></label>
              <input type="number" id="parking" class="form-control"
                name="ParkingFee"
                value="{{ old('ParkingFee') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Other Charges<span class="text-danger">*</span></label>
              <input type="number" id="other" class="form-control"
                name="OtherCharges"
                value="{{ old('OtherCharges') }}">
            </div>
          </div>

          {{-- Total --}}
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label fw-bold">Total</label>
              <input type="number" id="total-display" class="form-control bg-light fw-bold" readonly>
            </div>
          </div>

          {{-- Remarks --}}
          <div class="mb-3">
            <label class="form-label">Remarks or Changes</label>
            <textarea class="form-control" name="Remarks" rows="2">{{ old('Remarks') }}</textarea>
          </div>

          {{-- Submit --}}
          <button type="submit" class="btn btn-success"
            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
            Renew Lease
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- Auto-fill Script --}}
  <script>
    const leaseSelect = document.getElementById('lease-select');

    leaseSelect.addEventListener('change', function () {
      const selected = this.options[this.selectedIndex];

      if (!selected.value) return;

      // Basic info
      document.getElementById('lease-display').value = selected.dataset.leasenumber;
      document.getElementById('tenant-id').value = selected.dataset.tenantId;
      document.getElementById('tenant-display').value = selected.dataset.tenantName;
      document.getElementById('property-id').value = selected.dataset.propertyId;
      document.getElementById('property-display').value = selected.dataset.propertyName;
      document.getElementById('frequency-id').value = selected.dataset.frequencyId;
      document.getElementById('frequency-display').value = selected.dataset.frequencyName;

      // Old values
      document.getElementById('enddate-current').value = selected.dataset.enddate || '';
      document.getElementById('rent').value = selected.dataset.rent || 0;
      document.getElementById('service').value = selected.dataset.service || 0;
      document.getElementById('parking').value = selected.dataset.parking || 0;
      document.getElementById('other').value = selected.dataset.other || 0;

      calculateTotal();
    });

    function calculateTotal() {
      let rent = parseFloat(document.getElementById('rent').value) || 0;
      let service = parseFloat(document.getElementById('service').value) || 0;
      let parking = parseFloat(document.getElementById('parking').value) || 0;
      let other = parseFloat(document.getElementById('other').value) || 0;
      document.getElementById('total-display').value = rent + service + parking + other;
    }

    // Attach to inputs
    ['rent', 'service', 'parking', 'other'].forEach(id => {
      document.getElementById(id).addEventListener('input', calculateTotal);
    });

    // Run once at page load
    window.addEventListener('load', calculateTotal);
  </script>
@endsection

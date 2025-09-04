@extends('layouts.app')
@section('title', 'Renew Lease Agreement')

@section('content')
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if (session('error'))
    <script>
      alert("{{ session('error') }}");
    </script>
  @endif

  @if (session('success'))
    <script>
      alert("{{ session('success') }}");
    </script>
  @endif

  <div class="container mt-4">

    <form action="{{ route('renewlease.store') }}" method="POST">
      @csrf
      <div class="card shadow">
        <div class="card-header bg-light fw-bold">New Lease Terms</div>
        <div class="card-body">
          <!-- Lease Selection -->
          <div class="row g-3 mb-3">
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

          <!-- Dates -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">End Date of Current Lease<span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="EndDateCurrentLease" value="EndDateCurrentLease"
                placeholder="22/06/2025">
            </div>
            <div class="col-md-4">
              <label class="form-label">New Start Date<span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="NewStartDate" value="NewStartDate"
                placeholder="22/06/2025">
            </div>
            <div class="col-md-4">
              <label class="form-label">New End Date<span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="NewEndDate" value="NewEndDate" placeholder="22/06/2100">
            </div>
          </div>

          <!-- Financial Terms -->
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label">New Monthly Rent<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="NewMonthlyRent" value="NewMonthlyRent">
            </div>
            <div class="col-md-3">
              <label class="form-label">Service Charge<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="ServiceCharge" value="ServiceCharge">
            </div>
            <div class="col-md-3">
              <label class="form-label">Parking Fee<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="ParkingFee" value="ParkingFee">
            </div>
            <div class="col-md-3">
              <label class="form-label">Other Charges<span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="OtherCharges" value="OtherCharges">
            </div>
          </div>

          <!-- Remarks -->
          <div class="mb-3">
            <label class="form-label">Remarks or Changes</label>
            <textarea class="form-control" name="Remarks" rows="2" placeholder="E.g. rent increased by KES 2,500"></textarea>
          </div>

          <!-- Submit -->
          <button type="submit" class="btn btn-success"
            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Renew Lease</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Auto-fill Script -->
  <script>
    document.getElementById('lease-select').addEventListener('change', function() {
      const selected = this.options[this.selectedIndex];
      document.getElementById('lease-display').value = selected.getAttribute('data-leasenumber');
      document.getElementById('tenant-id').value = selected.getAttribute('data-tenant-id');
      document.getElementById('tenant-display').value = selected.getAttribute('data-tenant-name');
      document.getElementById('property-id').value = selected.getAttribute('data-property-id');
      document.getElementById('property-display').value = selected.getAttribute('data-property-name');
      document.getElementById('frequency-id').value = selected.getAttribute('data-frequency-id');
      document.getElementById('frequency-display').value = selected.getAttribute('data-frequency-name');
    });
  </script>
@endsection

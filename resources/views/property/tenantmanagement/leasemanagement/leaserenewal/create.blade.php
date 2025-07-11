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
    @if(session('error'))
        <script>
            alert("{{ session('error') }}");
        </script>
    @endif
    @if(session('success'))
        <script>
            alert("{{ session('success') }}");
        </script>
    @endif
<div class="container mt-4">

  <h4 class="fw-bold mb-3">🔁 Renew Lease Agreement</h4>

    <form action="{{ route('renewlease.store') }}" method="POST">
        @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📝 New Lease Terms</div>
    <div class="card-body">
        <!-- Select Current Lease -->
      <div class="row g-3 mb-3">
          <div class="col-md-6">
              <label class="form-label">Select Lease Number</label>
              <select id="lease-select" name="LeaseId" class="form-select" required>
                  <option value="">-- Select Lease --</option>
                  @foreach ($newleases as $lease)
                      <option
                          value="{{ $lease->Id }}"
                          data-leasenumber="{{ $lease->LeaseNumber }}"
                          data-tenant-id="{{ $lease->Tenant }}"
                          data-tenant-name="{{ $lease->tenant->TenantName ?? 'N/A' }}"
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
              <label class="form-label">Lease Number</label>
              <input type="text" id="lease-display" class="form-control" readonly>
          </div>

          <!-- Auto-filled Tenant -->
          <div class="col-md-6">
              <label class="form-label">Tenant</label>
              <input type="text" id="tenant-display" class="form-control" readonly>
              <input type="hidden" name="TenantId" id="tenant-id">
          </div>

          <!-- Auto-filled Property -->
          <div class="col-md-6">
              <label class="form-label">Property</label>
              <input type="text" id="property-display" class="form-control" readonly>
              <input type="hidden" name="PropertyId" id="property-id">
          </div>

          <!-- Auto-filled Payment Frequency -->
          <div class="col-md-6">
              <label class="form-label">Payment Frequency</label>
              <input type="text" id="frequency-display" class="form-control" readonly>
              <input type="hidden" name="PaymentFrequency" id="frequency-id">
          </div>
      </div>
        <div class="col-md-3">
            <label class="form-label">End Date of Current Lease</label>
            <input type="date" class="form-control" value="2025-08-31" name="EndDateCurrentLease">
        </div>
        <div class="col-md-3">
            <label class="form-label">New Start Date</label>
            <input type="date" class="form-control" value="2025-09-01" name="NewStartDate">
        </div>
    </div>

      <!-- New Terms -->
      <div class="row g-3 mb-3">
          <div class="col-md-3">
              <label class="form-label">New End Date</label>
              <input type="date" class="form-control" value="2026-08-31" name="NewEndDate">
          </div>
          <div class="col-md-3">
              <label class="form-label">New Monthly Rent</label>
              <input type="number" class="form-control" value="27500" name="NewMonthlyRent">
          </div>

          <div class="mb-3">
              <label class="form-label">Remarks or Changes</label>
              <textarea class="form-control" rows="2" placeholder="E.g. rent increased by KES 2,500"
                        name="Remarks"></textarea>
          </div>
          <button class="btn btn-success">🔁 Renew Lease</button>
      </div>
  </div>
</div>
    <script>
        document.getElementById('lease-select').addEventListener('change', function () {
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

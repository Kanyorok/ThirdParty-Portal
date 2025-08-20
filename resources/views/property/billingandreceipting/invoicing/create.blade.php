@extends('layouts.app')
@section('title', 'Rent Invoice')
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

<div class="container mt-4">
  <h4 class="fw-bold mb-3">🧾 Generate Rent Invoice</h4>

  <form action="{{ route('rentinvoice.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card shadow">
      <div class="card-header bg-light fw-bold">📄 Lease Billing Details</div>
      <div class="card-body">

        <!-- Lease Selection -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Select Lease<span class="text-danger">*</span></label>
            <select name="Lease" class="form-select" required>
              <option value="">-- Select Lease --</option>
              @foreach ($newleases as $newlease)
                <option value="{{ $newlease->Id }}"
                        data-rent="{{ $newlease->MonthlyRent }}"
                        data-service="{{ $newlease->ServiceCharge }}"
                        data-parking="{{ $newlease->ParkingFee }}"
                        data-other="{{ $newlease->OtherCharges }}">
                  LSno: {{ $newlease->LeaseNumber }} — Name: {{ $newlease->tenant->TenantName }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Billing Month<span class="text-danger">*</span></label>
            <input type="month" class="form-control" value="{{ old('BillingMonth', '2025-05') }}" name="BillingMonth">
          </div>

          <div class="col-md-3">
            <label class="form-label">Invoice Date<span class="text-danger">*</span></label>
            <input type="date" class="form-control" value="{{ old('InvoiceDate', '2025-05-01') }}" name="InvoiceDate">
          </div>
        </div>

        <!-- Charges Summary -->
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Rent Amount<span class="text-danger">*</span></label>
            <input type="number" class="form-control" 
                  value="{{ old('RentAmount', 0) }}" 
                  name="RentAmount" step="0.01" min="0" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Service Charge</label>
            <input type="number" class="form-control" 
                  value="{{ old('ServicesCharge', 0) }}" 
                  name="ServicesCharge" step="0.01" min="0">
          </div>
          <div class="col-md-4">
            <label class="form-label">Parking Fee</label>
            <input type="number" class="form-control" 
                  value="{{ old('ParkingFee', 0) }}" 
                  name="ParkingFee" step="0.01" min="0">
          </div>
          <div class="col-md-4">
            <label class="form-label">Other Charges</label>
            <input type="number" class="form-control" 
                  value="{{ old('OtherCharges', 0) }}" 
                  name="OtherCharges" step="0.01" min="0">
          </div>
        </div>

        <!-- Optional Notes -->
        <div class="mb-3">
          <label class="form-label">Invoice Notes</label>
          <textarea class="form-control" rows="2" 
                    placeholder="Optional notes or remarks..."
                    name="InvoiceNotes">{{ old('InvoiceNotes') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">📤 Generate Invoice</button>

      </div>
    </div>
  </form>
</div>

<!-- JavaScript to auto-fill charges -->
<script>
  document.querySelector('select[name="Lease"]').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];

    const rent = parseFloat(selected.dataset.rent || 0).toFixed(2);
    const service = parseFloat(selected.dataset.service || 0).toFixed(2);
    const parking = parseFloat(selected.dataset.parking || 0).toFixed(2);
    const other = parseFloat(selected.dataset.other || 0).toFixed(2);

    document.querySelector('input[name="RentAmount"]').value = rent;
    document.querySelector('input[name="ServicesCharge"]').value = service;
    document.querySelector('input[name="ParkingFee"]').value = parking;
    document.querySelector('input[name="OtherCharges"]').value = other;
  });

  // Ensure formatting on form submit
  document.querySelector('form').addEventListener('submit', function () {
    ['RentAmount', 'ServicesCharge', 'ParkingFee', 'OtherCharges'].forEach(function (name) {
      const input = document.querySelector(`[name="${name}"]`);
      if (input && input.value) {
        input.value = parseFloat(input.value).toFixed(2);
      }
    });
  });
</script>
@endsection

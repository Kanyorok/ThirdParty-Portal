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
    <h4 class="fw-bold mb-3">Generate Rent Invoice</h4>

  <form action="{{ route('rentinvoice.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card shadow">
        <div class="card-header bg-light fw-bold">Lease Billing Details</div>
      <div class="card-body">

        <!-- Lease Selection -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
              <label class="form-label">Select Lease<span class="text-danger">*</span></label>
            <select name="Lease" class="form-select" required>
              <option value="">-- Select Lease --</option>
              @foreach ($newleases as $newlease)
                <option value="{{ $newlease->Id }}"
                        data-rent="{{ $newlease->MonthlyRent ?? 0 }}"
                        data-service="{{ $newlease->ServiceCharge ?? 0 }}"
                        data-parking="{{ $newlease->ParkingFee ?? 0 }}"
                        data-other="{{ $newlease->OtherCharges ?? 0 }}">
                    LSno: {{ $newlease->LeaseNumber }} — Name: {{ $newlease->tenant->thirdParty->ThirdPartyName }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
              <label class="form-label">Billing Month<span class="text-danger">*</span></label>
              <input type="month" class="form-control" value="{{ old('BillingMonth', now()) }}" name="BillingMonth">
          </div>

          <div class="col-md-3">
              <label class="form-label">Invoice Date<span class="text-danger">*</span></label>
              <input type="date" class="form-control" value="{{ old('InvoiceDate', now()) }}" name="InvoiceDate">
          </div>
        </div>

        <!-- Charges Summary -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Rent Amount<span class="text-danger">*</span></label>
            <input type="number" class="form-control"
                  value="{{ old('RentAmount', 0) }}"
                  name="RentAmount" step="0.01" min="0" required>
          </div>
            <div class="col-md-3">
            <label class="form-label">Service Charge</label>
            <input type="number" class="form-control"
                  value="{{ old('ServicesCharge', 0) }}"
                  name="ServicesCharge" step="0.01" min="0">
          </div>
            <div class="col-md-3">
            <label class="form-label">Parking Fee</label>
            <input type="number" class="form-control"
                  value="{{ old('ParkingFee', 0) }}"
                  name="ParkingFee" step="0.01" min="0">
          </div>
            <div class="col-md-3">
            <label class="form-label">Other Charges</label>
            <input type="number" class="form-control"
                  value="{{ old('OtherCharges', 0) }}"
                  name="OtherCharges" step="0.01" min="0">
          </div>
        </div>

          <!-- Total Amount -->
          <div class="row g-3 mb-3">
              <div class="col-md-4">
                  <label class="form-label fw-bold">Total Amount</label>
                  <input type="number" class="form-control bg-light fw-bold"
                         value="{{ old('TotalAmount', 0) }}"
                         name="TotalAmount" step="0.01" min="0" readonly>
              </div>
          </div>

        <!-- Optional Notes -->
        <div class="mb-3">
          <label class="form-label">Invoice Notes</label>
          <textarea class="form-control" rows="2"
                    placeholder="Optional notes or remarks..."
                    name="InvoiceNotes">{{ old('InvoiceNotes') }}</textarea>
        </div>
          <a href="{{ route('rentinvoice.index') }}" class="btn btn-secondary">Back</a>
          <button type="submit" class="btn btn-success"
                  onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Generate Invoice
          </button>

      </div>
    </div>
  </form>
</div>

<!-- JavaScript -->
<script>
    function calculateTotal() {
        const rent = parseFloat(document.querySelector('input[name="RentAmount"]').value) || 0;
        const service = parseFloat(document.querySelector('input[name="ServicesCharge"]').value) || 0;
        const parking = parseFloat(document.querySelector('input[name="ParkingFee"]').value) || 0;
        const other = parseFloat(document.querySelector('input[name="OtherCharges"]').value) || 0;

        const total = (rent + service + parking + other).toFixed(2);
        document.querySelector('input[name="TotalAmount"]').value = total;
    }

    // Auto-fill charges on lease change
  document.querySelector('select[name="Lease"]').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];

      document.querySelector('input[name="RentAmount"]').value = parseFloat(selected.dataset.rent || 0).toFixed(2);
      document.querySelector('input[name="ServicesCharge"]').value = parseFloat(selected.dataset.service || 0).toFixed(2);
      document.querySelector('input[name="ParkingFee"]').value = parseFloat(selected.dataset.parking || 0).toFixed(2);
      document.querySelector('input[name="OtherCharges"]').value = parseFloat(selected.dataset.other || 0).toFixed(2);

      calculateTotal();
  });

    // Recalculate total when inputs change
    ['RentAmount', 'ServicesCharge', 'ParkingFee', 'OtherCharges'].forEach(function (name) {
        document.querySelector(`[name="${name}"]`).addEventListener('input', calculateTotal);
  });

    // Format on submit
  document.querySelector('form').addEventListener('submit', function () {
      ['RentAmount', 'ServicesCharge', 'ParkingFee', 'OtherCharges', 'TotalAmount'].forEach(function (name) {
      const input = document.querySelector(`[name="${name}"]`);
      if (input && input.value) {
        input.value = parseFloat(input.value).toFixed(2);
      }
    });
  });

    // Initial calc
    calculateTotal();
</script>
@endsection

@extends('layouts.app')
@section('title', 'Tenant Payments')
@section('content')
@if(session('error'))
    <script>alert("{{ session('error') }}");</script>
@endif

<div class="container mt-4">
  <p><small>Note: partial payment is applicable</small></p>

  <form action="{{ route('rentreceipt.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="card shadow">
      <div class="card-header bg-light fw-bold">Payment Details</div>
      <div class="card-body">

        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Select Invoice<span class="text-danger">*</span></label>
            <select name="InvoiceID" id="invoice-select" class="form-select" required>
              <option value="">-- Select Invoice --</option>
              @foreach ($invoices as $invoice)
                <option value="{{ $invoice->Id }}"
                  data-invoicenumber="{{ $invoice->InvoiceNumber ?? 'N/A' }}"
                  data-tenantid-name="{{ $invoice->lease->tenant->thirdParty->ThirdPartyName ?? 'N/A' }}"
                  data-billingmonth-name="{{ $invoice->BillingMonth ?? 'N/A' }}"
                  data-invoicedate-display="{{ $invoice->InvoiceDate ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('d/m/Y') : '' }}"
                  data-invoicedate-id="{{ $invoice->InvoiceDate ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('Y-m-d') : '' }}"
                  data-rentamount="{{ $invoice->RentAmount ?? '0' }}"
                  data-servicescharge="{{ $invoice->ServicesCharge ?? '0' }}"
                  data-parkingfee="{{ $invoice->ParkingFee ?? '0' }}"
                  data-othercharges="{{ $invoice->OtherCharges ?? '0' }}">
                  {{ $invoice->InvoiceNumber }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Invoice Number<span class="text-danger">*</span></label>
            <input type="text" id="invoice-display" class="form-control" readonly>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Tenant Name<span class="text-danger">*</span></label>
            <input type="text" id="tenantid-display" class="form-control" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label">Billing Month<span class="text-danger">*</span></label>
            <input type="text" id="billingmonth-display" class="form-control" readonly>
            <input type="hidden" name="BillingMonth" id="billingmonth-id">
          </div>

          <div class="col-md-6">
            <label class="form-label">Invoice Date<span class="text-danger">*</span></label>
            <input type="text" id="invoicedate-display" class="form-control" readonly>
            <input type="hidden" name="InvoiceDate" id="invoicedate-id">
          </div>

          <div class="col-md-6">
            <label class="form-label">Rent Amount<span class="text-danger">*</span></label>
            <input type="number" id="rentamount-display" class="form-control" readonly>
            <input type="hidden" name="RentAmount" id="rentamount-id">
          </div>

          <div class="col-md-6">
            <label class="form-label">Services Charge</label>
            <input type="number" id="servicescharge-display" class="form-control" readonly>
            <input type="hidden" name="ServicesCharge" id="servicescharge-id">
          </div>

          <div class="col-md-6">
            <label class="form-label">Parking Fee</label>
            <input type="number" id="parkingfee-display" class="form-control" readonly>
            <input type="hidden" name="ParkingFee" id="parkingfee-id">
          </div>

          <div class="col-md-6">
            <label class="form-label">Other Charges</label>
            <input type="number" id="othercharges-display" class="form-control" readonly>
            <input type="hidden" name="OtherCharges" id="othercharges-id">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Total Due</label>
            <input type="number" class="form-control" name="TotalDue" id="totaldue" readonly step="0.01">
          </div>
          <div class="col-md-3">
            <label class="form-label">Amount Paid So Far</label>
            <input type="number" name="AmountPaidSoFar" id="amount_paid_so_far" class="form-control" readonly>
          </div>
          <div class="col-md-3">
            <label class="form-label">Balance<span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="Balance" id="balance" readonly step="0.01">
          </div>
          <div class="col-md-3">
            <label class="form-label">Payment Date<span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="PaymentDate" id="paymentdate" required value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Amount Paid Now<span class="text-danger">*</span></label>
            <input type="number" class="form-control" placeholder="e.g. 5000" name="AmountPaidNow" id="amount_paid_now" required min="1">
          </div>
          <div class="col-md-4">
            <label class="form-label">Payment Method<span class="text-danger">*</span></label>
            <select class="form-select" name="PaymentMethod" required>
              <option value="">----Select Payment Method---</option>
              @foreach ($codes as $code)
                <option value="{{ $code->ID }}">{{ $code->Description }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Reference / Receipt No<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="ReferenceNo" placeholder="e.g. MPESA12345" required maxlength="100">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Remarks</label>
          <textarea class="form-control" name="Remarks" rows="2" placeholder="e.g. Paid KES 5,000 - next part due 10th"></textarea>
        </div>

        <button type="submit" class="btn btn-success"
          onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
          Record Payment
        </button>

      </div>
    </div>
  </form>
</div>

<script>
  function updateTotals(amountPaidSoFar = null) {
    const rent = parseFloat(document.getElementById('rentamount-display').value) || 0;
    const service = parseFloat(document.getElementById('servicescharge-display').value) || 0;
    const parking = parseFloat(document.getElementById('parkingfee-display').value) || 0;
    const other = parseFloat(document.getElementById('othercharges-display').value) || 0;

    const totalDue = rent + service + parking + other;
    document.getElementById('totaldue').value = totalDue;

    const paidSoFar = amountPaidSoFar !== null
      ? amountPaidSoFar
      : (parseFloat(document.getElementById('amount_paid_so_far').value) || 0);

    document.getElementById('amount_paid_so_far').value = paidSoFar;
    document.getElementById('balance').value = totalDue - paidSoFar;
  }

  document.getElementById('invoice-select').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];

    document.getElementById('invoice-display').value = selected.getAttribute('data-invoicenumber');
    document.getElementById('tenantid-display').value = selected.getAttribute('data-tenantid-name');
    document.getElementById('billingmonth-display').value = selected.getAttribute('data-billingmonth-name');
    document.getElementById('billingmonth-id').value = selected.getAttribute('data-billingmonth-name');

    // Invoice date
    document.getElementById('invoicedate-display').value = selected.getAttribute('data-invoicedate-display');
    document.getElementById('invoicedate-id').value = selected.getAttribute('data-invoicedate-id');

    // Rent & charges
    document.getElementById('rentamount-display').value = selected.getAttribute('data-rentamount');
    document.getElementById('rentamount-id').value = selected.getAttribute('data-rentamount');
    document.getElementById('servicescharge-display').value = selected.getAttribute('data-servicescharge');
    document.getElementById('servicescharge-id').value = selected.getAttribute('data-servicescharge');
    document.getElementById('parkingfee-display').value = selected.getAttribute('data-parkingfee');
    document.getElementById('parkingfee-id').value = selected.getAttribute('data-parkingfee');
    document.getElementById('othercharges-display').value = selected.getAttribute('data-othercharges');
    document.getElementById('othercharges-id').value = selected.getAttribute('data-othercharges');

    const invoiceId = selected.value;
    if (invoiceId) {
      fetch(`/property/rentreceipt/amount-paid/${invoiceId}`)
        .then(response => response.json())
        .then(data => {
          updateTotals(data.amount_paid);
        });
    } else {
      updateTotals(0);
    }
  });

  // Warn if overpayment
  document.getElementById('amount_paid_now').addEventListener('input', function () {
    const nowPaying = parseFloat(this.value) || 0;
    const totalDue = parseFloat(document.getElementById('totaldue').value) || 0;
    const paidSoFar = parseFloat(document.getElementById('amount_paid_so_far').value) || 0;
    if (nowPaying + paidSoFar > totalDue) {
      alert("Warning: Amount exceeds total due.");
    }
  });
</script>
@endsection

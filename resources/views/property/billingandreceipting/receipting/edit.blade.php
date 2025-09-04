@extends('layouts.app')
@section('title', 'Tenant Payments')
@section('content')
  @if (session('error'))
    <script>
      alert("{{ session('error') }}");
    </script>
  @endif

  <div class="container mt-4">
    <h4 class="fw-bold mb-3">Edit Tenant Payment</h4>

    <form action="{{ route('rentreceipt.update', $receipts->Id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="card shadow">
        <div class="card-header bg-light fw-bold">Payment Details</div>
        <div class="card-body">

          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label">Select Invoice</label>
              <select id="invoice-select" class="form-select" disabled>
                <option value="">-- Select Invoice --</option>
                @foreach ($invoices as $invoice)
                  <option value="{{ $invoice->Id }}" data-invoicenumber="{{ $invoice->InvoiceNumber }}"
                    data-tenantid-name="{{ $invoice->lease->tenant->TenantName }}"
                    data-billingmonth-name="{{ $invoice->BillingMonth }}"
                    data-invoicedate-name="{{ $invoice->InvoiceDate }}" data-invoicedate-id="{{ $invoice->InvoiceDate }}"
                    data-rentamount-name="{{ $invoice->RentAmount }}" data-rentamount-id="{{ $invoice->RentAmount }}"
                    data-servicescharge-name="{{ $invoice->ServicesCharge }}"
                    data-servicescharge-id="{{ $invoice->ServicesCharge }}"
                    data-parkingfee-name="{{ $invoice->ParkingFee }}" data-parkingfee-id="{{ $invoice->ParkingFee }}"
                    data-othercharges-name="{{ $invoice->OtherCharges }}"
                    data-othercharges-id="{{ $invoice->OtherCharges }}"
                    {{ $receipts->InvoiceID == $invoice->Id ? 'selected' : '' }}>
                    {{ $invoice->InvoiceNumber }}
                  </option>
                @endforeach
              </select>
              <input type="hidden" name="InvoiceID" value="{{ $receipts->InvoiceID }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Invoice Number</label>
              <input type="text" id="invoice-display" class="form-control" readonly
                value="{{ optional($receipts->invoice)->InvoiceNumber }}">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Tenant Name</label>
              <input type="text" id="tenantid-display" class="form-control" readonly
                value="{{ optional($receipts->invoice)->lease->tenant->TenantName }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Billing Month</label>
              <input type="text" id="billingmonth-display" class="form-control" readonly
                value="{{ $receipts->BillingMonth }}">
              <input type="hidden" name="BillingMonth" id="billingmonth-id" value="{{ $receipts->BillingMonth }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Invoice Date</label>
              <input type="date" id="invoicedate-display" class="form-control" readonly
                value="{{ $receipts->InvoiceDate }}">
              <input type="hidden" name="InvoiceDate" id="invoicedate-id" value="{{ $receipts->InvoiceDate }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Rent Amount</label>
              <input type="number" id="rentamount-display" class="form-control" readonly
                value="{{ $receipts->RentAmount }}">
              <input type="hidden" name="RentAmount" id="rentamount-id" value="{{ $receipts->RentAmount }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Services Charge</label>
              <input type="number" id="servicescharge-display" class="form-control" readonly
                value="{{ $receipts->ServicesCharge }}">
              <input type="hidden" name="ServicesCharge" id="servicescharge-id" value="{{ $receipts->ServicesCharge }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Parking Fee</label>
              <input type="number" id="parkingfee-display" class="form-control" readonly
                value="{{ $receipts->ParkingFee }}">
              <input type="hidden" name="ParkingFee" id="parkingfee-id" value="{{ $receipts->ParkingFee }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Other Charges</label>
              <input type="number" id="othercharges-display" class="form-control" readonly
                value="{{ $receipts->OtherCharges }}">
              <input type="hidden" name="OtherCharges" id="othercharges-id" value="{{ $receipts->OtherCharges }}">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label">Total Due</label>
              <input type="number" class="form-control" name="TotalDue" id="totaldue" readonly step="0.01"
                value="{{ $receipts->TotalDue }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Amount Paid So Far</label>
              <input type="number" name="AmountPaidSoFar" id="amount_paid_so_far" class="form-control" readonly
                value="{{ $receipts->AmountPaidSoFar }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Balance</label>
              <input type="number" class="form-control" name="Balance" id="balance" readonly step="0.01"
                value="{{ $receipts->Balance }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Payment Date</label>
              <input type="date" class="form-control" name="PaymentDate" required
                value="{{ $receipts->PaymentDate }}">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Amount Paid Now</label>
              <input type="number" class="form-control" name="AmountPaidNow" id="amount_paid_now" required
                min="1" value="{{ $receipts->AmountPaidNow }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">Payment Method</label>
              <select class="form-select" name="PaymentMethod" required>
                <option value="">-- Select Payment Method --</option>
                @foreach ($receipts->code ? [$receipts] : [] as $receipt)
                  <option value="{{ $receipt->PaymentMethod }}" selected>{{ $receipt->code->Description }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Reference / Receipt No</label>
              <input type="text" class="form-control" name="ReferenceNo" placeholder="e.g. MPESA12345" required
                maxlength="100" value="{{ $receipts->ReferenceNo }}">
            </div>
          </div>

          <div class="form-group">
            <label for="Status">Invoice Status</label>
            <select name="Status" id="Status" class="form-control" required>
              @foreach ($statuses as $status)
                <option value="{{ $status->value }}"
                  {{ $receipts->invoice->Status === $status->value ? 'selected' : '' }}>
                  {{ $status->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="Remarks" rows="2">{{ $receipts->Remarks }}</textarea>
          </div>

          <button type="submit" class="btn btn-success">Update Payment</button>
        </div>
      </div>
    </form>
  </div>

  <script>
    document.getElementById('amount_paid_now').addEventListener('input', function() {
      const nowPaying = parseFloat(this.value) || 0;
      const totalDue = parseFloat(document.getElementById('totaldue').value) || 0;
      const paidSoFar = parseFloat(document.getElementById('amount_paid_so_far').value) || 0;
      if (nowPaying + paidSoFar > totalDue) {
        alert("Warning: Amount exceeds total due.");
      }
    });
  </script>
@endsection

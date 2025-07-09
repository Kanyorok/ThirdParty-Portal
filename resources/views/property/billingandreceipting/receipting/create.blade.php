@extends('layouts.app')
@section('title', 'Tenant Payments')
@section('content')
@if(session('error'))
    <script>
        alert("{{ session('error') }}");
    </script>
@endif
<div class="container mt-4">
  <h4 class="fw-bold mb-3">💳 Record Tenant Payment (Supports Partials)</h4>

    <form action="{{ route('rentreceipt.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🧾 Payment Details</div>
    <div class="card-body">
      <!-- Select Invoice -->
      <div class="row g-3 mb-3">
          <div class="col-md-3">
              <label class="form-label">InvoiceID</label>
              <select name="InvoiceID"id="invoice-select" class="form-select" required>
                <option value="">-- Select InvoiceID --</option>
                  @foreach ($invoices as $invoice)
                      <option value="{{ $invoice->Id }}"                
                    
                                    data-invoicenumber="{{ $invoice->InvoiceNumber ?? 'N/A' }}"
                                    data-tenantid-id="{{ $invoice->TenantId ?? 'N/A' }}" 
                                    data-tenantid-name="{{ $invoice->TenantId ?? 'N/A' }}" 
                                    data-billingmonth-id="{{ $invoice->BillingMonth ?? 'N/A'}}"
                                    data-billingmonth-name="{{ $invoice->BillingMonth ?? 'N/A' }}"    
                                    data-invoicedate-id="{{ $invoice->InvoiceDate ?? 'N/A' }}"
                                    data-invoicedate-name="{{ $invoice->InvoiceDate ?? 'N/A' }}"
                                    data-rentamount-id="{{ $invoice->RentAmount ?? 'N/A' }}"
                                    data-rentamount-name="{{ $invoice->RentAmount ?? 'N/A' }}"
                                    data-servicescharge-id="{{ $invoice->ServicesCharge ?? '0' }}"
                                    data-servicescharge-name="{{ $invoice->ServicesCharge ?? '0' }}"                                  
                                    data-OtherCharges-id="{{ $invoice->OtherCharges ?? 'N/A' }}"
                                    data-OtherCharges-name="{{ $invoice->OtherCharges ?? 'N/A' }}">
                                    {{ $invoice->InvoiceNumber }}</option>
                  @endforeach
              </select>
          </div>
            <div class="col-md-6">
            <label class="form-label">InvoiceID</label>
            <input type="text" id="invoice-display" class="form-control" readonly>    
          </div>
          
          </div>
            <div class="col-md-6">
            <label class="form-label">TenantId</label>
          <input type="text" id="tenantid-display" class="form-control" readonly>
          <input type="hidden" name="TenantId" id="tenantid-id">
        </div>

        <div class="col-md-6">
          <label class="form-label">Billing Month</label>
          <input type="text" id="billingmonth-display" class="form-control" readonly>
          <input type="hidden" name="BillingMonth" id="billingmonth-id">
        </div>

        <div class="col-md-6">
          <label class="form-label">Invoice Date</label>
          <input type="date" id="invoicedate-display" class="form-control" readonly>
          <input type="hidden" name="InvoiceDate" id="invoicedate-id"> 
        </div>

        <div class="col-md-6">
          <label class="form-label">Rent Amount</label>
          <input type="number" id="rentamount-display" class="form-control" readonly>
          <input type="hidden" name="RentAmount" id="rentamount-id">
        </div>

        <div class="col-md-6">
          <label class="form-label">Services Charge</label>
          <input type="number" id="servicescharge-display" class="form-control" readonly>
          <input type="hidden" name="ServicesCharge" id="servicescharge-id">
        </div>

        <div class="col-md-6">
          <label class="form-label">Other Charges</label>
          <input type="number" id="OtherCharges-display" class="form-control" readonly>
          <input type="hidden" name="OtherCharges" id="OtherCharges-id">
        </div>

      <!-- Invoice Summary (Static example, should populate dynamically) -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Total Due</label>
          <input type="number" class="form-control" name="TotalDue" id="totaldue" readonly step="0.01">
        </div>
        <div class="col-md-3">
          <label class="form-label">Amount Paid So Far</label>
            <input type="number" class="form-control" value="10000" name="AmountPaid">
        </div>
        <div class="col-md-3">
          <label class="form-label">Balance</label>
            <input type="number" class="form-control" name="Balance" id="balance" readonly step="0.01">
        </div>
        <div class="col-md-3">
          <label class="form-label">Payment Date</label>
            <input type="date" class="form-control" value="2025-05-03" name="PaymentDate">
        </div>
      </div>

      <!-- Payment Entry -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Amount Paid Now</label>
            <input type="number" class="form-control" placeholder="e.g. 5000"  name="Amount">
        </div>
        <div class="col-md-4">
          <label class="form-label">Payment Method</label>
            <select class="form-select" name="PaymentMethod">
            <option>MPESA</option>
            <option>Bank Transfer</option>
            <option>Cash</option>
            <option>Cheque</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Reference / Receipt No</label>
            <input type="text" class="form-control" placeholder="e.g. MPESA12345" name="ReferenceNo">
        </div>
      </div>

      <!-- Optional Notes -->
      <div class="mb-3">
        <label class="form-label">Remarks</label>
          <textarea class="form-control" rows="2" placeholder="e.g. Paid KES 5,000 - next part due 10th"
                    name="Remarks"></textarea>
      </div>
          <button class="btn btn-success">💾 Record Payment</button>
    </form>
    </div>
  </div>
</div>
<script>
    function updateTotals() {
        const rent = parseFloat(document.getElementById('rentamount-display').value) || 0;
        const service = parseFloat(document.getElementById('servicescharge-display').value) || 0;
        const other = parseFloat(document.getElementById('OtherCharges-display').value) || 0;
        const totalDue = rent + service + other;
        document.getElementById('totaldue').value = totalDue;

        const amountPaid = parseFloat(document.getElementsByName('AmountPaid')[0].value) || 0;
        document.getElementById('balance').value = totalDue - amountPaid;
    }

    document.getElementById('invoice-select').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];

        document.getElementById('invoice-display').value = selected.getAttribute('data-invoicenumber');
        document.getElementById('tenantid-id').value = selected.getAttribute('data-tenantid-id');
        document.getElementById('tenantid-display').value = selected.getAttribute('data-tenantid-id');
        document.getElementById('billingmonth-id').value = selected.getAttribute('data-billingmonth-name');
        document.getElementById('billingmonth-display').value = selected.getAttribute('data-billingmonth-name');
        document.getElementById('invoicedate-id').value = selected.getAttribute('data-invoicedate-id');
        document.getElementById('invoicedate-display').value = selected.getAttribute('data-invoicedate-name');
        document.getElementById('rentamount-id').value = selected.getAttribute('data-rentamount-id');
        document.getElementById('rentamount-display').value = selected.getAttribute('data-rentamount-name');
        document.getElementById('servicescharge-id').value = selected.getAttribute('data-servicescharge-id');
        console.log("Service Charge Value: ", document.getElementById('servicescharge-id').value);
        document.getElementById('servicescharge-display').value = selected.getAttribute('data-servicescharge-name');
        document.getElementById('OtherCharges-id').value = selected.getAttribute('data-OtherCharges-id');
        document.getElementById('OtherCharges-display').value = selected.getAttribute('data-OtherCharges-name');

        updateTotals();
    });

    // Update balance when Amount Paid So Far changes
    document.getElementsByName('AmountPaid')[0].addEventListener('input', updateTotals);
</script>
@endsection

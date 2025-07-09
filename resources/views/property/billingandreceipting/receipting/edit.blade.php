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

    <form action="{{ route('rentreceipt.update', $receipts->Id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card shadow">
            <div class="card-header bg-light fw-bold">🧾 Payment Details</div>
            <div class="card-body">
                <!-- Select Invoice -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Invoice ID</label>
                        <select name="InvoiceID" id="invoice-select" class="form-select" required>
                            <option value="">-- Select InvoiceID --</option>
                            @foreach ($invoices as $invoice)
                                <option value="{{ $invoice->Id }}"
                                    data-invoicenumber="{{ $invoice->InvoiceNumber ?? 'N/A' }}"
                                    data-tenantid-id="{{ $invoice->TenantId ?? 'N/A' }}" 
                                    data-tenantid-name="{{ $invoice->TenantId ?? 'N/A' }}"
                                    data-billingmonth-id="{{ $invoice->BillingMonth ?? 'N/A' }}"
                                    data-billingmonth-name="{{ $invoice->BillingMonth ?? 'N/A' }}"
                                    data-invoicedate-id="{{ $invoice->InvoiceDate ?? 'N/A' }}"
                                    data-invoicedate-name="{{ $invoice->InvoiceDate ?? 'N/A' }}"
                                    data-rentamount-id="{{ $invoice->RentAmount ?? 'N/A' }}"
                                    data-rentamount-name="{{ $invoice->RentAmount ?? 'N/A' }}"
                                    data-servicescharge-id="{{ $invoice->ServicesCharge ?? '0' }}"
                                    data-servicescharge-name="{{ $invoice->ServicesCharge ?? '0' }}"
                                    data-OtherCharges-id="{{ $invoice->OtherCharges ?? 'N/A' }}"
                                    data-OtherCharges-name="{{ $invoice->OtherCharges ?? 'N/A' }}"
                                    @if(old('InvoiceID', $receipts->InvoiceID) == $invoice->Id) selected @endif
                                >
                                    {{ $invoice->InvoiceNumber }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                <div class="col-md-6">
                    <label class="form-label">TenantId</label>
                        <input type="text" id="tenantid-display" class="form-control" readonly>
                        <input type="hidden" name="TenantId" id="tenantid-id"
                               value="{{ old('TenantId', $receipts->TenantId ?? '') }}">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Billing Month</label>
                        <input type="text" id="billingmonth-display" class="form-control" readonly>
                        <input type="hidden" name="BillingMonth" id="billingmonth-id"
                               value="{{ old('BillingMonth', $receipts->BillingMonth ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Invoice Date</label>
                        <input type="date" id="invoicedate-display" class="form-control" readonly>
                        <input type="hidden" name="InvoiceDate" id="invoicedate-id"
                               value="{{ old('InvoiceDate', $receipts->InvoiceDate ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Rent Amount</label>
                        <input type="number" id="rentamount-display" class="form-control" readonly>
                        <input type="hidden" name="RentAmount" id="rentamount-id"
                               value="{{ old('RentAmount', $receipts->RentAmount ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Services Charge</label>
                        <input type="number" id="servicescharge-display" class="form-control" readonly>
                        <input type="hidden" name="ServicesCharge" id="servicescharge-id"
                               value="{{ old('ServicesCharge', $receipts->ServicesCharge ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Other Charges</label>
                        <input type="number" id="OtherCharges-display" class="form-control" readonly>
                        <input type="hidden" name="OtherCharges" id="OtherCharges-id"
                               value="{{ old('OtherCharges', $receipts->OtherCharges ?? '') }}">
                    </div>
                </div>

                <!-- Invoice Summary -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Total Due</label>
                        <input type="number" class="form-control" name="TotalDue" id="totaldue" readonly
                               value="{{ old('TotalDue', $receipts->TotalDue ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount Paid So Far</label>
                        <input type="number" class="form-control" name="AmountPaid"
                               value="{{ old('AmountPaid', $receipts->AmountPaid ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Balance</label>
                        <input type="number" class="form-control" name="Balance" id="balance" readonly
                               value="{{ old('Balance', $receipts->Balance ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" name="PaymentDate"
                               value="{{ old('PaymentDate', \Carbon\Carbon::parse($receipts->PaymentDate)->format('Y-m-d')) }}">
                    </div>
                </div>

                <!-- Payment Entry -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Amount Paid Now</label>
                        <input type="number" class="form-control" name="Amount"
                               value="{{ old('Amount', $receipts->Amount ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" name="PaymentMethod">
                            <option {{ $receipts->PaymentMethod == 'MPESA' ? 'selected' : '' }}>MPESA</option>
                            <option {{ $receipts->PaymentMethod == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option {{ $receipts->PaymentMethod == 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option {{ $receipts->PaymentMethod == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reference / Receipt No</label>
                        <input type="text" class="form-control" name="ReferenceNo"
                               value="{{ old('ReferenceNo', $receipts->ReferenceNo ?? '') }}">
                    </div>
                </div>

                <!-- Remarks -->
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" rows="2" name="Remarks">{{ old('Remarks', $receipts->Remarks ?? '') }}</textarea>
                </div>

                <button type="submit" class="btn btn-success">💾 Update Payment</button>
                <a href="{{ route('rentreceipt.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
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
        document.getElementById('tenantid-id').value = selected.getAttribute('data-tenantid-id');
        document.getElementById('tenantid-display').value = selected.getAttribute('data-tenantid-id');
        document.getElementById('billingmonth-id').value = selected.getAttribute('data-billingmonth-id');
        document.getElementById('billingmonth-display').value = selected.getAttribute('data-billingmonth-name');
        document.getElementById('invoicedate-id').value = selected.getAttribute('data-invoicedate-id');
        document.getElementById('invoicedate-display').value = selected.getAttribute('data-invoicedate-name');
        document.getElementById('rentamount-id').value = selected.getAttribute('data-rentamount-id');
        document.getElementById('rentamount-display').value = selected.getAttribute('data-rentamount-name');
        document.getElementById('servicescharge-id').value = selected.getAttribute('data-servicescharge-id');
        document.getElementById('servicescharge-display').value = selected.getAttribute('data-servicescharge-name');
        document.getElementById('OtherCharges-id').value = selected.getAttribute('data-OtherCharges-id');
        document.getElementById('OtherCharges-display').value = selected.getAttribute('data-OtherCharges-name');
        updateTotals();
    });

    document.getElementsByName('AmountPaid')[0].addEventListener('input', updateTotals);

    // Trigger change on load to populate data
    window.addEventListener('DOMContentLoaded', function () {
        const invoiceSelect = document.getElementById('invoice-select');
        if (invoiceSelect.value) {
            invoiceSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
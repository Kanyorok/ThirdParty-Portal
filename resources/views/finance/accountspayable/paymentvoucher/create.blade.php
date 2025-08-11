@extends('layouts.app')
@section('title', 'Create Payment Voucher')
 
@section('content')
<div class="container mt-2">
    <div class="card shadow-rounded-4">
        <div class="card-header bg-light py-1 px-3">
            <h6 class="mb-0 text-muted"><i class="fas fa-file-invoice-dollar text-info"></i>Voucher</h6>
        </div>
        <div class="card-body">
            <p class="text-muted">Fill in the details below to create a new payment voucher.</p>
            @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
            @endif
            <form action="{{route('paymentvoucher.store')}}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label" for="vouchernumber">Voucher Number</label>
                        <input type="text" id="VoucherNo" name="VoucherNo" class="form-control" value="{{$VoucherNo}}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="invoiceref">Invoice Ref</label>
                        <select name="InvoiceNo" id="InvoiceNo" class="form-select" onchange="fillInvoiceAmount()">
                            <option value="">-- Select Invoice --</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->Id }}" data-amount="{{ $invoice->InvoiceAmount }}">
                                    {{ $invoice->InvoiceNumber }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="totalamount">Total Invoice Amount</label>
                        <input type="number" class="form-control mt-2" id="TotAmnt" placeholder="Based on the selected invoce" readonly>
                    </div>
                </div>
        
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label" for="paymentmethood">Payment Method</label>
                        <select class="form-control" name="PaymentMethod">
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="paymenttype">Payment Type</label>
                        <select class="form-control" name="PaymentType" id="paymentType" onchange="toggleSchedule()">
                            <option disabled selected value="">--Select Payment Type--</option>
                            <option value="Full">Full</option>
                            <option value="Partial">Partial</option>
                            <option value="Scheduled">Scheduled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="amntpaid">Amount</label>
                        <input type="number" class="form-control mt-2" name="TotAmnt" placeholder="e.g. 1000.00" required>
                    </div>
                </div>
        
                <div id="scheduleOptions" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="startdate">Start Date</label>
                            <input type="date" class="form-control" name="StartDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="frequency">Frequency</label>
                            <select class="form-control" name="Frequency">
                                <option value="Monthly">Monthly</option>
                                <option value="Biweekly">Biweekly</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Custom">Custom</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" name="Description" rows="3" placeholder="Enter Voucher Description"></textarea>
                </div>
        
                <div class="text-end">
                    <a href="{{ route('paymentvoucher.index') }}" class="btn btn-secondary me-2">Back</a>
                    <button class="btn btn-success" onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='💾 Saving....'; this.form.submit();}">💾  Save Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>
 
<script>
  function toggleSchedule() {
    const type = document.getElementById('paymentType').value;

    if(type === 'Scheduled') {
        document.getElementById('scheduleOptions').style.display = 'block';
    }else{
        document.getElementById('scheduleOptions').style.display = 'none';
    }
}

    // document.addEventListener('DOMContentLoaded', function() {
    //     const rand = Math.floor(Math.random() * 90000)+ 10000;
    //     document.getElementById('VoucherNo').value = 'VCH-2025-' + rand;
    // });

    function fillInvoiceAmount() {
        const selectedOption = document.querySelector('#InvoiceNo option:checked');
        const amount = selectedOption.getAttribute('data-amount');

        document.getElementById('TotAmnt').value = amount ? parseFloat(amount).toFixed(2) : '';
    }

</script>
@endsection
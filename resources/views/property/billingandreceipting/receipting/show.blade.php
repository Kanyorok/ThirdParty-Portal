@extends('layouts.app')
@section('title', 'Property Invoice Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Property Invoice Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">

                    <dt class="col-sm-4">InvoiceID</dt>
                    <dd class="col-sm-8">{{ $receipt->InvoiceID ?? '_' }}</dd>

                    <dt class="col-sm-4">Billing Month</dt>
                    <dd class="col-sm-8">{{ $receipt->BillingMonth ?? '_' }}</dd>

                    <dt class="col-sm-4">Invoice Date</dt>
                    <dd class="col-sm-8">{{ $receipt->InvoiceDate ?? '_' }}</dd>

                    <dt class="col-sm-4">Rent Amount</dt>
                    <dd class="col-sm-8">{{ $receipt->RentAmount ?? '_' }}</dd>

                    <dt class="col-sm-4">Total Due</dt>
                    <dd class="col-sm-8">{{ $receipt->TotalDue ?? '_' }}</dd>

                    <dt class="col-sm-4">Amount Paid</dt>
                    <dd class="col-sm-8">{{ $receipt->AmountPaid ?? '_' }}</dd>

                    <dt class="col-sm-4">Balance</dt>
                    <dd class="col-sm-8">{{ $receipt->Balance ?? '_' }}</dd>

                    <dt class="col-sm-4">Payment Date</dt>
                    <dd class="col-sm-8">{{ $receipt->PaymentDate ?? '_' }}</dd>

                    <dt class="col-sm-4">Amount</dt>
                    <dd class="col-sm-8">{{ $receipt->Amount ?? '_' }}</dd>

                    <dt class="col-sm-4">Payment Method</dt>
                    <dd class="col-sm-8">{{ $receipt->PaymentMethod ?? '_' }}</dd>

                    <dt class="col-sm-4">Reference Number</dt>
                    <dd class="col-sm-8">{{ $receipt->ReferenceNo ?? '_' }}</dd>

                    <dt class="col-sm-4">Remarks</dt>
                    <dd class="col-sm-8">{{ $receipt->Remarks ?? '_' }}</dd>

                </dl>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-primary">Edit</a>
                <a href="{{ route('rentreceipt.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection

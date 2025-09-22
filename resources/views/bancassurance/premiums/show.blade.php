@extends('layouts.app')
@section('title', 'Premium Payment Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Policy Number</dt>
                    <dd class="col-sm-8">{{ $payment->policies->PolicyNumber ?? '-' }}</dd>

                    <dt class="col-sm-4">Customer Name</dt>
                    <dd class="col-sm-8">{{ $payment->CustomerID ?? '-' }}</dd>

                    <dt class="col-sm-4">Payment Frequency</dt>
                    <dd class="col-sm-8">{{ $payment->PaymentFrequency ?? '-' }}</dd>

                    <dt class="col-sm-4">Payment Date</dt>
                    <dd class="col-sm-8">
                        {{ $payment->PaymentDate ? \Carbon\Carbon::parse($payment->PaymentDate)->format('d M Y') : '-' }}
                    </dd>

                    <dt class="col-sm-4">Next Payment Date</dt>
                    <dd class="col-sm-8">
                        {{ $payment->NextPaymentDate ? \Carbon\Carbon::parse($payment->NextPaymentDate)->format('d M Y') : '-' }}
                    </dd>

                    <dt class="col-sm-4">Amount</dt>
                    <dd class="col-sm-8">{{ number_format($payment->Amount, 2) }}</dd>

                    <dt class="col-sm-4">Payment Mode</dt>
                    <dd class="col-sm-8">{{ $payment->paymentModes->Description ?? '-' }}</dd>

                    <dt class="col-sm-4">Reference Number</dt>
                    <dd class="col-sm-8">{{ $payment->ReferenceNumber ?? '-' }}</dd>

                    <dt class="col-sm-4">Notes</dt>
                    <dd class="col-sm-8">{{ $payment->Notes ?? '-' }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection

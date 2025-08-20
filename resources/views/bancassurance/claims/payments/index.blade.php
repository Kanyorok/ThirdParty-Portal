@extends('layouts.app')
@section('title', 'Claim Payments')

@section('content')
<div class="container mt-4">
    <h4>💸 Claim Payments</h4>

    <a href="{{ route('bancassurance.claims.payments.initiate') }}" class="btn btn-primary mb-3">➕ Initiate Payment</a>

    @if($payments->count())
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Policy</th>
                <th>Customer</th>
                <th>Claim Type</th>
                <th>Paid Amount (KES)</th>
                <th>Payment Date</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $pay)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $pay->PolicyNumber }}</td>
                <td>{{ $pay->CustomerName }}</td>
                <td>{{ $pay->ClaimType }}</td>
                <td>{{ $pay->FormattedAmount }}</td>
                <td>{{ \Carbon\Carbon::parse($pay->PaymentDate)->format('Y-m-d') }}</td>
                <td>{{ $pay->PaymentReference }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p class="text-muted">No payments recorded yet.</p>
    @endif
</div>
@endsection

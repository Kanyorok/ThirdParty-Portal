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
                <th>Paid Amount</th>
                <th>Payment Date</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $pay)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $pay->claim->policy->PolicyNumber }}</td>
                <td>{{ $pay->claim->policy->customer->FullName }}</td>
                <td>{{ $pay->claim->claimtype->Description }}</td>
                <td>{{ $pay->PaymentAmount }}</td>
                <td>{{ \Carbon\Carbon::parse($pay->PaymentDate)->format('d/m/Y') }}</td>
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

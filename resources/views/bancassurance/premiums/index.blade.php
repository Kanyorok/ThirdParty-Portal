@extends('layouts.app')
@section('title', 'Premium Payments')

@section('content')
<div class="container mt-4">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>💳 Premium Payments</h4>

    {{-- Optional: dropdown to select policy to record premium for --}}
    <a href="{{ route('bancassurance.premiums.create') }}" class="btn btn-primary">
        ➕ Record Premium Payment
    </a>
</div>

    <table class="table table-striped table-bordered mt-3">
        <thead>
            <tr>
                <th>#</th>
                <th>Policy Number</th>
                <th>Customer</th>
                <th>Payment Date</th>
                <th>Amount</th>
                <th>Mode</th>
                <th>Reference</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $payment->PolicyNumber }}</td>
                <td>{{ $payment->CustomerName }}</td>
                <td>{{ \Carbon\Carbon::parse($payment->PaymentDate)->format('d M Y') }}</td>
                <td>{{ number_format($payment->Amount, 2) }}</td>
                <td>{{ $payment->PaymentMode }}</td>
                <td>{{ $payment->ReferenceNumber }}</td>
                <td>{{ $payment->Notes }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No premium payments recorded yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

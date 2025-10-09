@extends('layouts.app')
@section('title', 'Settled Claims')

@section('content')
    <div class="container mt-4">
        <h4>💳 Settled Claims</h4>
        <a href="{{ route('bancassurance.claims.payments.create') }}" class="btn btn-success mb-3">
            ➕ Initiate Payment
        </a>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Policy No.</th>
                <th>Customer</th>
                <th>Claim Type</th>
                <th>Approved Amount</th>
                <th>Paid Amount</th>
                <th>Payment Ref</th>
                <th>Payment Date</th>
            </tr>
            </thead>
            <tbody>
            @forelse($payments as $p)
                <tr>
                    <td>{{ $p->Id ?? '-'}}</td>
                    <td>{{ $p->PolicyNumber ?? '-'}}</td>
                    <td>{{ $p->CustomerName ?? '-'}}</td>
                    <td>{{ $p->ClaimType ?? '-'}}</td>
                    <td>{{ number_format($p->ApprovedAmount, 2) }}</td>
                    <td>{{ number_format($p->PaymentAmount, 2) }}</td>
                    <td>{{ $p->PaymentReference }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->PaymentDate)->format('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-muted text-center">No payments recorded yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection

@extends('layouts.app')
@section('title', 'Commission Payout History')

@section('content')
    <div class="container mt-4">
        <h4>📜 Commission Payout History</h4>

        @if($payouts->isEmpty())
            <div class="alert alert-info">No commission payouts have been recorded yet.</div>
        @else
            <table class="table table-bordered">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Policy Number</th>
                    <th>Beneficiary</th>
                    <th>Earned Amount</th>
                    <th>Paid Amount</th>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Mode</th>
                    <th>Remarks</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payouts as $p)
                    <tr>
                        <td>{{ $p->Id }}</td>
                        <td>{{ $p->PolicyNumber }}</td>
                        <td>{{ $p->EarnedByType }} #{{ $p->EarnedByID }}</td>
                        <td>{{ number_format($p->EarnedAmount, 2) }}</td>
                        <td>{{ number_format($p->PaidAmount, 2) }}</td>
                        <td>{{ $p->PayoutReference }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->PaymentDate)->format('d M Y') }}</td>
                        <td>{{ $p->PaymentMode }}</td>
                        <td>{{ $p->Remarks }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

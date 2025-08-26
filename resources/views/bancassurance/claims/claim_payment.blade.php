@extends('layouts.app')
@section('title', 'Settle Claim')

@section('content')
    <div class="container mt-4">
        <h4>💰 Settle Claim – #{{ $claim->Id }} | Policy: {{ $claim->PolicyNumber }}</h4>

        <form method="POST" action="{{ route('bancassurance.claims.settle.store', $claim->Id) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Approved Amount</label>
                <input type="text" class="form-control" value="{{ number_format($claim->ApprovedAmount, 2) }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Date</label>
                <input type="date" name="PaymentDate" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Amount Paid (KES)</label>
                <input type="number" name="PaymentAmount" class="form-control" step="0.01" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Reference</label>
                <input type="text" name="PaymentReference" class="form-control" placeholder="e.g. EFT123456">
            </div>

            <div class="mb-3">
                <label class="form-label">Notes (optional)</label>
                <textarea name="Notes" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">💸 Record Payment</button>
        </form>
    </div>
@endsection

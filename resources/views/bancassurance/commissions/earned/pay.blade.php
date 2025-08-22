@extends('layouts.app')
@section('title', 'Initiate Commission Payout')

@section('content')
    <div class="container mt-4">
        <h4>💰 Initiate Payout for Policy #{{ $commission->PolicyNumber }}</h4>

        <form method="POST" action="{{ route('bancassurance.commissions.earned.storePayout', $commission->Id) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Payout Reference</label>
                <input type="text" name="PayoutReference" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Amount (KES)</label>
                <input type="text" name="PaidAmount" class="form-control" value="{{ $commission->EarnedAmount }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Date</label>
                <input type="date" name="PaymentDate" class="form-control" value="{{ now()->format('Y-m-d') }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Mode</label>
                <input type="text" name="PaymentMode" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea name="Remarks" class="form-control" rows="2"></textarea>
            </div>

            <div class="text-end">
                <button class="btn btn-success">✅ Submit Payout</button>
            </div>
        </form>
    </div>
@endsection

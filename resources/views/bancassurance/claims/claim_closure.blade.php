@extends('layouts.app')
@section('title', 'Close Claim')

@section('content')
<div class="container mt-4">
    <h4>🔒 Close Claim – Ref #{{ $claim->Id }}</h4>

    <div class="mb-3">
        <strong>Policy Number:</strong> {{ $claim->PolicyNumber }}<br>
        <strong>Customer:</strong> {{ $claim->CustomerName }}<br>
        <strong>Claim Type:</strong> {{ $claim->ClaimType }}<br>
        <strong>Approved Amount:</strong> KES {{ number_format($claim->ApprovedAmount, 2) }}
    </div>

    <form method="POST" action="{{ route('bancassurance.claims.storeClosure', $claim->Id) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Closure Status</label>
            <select name="ClosureStatus" class="form-select" required>
                <option value="">-- Select Status --</option>
                <option value="Closed - Paid">Closed - Paid</option>
                <option value="Closed - Cancelled">Closed - Cancelled</option>
                <option value="Closed - Withdrawn">Closed - Withdrawn</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Closure Remarks</label>
            <textarea name="Remarks" class="form-control" rows="3" placeholder="Optional remarks..."></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Closure Date</label>
            <input type="date" name="ClosureDate" class="form-control" required value="{{ date('Y-m-d') }}">
        </div>

        <button type="submit" class="btn btn-primary">🔒 Submit Closure</button>
    </form>
</div>
@endsection

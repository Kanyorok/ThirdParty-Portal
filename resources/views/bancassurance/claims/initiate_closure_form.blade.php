@extends('layouts.app')
@section('title', 'Initiate Claim Closure')

@section('content')
<div class="container mt-4">
    <h4>🛑 Initiate Claim Closure</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('bancassurance.claims.storeClosureFromList') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Select Claim to Close</label>
            <select name="ClaimID" class="form-select" required>
                <option value="">-- Choose Claim --</option>
                @foreach($claims as $claim)
                    <option value="{{ $claim->Id }}">
                        [#{{ $claim->Id }}] {{ $claim->ClaimType }} – Policy {{ $claim->PolicyNumber }} – {{ $claim->CustomerName }} (KES {{ number_format($claim->ApprovalAmount, 2) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Closure Status</label>
            <select name="ClosureStatus" class="form-select" required>
                <option value="">-- Select Status --</option>
                <option value="Successfully Closed">Successfully Closed</option>
                <option value="Rejected at Closure">Rejected at Closure</option>
                <option value="Escalated">Escalated</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Closure Date</label>
            <input type="date" name="ClosureDate" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="Remarks" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">✅ Close Claim</button>
            <a href="{{ route('bancassurance.claims.closed') }}" class="btn btn-secondary">⬅️ Back to Closed Claims</a>
        </div>
    </form>
</div>
@endsection

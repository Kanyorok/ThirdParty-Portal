@extends('layouts.app')
@section('title', 'Claim Approval')

@section('content')
<div class="container mt-4">
    <h4>✅ Approve Claim – Policy #{{ $claim->PolicyNumber }}</h4>

    <div class="mb-3">
        <strong>Claim Amount:</strong> KES {{ $claim->FormattedClaimAmount }}<br>
        <strong>Assessed Amount:</strong> KES {{ $claim->FormattedAssessedAmount ?? 'Not Assessed' }}
    </div>

    @if($documents && count($documents) > 0)
    <div class="mb-3">
        <strong>Attached Documents:</strong>
        <ul>
            @foreach($documents as $doc)
                <li><a href="{{ asset('storage/' . $doc->FilePath) }}" target="_blank">{{ $doc->DocumentName }}</a></li>
            @endforeach
        </ul>
    </div>
    @else
        <p class="text-muted">No documents uploaded.</p>
    @endif

    <form method="POST" action="{{ route('bancassurance.claims.approveStore', $claim->Id) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Approval Decision</label>
            <select name="Decision" class="form-select" required>
                <option value="">-- Select Decision --</option>
                <option value="Approved">Approve</option>
                <option value="Rejected">Reject</option>
                <option value="More Info Needed">More Info Needed</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Approved Amount</label>
            <input type="number" name="ApprovalAmount" step="0.01" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Approval Date</label>
            <input type="date" name="ApprovalDate" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="Remarks" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">✅ Approve Claim</button>
    </form>
</div>
@endsection

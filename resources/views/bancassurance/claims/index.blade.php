@extends('layouts.app')
@section('title', 'Claims Listing')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>🗂️ Insurance Claims Register</h4>
        <a href="{{ route('bancassurance.claims.create') }}" class="btn btn-primary">
            ➕ Initiate New Claim
        </a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Policy No.</th>
                <th>Claim Type</th>
                <th>Claim Reason</th>
                <th>Amount (KES)</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th> {{-- Added column --}}
            </tr>
        </thead>
        <tbody>
            @forelse ($claims as $claim)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $claim->PolicyNumber }}</td>
                <td>{{ $claim->ClaimType }}</td>
                <td>{{ $claim->ClaimReason }}</td>
                <td>{{ number_format($claim->ClaimAmount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($claim->ClaimDate)->format('d M Y') }}</td>
                <td>
                    <span class="badge 
                        @if($claim->Status == 'Initiated') bg-warning
                        @elseif($claim->Status == 'Under Assessment') bg-info
                        @elseif($claim->Status == 'Approved') bg-success
                        @elseif($claim->Status == 'Rejected') bg-danger
                        @elseif($claim->Status == 'Paid') bg-primary
                        @endif">
                        {{ $claim->Status }}
                    </span>
                </td>
                <td>
                    @if($mode === 'assessment')
                        <a href="{{ route('bancassurance.claims.assessForm', $claim->Id) }}" class="btn btn-sm btn-outline-info">
                            📝 Assess
                        </a>
                    @else
                        <a href="{{ route('bancassurance.claims.documents', $claim->Id) }}" class="btn btn-sm btn-outline-secondary">
                            📂 Documents
                        </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">No claims found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mb-3 text-end">
<a href="{{ route('bancassurance.claims.closed') }}" class="btn btn-outline-secondary">
    📁 View Closed Claims
</a>
</div>
@endsection

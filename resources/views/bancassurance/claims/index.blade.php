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
                <td>{{ $claim->policy->PolicyNumber }}</td>
                <td>{{ $claim->claimtype->Description }}</td>
                <td>{{ $claim->ClaimReason }}</td>
                <td>{{ number_format($claim->ClaimAmount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($claim->ClaimDate)->format('d/m/Y') }}</td>
                <td>
                    <span class="#">
                        {{ $claim->status->Description}}
                    </span>
                </td>
                <td><a href="{{ route('bancassurance.claims.assessForm', $claim->Id) }}" class="btn btn-sm btn-outline-info">Assess</a></td>
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

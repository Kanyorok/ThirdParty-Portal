@extends('layouts.app')
@section('title', 'Claims Awaiting Approval')

@section('content')
<div class="container mt-4">
    <h4>✅ Claims Awaiting Approval</h4>

    <table class="table table-bordered table-striped mt-3">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Policy No.</th>
                <th>Customer</th>
                <th>Type</th>
                <th>Reason</th>
                <th>Amount (KES)</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($claims as $claim)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $claim->PolicyNumber }}</td>
                <td>{{ $claim->CustomerName }}</td>
                <td>{{ $claim->ClaimType }}</td>
                <td>{{ $claim->ClaimReason }}</td>
                <td>{{ number_format($claim->ClaimAmount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($claim->ClaimDate)->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('bancassurance.claims.approveForm', $claim->Id) }}" class="btn btn-sm btn-outline-success">
                        ✅ Approve
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">No claims pending approval.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

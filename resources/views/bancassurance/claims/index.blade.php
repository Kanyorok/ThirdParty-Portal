@extends('layouts.app')
@section('title', 'Claims Listing')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Insurance Claims Register</h4>
        <a href="{{ route('bancassurance.claims.create') }}" class="btn btn-primary">Initiate New Claim</a>
    </div>

    <table class="table table-bordered table-striped" id="claim">
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
            @endforelse
        </tbody>
    </table>
</div>
<div class="mb-3 text-end">
<a href="{{ route('bancassurance.claims.closed') }}" class="btn btn-outline-secondary">View Closed Claims</a>
</div>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#claim').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
@endsection

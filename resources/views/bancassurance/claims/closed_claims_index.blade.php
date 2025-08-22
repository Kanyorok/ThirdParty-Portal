@extends('layouts.app')
@section('title', 'Closed Claims')

@section('content')
    <div class="container mt-4">
        <div class="container mt-4">
            <h4>✅ Closed Claims</h4>

            <div class="mb-3 text-end">
                <a href="{{ route('bancassurance.claims.initiateClosureForm') }}" class="btn btn-primary">
                    ➕ Initiate Closure
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif


            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($closedClaims->isEmpty())
                <p class="text-muted">No closed claims found.</p>
            @else
                <table class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Claim Type</th>
                        <th>Policy Number</th>
                        <th>Customer</th>
                        <th>Approved Amount</th>
                        <th>Closure Status</th>
                        <th>Closure Date</th>
                        <th>Remarks</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($closedClaims as $claim)
                        <tr>
                            <td>{{ $claim->Id }}</td>
                            <td>{{ $claim->ClaimType }}</td>
                            <td>{{ $claim->PolicyNumber }}</td>
                            <td>{{ $claim->CustomerName }}</td>
                            <td>KES {{ number_format($claim->ApprovalAmount, 2) }}</td>
                            <td>{{ $claim->ClosureStatus }}</td>
                            <td>{{ \Carbon\Carbon::parse($claim->ClosureDate)->format('d M Y') }}</td>
                            <td>{{ $claim->Remarks }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
@endsection

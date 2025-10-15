@extends('layouts.app')
@section('title', 'Closed Claims')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
 <div class="container mt-4">

    <div class="mb-3 text-end">
        <a href="{{ route('bancassurance.claims.initiateClosureForm') }}" class="btn btn-primary">Initiate Closure</a>
    </div>
    <p><small>This is a list of all closed claims</small></p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

        <table class="table table-bordered" id="claimclosed">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Claim Type</th>
                    <th>Policy Number</th>
                    <th>Customer</th>
                    <th>Approved Amount</th>
                    <th>Closure Status</th>
                    <th>Closure Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($closedClaims as $claim)
                <tr>
                    <td>{{ $claim->Id ?? '-'}}</td>
                    <td>{{ $claim->claim->claimtype->Description ?? '-'}}</td>
                    <td>{{ $claim->claim->policy->PolicyNumber ?? '-'}}</td>
                    <td>{{ $claim->claim->policy->customer->ThirdParty->ThirdPartyName ?? '-'}}</td>
                    <td>{{ number_format($claim->paidamount->PaymentAmount, 2) }}</td>
                    <td>{{ $claim->FinalStatus->label() }}</td>
                    <td>{{ \Carbon\Carbon::parse($claim->ClosureDate)->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
</div>
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#claimclosed').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
@endsection

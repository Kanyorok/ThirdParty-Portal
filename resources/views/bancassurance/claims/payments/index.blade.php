@extends('layouts.app')
@section('title', 'Claim Payments')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    <a href="{{ route('bancassurance.claims.payments.initiate') }}" class="btn btn-primary mb-3">Initiate Payment</a>

    <table class="table table-bordered" id="claimpayment">
        <thead>
            <tr>
                <th>#</th>
                <th>Policy</th>
                <th>Customer</th>
                <th>Claim Type</th>
                <th>Paid Amount</th>
                <th>Payment Date</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $pay)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $pay->claim->policy->PolicyNumber ?? '-'}}</td>
                <td>{{ $pay->claim->policy->customer->FullName ?? '-'}}</td>
                <td>{{ $pay->claim->claimtype->Description ?? '-'}}</td>
                <td>{{ $pay->PaymentAmount ?? '-'}}</td>
                <td>{{ \Carbon\Carbon::parse($pay->PaymentDate)->format('d/m/Y') }}</td>
                <td>{{ $pay->PaymentReference }}</td>
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
            $('#claimpayment').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
@endsection

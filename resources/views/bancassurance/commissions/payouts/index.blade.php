@extends('layouts.app')
@section('title', 'Commission Payout History')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection


@section('content')
<div class="container mt-4">
    <p><small>The list below Consists of commission payouts made</small></p>
        <table class="table table-bordered" id="payout">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Policy Number</th>
                    <th>Paid Amount</th>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Mode</th>
                    <th>Remarks</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payouts as $p)
                    <tr>
                        <td>{{ $p->Id }}</td>
                        <td>{{ $p->policies->PolicyNumber ?? '-'}}</td>
                        <td>{{ number_format($p->PaidAmount, 2) ?? '-'}}</td>
                        <td>{{ $p->PayoutReference ?? '-'}}</td>
                        <td>{{ \Carbon\Carbon::parse($p->PaymentDate)->format('d/m/Y') ?? '-'}}</td>
                        <td>{{ $p->paymentmodes->Description ?? '-'}}</td>
                        <td>{{ $p->Remarks ?? '-'}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#payout').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection

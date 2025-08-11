@extends('layouts.app')
@section('title', 'Payment Processing - Accounts Payable')
@section('content')
<div class="container mt-5">
    <h2>Payment Processing - Accounts Payable</h2>
    <a href="{{ route('paymentprocessing.create') }}" class="btn btn-primary mb-3">Schedule/Make Payment</a>

    <h4 class="mt-4">Scheduled Payments</h4>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Vendor</th>
                <th>Invoice No.</th>
                <th>Amount (Ksh)</th>
                <th>Scheduled Date</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>ABC Supplies Ltd</td>
                <td>INV-2025-01</td>
                <td>75,000</td>
                <td>2025-05-20</td>
                <td>Scheduled after delivery verification</td>
            </tr>
            <tr>
                <td>2</td>
                <td>XYZ Logistics</td>
                <td>INV-2025-11</td>
                <td>48,500</td>
                <td>2025-05-25</td>
                <td>Payment after GRN approval</td>
            </tr>
        </tbody>
    </table>

    <h4 class="mt-5">Completed Payments</h4>
    <table class="table table-bordered table-striped">
        <thead class="table-success">
            <tr>
                <th>#</th>
                <th>Vendor</th>
                <th>Invoice No.</th>
                <th>Amount (Ksh)</th>
                <th>Paid On</th>
                <th>Method</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Global Tech Ltd</td>
                <td>INV-2025-03</td>
                <td>150,000</td>
                <td>2025-05-01</td>
                <td>Bank Transfer</td>
                <td>Quarterly license renewal</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Quick Supplies</td>
                <td>INV-2025-07</td>
                <td>30,000</td>
                <td>2025-05-03</td>
                <td>Cheque</td>
                <td>Advance for office supplies</td>
            </tr>
        </tbody>
    </table>
</div>

@endsection

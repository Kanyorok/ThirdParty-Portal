@extends('layouts.app')
@section('title', 'Invoice Entry - Accounts Payable')
@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Invoice List - Accounts Payable</h2>

    <div class="mb-3 text-end">
        <a href="{{ route('invoiceentry.create') }}" class="btn btn-primary">Add Invoice</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Vendor</th>
                <th>Invoice Number</th>
                <th>Invoice Date</th>
                <th>Due Date</th>
                <th>Amount (Ksh)</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>ABC Supplies Ltd.</td>
                <td>INV123456</td>
                <td>2025-05-01</td>
                <td>2025-05-31</td>
                <td>12,000</td>
                <td>Unpaid</td>
                <td>Office supplies</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Global Tech Ltd.</td>
                <td>GT98765</td>
                <td>2025-04-15</td>
                <td>2025-05-15</td>
                <td>25,000</td>
                <td>Paid</td>
                <td>Software licenses</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Express Logistics</td>
                <td>EXL202504</td>
                <td>2025-04-28</td>
                <td>2025-05-28</td>
                <td>18,500</td>
                <td>Partially Paid</td>
                <td>Freight services</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

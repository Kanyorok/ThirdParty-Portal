@extends('layouts.app')
@section('title', 'Customer Master- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <h2>Customer Master - Accounts Receivable</h2>
    <a href="{{ route('customermaster.create') }}" class="btn btn-primary mb-3">Add New Customer</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Customer Code</th>
                <th>Contact Person</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Credit Limit</th>
                <th>Payment Terms</th>
            </tr>
        </thead>
        <tbody>
            <!-- Sample data for now -->
            <tr>
                <td>1</td>
                <td>Acme Supplies Ltd</td>
                <td>CUST001</td>
                <td>Jane Wanjiru</td>
                <td>jane@acme.co.ke</td>
                <td>0712345678</td>
                <td>500,000</td>
                <td>Net 30</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Blue Ocean Traders</td>
                <td>CUST002</td>
                <td>Peter Kamau</td>
                <td>peter@blueocean.co.ke</td>
                <td>0722456789</td>
                <td>250,000</td>
                <td>Cash on Delivery</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

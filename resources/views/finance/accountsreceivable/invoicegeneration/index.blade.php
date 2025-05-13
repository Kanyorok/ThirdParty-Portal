@extends('layouts.app')
@section('title', 'Invoice Generation Accounts Receivable')
@section('content')
<div class="container mt-5">
    <h2>Invoices - Accounts Receivable</h2>
    <a href="{{ route('invoicegeneration.create') }}" class="btn btn-primary mb-3">Generate New Invoice</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Invoice Number</th>
                <th>Invoice Date</th>
                <th>Customer</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price (Ksh)</th>
                <th>Total Amount (Ksh)</th>
                <th>Payment Terms</th>
            </tr>
        </thead>
        <tbody>
            <!-- Static sample data -->
            <tr>
                <td>1</td>
                <td>INV001</td>
                <td>2025-05-07</td>
                <td>Acme Supplies Ltd</td>
                <td>Delivery of office furniture</td>
                <td>10</td>
                <td>15,000</td>
                <td>150,000</td>
                <td>Net 30</td>
            </tr>
            <tr>
                <td>2</td>
                <td>INV002</td>
                <td>2025-05-06</td>
                <td>Blue Ocean Traders</td>
                <td>Monthly maintenance service</td>
                <td>1</td>
                <td>25,000</td>
                <td>25,000</td>
                <td>Cash on Delivery</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

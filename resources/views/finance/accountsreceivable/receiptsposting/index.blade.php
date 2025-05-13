@extends('layouts.app')
@section('title', 'Receipts Posting- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Receipts Posting - Collections</h2>
        <a href="{{ route('receiptsposting.create') }}" class="btn btn-primary">Post New Receipt</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Receipt Date</th>
                <th>Customer</th>
                <th>Invoice No.</th>
                <th>Amount Received</th>
                <th>Payment Method</th>
                <th>Reference</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Static sample data -->
            <tr>
                <td>2025-05-07</td>
                <td>ABC Traders</td>
                <td>INV-1001</td>
                <td>25,000</td>
                <td>Bank Transfer</td>
                <td>BTX12345</td>
                <td>Payment for April supplies</td>
                <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
            <tr>
                <td>2025-05-06</td>
                <td>XYZ Limited</td>
                <td>INV-0998</td>
                <td>15,000</td>
                <td>Mobile Money</td>
                <td>MPESA5678</td>
                <td>Final installment</td>
                <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

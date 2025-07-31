@extends('layouts.app')
@section('title', 'Processed Payments')
 
@section('content')
<div class="card shadow p-4 rounded-4">
    <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('paymentprocessing.create') }}" class="btn btn-success"><i class="fab fa-wpforms text-info"></i> Process Payments</a>
   </div>
    <h4 class="mb-4">💳 Payments Processed</h4>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Payment ID</th>
                <th>Voucher No</th>
                <th>Supplier</th>
                <th>Amount Paid</th>
                <th>Payment Method</th>
                <th>Bank</th>
                <th>Payment Date</th>
                <th>Narration</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>#P-0001</td>
                <td>VCH-2025-0001</td>
                <td>ABC Suppliers Ltd</td>
                <td>KES 50,000.00</td>
                <td>Bank Transfer</td>
                <td>KCB Main Account</td>
                <td>2025-07-29</td>
                <td>First installment payment</td>
            </tr>
            <tr>
                <td>#P-0002</td>
                <td>VCH-2025-0002</td>
                <td>XYZ Traders</td>
                <td>KES 75,000.00</td>
                <td>Cheque</td>
                <td>Equity Payments</td>
                <td>2025-07-29</td>
                <td>Final payment for services</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
 
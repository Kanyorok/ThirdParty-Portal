@extends('layouts.app')
@section('title', 'Payment Vouchers')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">📋 Payment Vouchers List</h4>
   <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('paymentvoucher.create') }}" class="btn btn-success">➕ Add Voucher</a>
   </div>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Voucher No</th>
                <th>Supplier</th>
                <th>Invoice Ref</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Scheduled</th>
                <th>Created On</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>VCH-2025-0001</td>
                <td>ABC Suppliers Ltd</td>
                <td>INV-2025-0145</td>
                <td>KES 100,000.00</td>
                <td><span class="badge bg-warning">Pending</span></td>
                <td><span class="badge bg-info">No</span></td>
                <td>2025-07-28</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-success">Process Payment</a>
                </td>
            </tr>
            <tr>
                <td>VCH-2025-0002</td>
                <td>XYZ Traders</td>
                <td>INV-2025-0148</td>
                <td>KES 75,000.00</td>
                <td><span class="badge bg-success">Approved</span></td>
                <td><span class="badge bg-info">Yes</span></td>
                <td>2025-07-27</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-success">Process Payment</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Payment Voucher - Accounts Payable')
@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Payment Vouchers</h2>
    <a href="{{ route('paymentvoucher.create') }}" class="btn btn-success mb-3">Create New Voucher</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Voucher No</th>
                <th>Date</th>
                <th>Payee</th>
                <th>Amount</th>
                <th>Payment Mode</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Static rows -->
            <tr>
                <td>PV001</td>
                <td>2025-05-01</td>
                <td>ABC Suppliers Ltd</td>
                <td>10,000.00</td>
                <td>Bank Transfer</td>
                <td>Office supplies</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">Edit</a>
                    <button class="btn btn-sm btn-danger" onclick="alert('Static data - delete action disabled')">Delete</button>
                </td>
            </tr>
            <tr>
                <td>PV002</td>
                <td>2025-05-03</td>
                <td>XYZ Transport</td>
                <td>2,500.00</td>
                <td>Cheque</td>
                <td>Logistics payment</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">Edit</a>
                    <button class="btn btn-sm btn-danger" onclick="alert('Static data - delete action disabled')">Delete</button>
                </td>
            </tr>
            <tr>
                <td>PV003</td>
                <td>2025-05-06</td>
                <td>Jane Wanjiru</td>
                <td>800.00</td>
                <td>Mobile Money</td>
                <td>Reimbursement</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">Edit</a>
                    <button class="btn btn-sm btn-danger" onclick="alert('Static data - delete action disabled')">Delete</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

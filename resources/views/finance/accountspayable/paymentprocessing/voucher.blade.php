@extends('layouts.app')
@section('title', 'Payment Voucher')
@section('content')

    <div class="container mt-4">

        <div class="d-flex justify-content-between mb-4">
            <h4>📄 Payment Voucher</h4>
            <button class="btn btn-outline-secondary">Print</button>
        </div>

        <!-- Header Section -->
        <div class="row mb-3">
            <div class="col-md-4"><strong>Voucher No:</strong> PV-2025-001</div>
            <div class="col-md-4"><strong>Payment Date:</strong> 2025-06-24</div>
            <div class="col-md-4"><strong>Status:</strong> Approved</div>
            <div class="col-md-4"><strong>Vendor:</strong> ABC Supplies Ltd</div>
            <div class="col-md-4"><strong>Payment Method:</strong> Bank Transfer</div>
            <div class="col-md-4"><strong>Bank Account:</strong> 1010 – KCB</div>
        </div>

        <!-- Invoice Reference Table -->
        <h6>Invoices Paid</h6>
        <table class="table table-bordered mb-4">
            <thead>
            <tr>
                <th>Invoice No.</th>
                <th>Invoice Date</th>
                <th>Amount (KSH)</th>
                <th>Remarks</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>INV-2025-01</td>
                <td>2025-06-10</td>
                <td>75,000</td>
                <td>Delivery of office supplies</td>
            </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="row mb-4">
            <div class="col-md-4 offset-md-8">
                <table class="table table-sm">
                    <tr>
                        <th>Total Paid:</th>
                        <td><strong>KES 75,000.00</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Approval Section -->
        <h6>Authorization</h6>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Prepared By</th>
                <th>Checked By</th>
                <th>Approved By</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>John M. – Finance Officer</td>
                <td>Agnes K. – Accountant</td>
                <td>Samuel O. – Finance Manager</td>
            </tr>
            </tbody>
        </table>

    </div>
@endsection

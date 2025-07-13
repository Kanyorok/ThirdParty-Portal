@extends('layouts.app')
@section('title', 'AR Aging Drilldown')
@section('content')

    <div class="container mt-4">
        <h4>📄 Unpaid Invoices – {{ request('customer') }} ({{ strtoupper(request('bucket')) }} Days)</h4>

        <table class="table table-bordered table-hover mt-3">
            <thead class="table-dark">
            <tr>
                <th>Invoice No</th>
                <th>Date</th>
                <th>Due Date</th>
                <th>Amount</th>
                <th>Outstanding</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>AR-INV-2025-004</td>
                <td>2025-05-20</td>
                <td>2025-06-20</td>
                <td>50,000.00</td>
                <td>18,000.00</td>
                <td><span class="badge bg-warning">Partially Paid</span></td>
            </tr>
            <tr>
                <td>AR-INV-2025-006</td>
                <td>2025-04-10</td>
                <td>2025-05-10</td>
                <td>30,000.00</td>
                <td>30,000.00</td>
                <td><span class="badge bg-danger">Unpaid</span></td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection

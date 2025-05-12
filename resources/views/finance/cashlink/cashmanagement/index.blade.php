@extends('layouts.app')
@section('title', 'Cash Management')
@section('content')

<div class="container mt-5">
    <h2>Cash Management</h2>
    <a href="{{route('cashmanagement.create')}} " class="btn btn-primary mb-3">Add Cash Entry</a>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Recipient</th>
                <th>Amount</th>
                <th>Purpose</th>
                <th>Payment Method</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>2025-05-01</td>
                <td>James Kariuki</td>
                <td>15,000</td>
                <td>Office Supplies</td>
                <td>Cash</td>
                <td>Stationery restock</td>
            </tr>
            <tr>
                <td>2</td>
                <td>2025-05-02</td>
                <td>Mary Njeri</td>
                <td>8,500</td>
                <td>Fuel Refund</td>
                <td>Mobile Money</td>
                <td>Fuel reimbursement</td>
            </tr>
            <tr>
                <td>3</td>
                <td>2025-05-03</td>
                <td>Daniel Otieno</td>
                <td>20,000</td>
                <td>Client Entertainment</td>
                <td>Bank Transfer</td>
                <td>Dinner with clients</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
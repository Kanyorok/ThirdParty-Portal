@extends('layouts.app')
@section('title', 'Customer Statement- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <h2>Customer Statements</h2>
    <a href="{{ route('customerstatement.create') }}" class="btn btn-success mb-3">Generate New Statement</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>From Date</th>
                <th>To Date</th>
                <th>Type</th>
                <th>Date Generated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>ABC Distributors</td>
                <td>2025-04-01</td>
                <td>2025-04-30</td>
                <td>Summary</td>
                <td>2025-05-01</td>
                <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
            </tr>
            <tr>
                <td>2</td>
                <td>XYZ Enterprises</td>
                <td>2025-04-01</td>
                <td>2025-04-30</td>
                <td>Detailed</td>
                <td>2025-05-01</td>
                <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
            </tr>
            <tr>
                <td>3</td>
                <td>Global Traders</td>
                <td>2025-03-01</td>
                <td>2025-03-31</td>
                <td>Summary</td>
                <td>2025-04-01</td>
                <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

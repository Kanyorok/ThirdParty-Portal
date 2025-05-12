@extends('layouts.app')
@section('title', 'Aging Report- Accounts Payable')
@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Aging Report - Outstanding Liabilities</h2>
        <a href="{{ route('agingreport.create') }}" class="btn btn-success">Generate New Report</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Report Date</th>
                <th>Vendor</th>
                <th>Currency</th>
                <th>0–30 Days</th>
                <th>31–60 Days</th>
                <th>61–90 Days</th>
                <th>90+ Days</th>
                <th>Total Due</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>2025-05-01</td>
                <td>ABC Supplies</td>
                <td>KES</td>
                <td>40,000</td>
                <td>30,000</td>
                <td>25,000</td>
                <td>25,000</td>
                <td>120,000</td>
                <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
            <tr>
                <td>2025-05-01</td>
                <td>XYZ Ltd</td>
                <td>KES</td>
                <td>20,000</td>
                <td>10,000</td>
                <td>15,000</td>
                <td>20,000</td>
                <td>65,000</td>
                <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

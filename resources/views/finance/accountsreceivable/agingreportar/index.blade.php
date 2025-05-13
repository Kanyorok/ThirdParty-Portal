@extends('layouts.app')
@section('title', 'Aging Report- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <h2>Accounts Receivable Aging Report</h2>
    <a href="{{ route('agingreportar.create') }}" class="btn btn-success mb-3">Generate New Report</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Report Date</th>
                <th>0-30 Days</th>
                <th>31-60 Days</th>
                <th>61-90 Days</th>
                <th>90+ Days</th>
                <th>Total Outstanding</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>ABC Distributors</td>
                <td>2025-05-01</td>
                <td>25,000</td>
                <td>10,000</td>
                <td>5,000</td>
                <td>2,500</td>
                <td>42,500</td>
            </tr>
            <tr>
                <td>2</td>
                <td>XYZ Enterprises</td>
                <td>2025-05-01</td>
                <td>18,000</td>
                <td>7,500</td>
                <td>3,000</td>
                <td>1,200</td>
                <td>29,700</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Global Traders</td>
                <td>2025-05-01</td>
                <td>12,000</td>
                <td>4,000</td>
                <td>1,000</td>
                <td>3,500</td>
                <td>20,500</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

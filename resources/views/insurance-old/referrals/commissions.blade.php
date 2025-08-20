@extends('layouts.app')
@section('title', 'My Commission Statement')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">💼 My Commission Statement</h4>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Policy No</th>
                <th>Client Name</th>
                <th>Product</th>
                <th>Premium</th>
                <th>Commission Rate</th>
                <th>Commission Earned</th>
                <th>Payment Status</th>
                <th>Date Earned</th>
            </tr>
        </thead>
        <tbody>
            {{-- Static rows for now --}}
            <tr>
                <td>1</td>
                <td>POL-202507001</td>
                <td>Jane Njeri</td>
                <td>Credit Life</td>
                <td>KES 5,000</td>
                <td>5%</td>
                <td>KES 250</td>
                <td><span class="badge bg-success">Paid</span></td>
                <td>2025-07-10</td>
            </tr>
            <tr>
                <td>2</td>
                <td>POL-202507003</td>
                <td>John Mbugua</td>
                <td>Property Cover</td>
                <td>KES 12,000</td>
                <td>5%</td>
                <td>KES 600</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td>2025-07-11</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

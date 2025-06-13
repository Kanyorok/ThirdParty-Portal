@extends('layouts.app')
@section('title', 'Driver Projections')
@section('content')
    <div class="card mt-4">

        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('budgetprojections.create') }}" class="btn btn-success btn-sm">+ New Mapping</a>
        </div>
        <div class="card-header bg-secondary text-white">📊 KPI Driver Projections</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Period</th>
                    <th>Branch</th>
                    <th>Product</th>
                    <th>KPI Type</th>
                    <th>Projected Amount</th>
                    <th>Growth Rate (%)</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>FY2025-Q1</td>
                    <td>Main Branch</td>
                    <td>Personal Loan</td>
                    <td>Loan</td>
                    <td>10,000,000</td>
                    <td>6.50%</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>FY2025-Q1</td>
                    <td>Westlands Branch</td>
                    <td>Fixed Deposit</td>
                    <td>Deposit</td>
                    <td>12,500,000</td>
                    <td>4.00%</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

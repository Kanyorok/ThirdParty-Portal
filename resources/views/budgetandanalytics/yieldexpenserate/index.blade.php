@extends('layouts.app')
@section('title', 'Driver Rates')
@section('content')
    <div class="card mt-4">

        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('yieldexpenserate.create') }}" class="btn btn-success btn-sm">+ New Rate</a>
        </div>
        <div class="card-header bg-secondary text-white">📈 Loan Yield / Interest Expense Rates</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Period</th>
                    <th>Product</th>
                    <th>Rate Type</th>
                    <th>Rate Value</th>
                    <th>Effective Date</th>
                    <th>Source</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>FY2025-Q1</td>
                    <td>Personal Loan</td>
                    <td>LoanYield</td>
                    <td>11.00%</td>
                    <td>2025-01-01</td>
                    <td>CBS</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>FY2025-Q1</td>
                    <td>Fixed Deposit</td>
                    <td>InterestExpense</td>
                    <td>6.00%</td>
                    <td>2025-01-01</td>
                    <td>Manual</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

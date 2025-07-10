@extends('layouts.app')
@section('title', 'Income Statement')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📉 Income Statement (Profit & Loss)</h4>

        <!-- Filter Form -->
        <form method="GET" action="#" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="FromDate" class="form-control" value="2025-06-01">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="ToDate" class="form-control" value="2025-06-30">
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select name="Branch" class="form-select">
                    <option value="">All</option>
                    <option value="001">001 - HQ</option>
                    <option value="002">002 - Nairobi</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select name="Department" class="form-select">
                    <option value="">All</option>
                    <option value="100">100 - Finance</option>
                    <option value="200">200 - HR</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </div>
        </form>

        <!-- Income Statement Table -->
        <table class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>Category</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><strong>Income</strong></td>
                <td></td>
            </tr>
            <tr>
                <td class="ps-4">Sales Revenue</td>
                <td class="text-end">120,000.00</td>
            </tr>
            <tr>
                <td class="ps-4">Interest Income</td>
                <td class="text-end">30,000.00</td>
            </tr>
            <tr>
                <td><strong>Total Income</strong></td>
                <td class="text-end fw-bold">150,000.00</td>
            </tr>

            <tr>
                <td><strong>Expenses</strong></td>
                <td></td>
            </tr>
            <tr>
                <td class="ps-4">Rent</td>
                <td class="text-end">50,000.00</td>
            </tr>
            <tr>
                <td class="ps-4">Utilities</td>
                <td class="text-end">10,000.00</td>
            </tr>
            <tr>
                <td><strong>Total Expenses</strong></td>
                <td class="text-end fw-bold">60,000.00</td>
            </tr>

            <tr class="table-info">
                <td><strong>Net Profit</strong></td>
                <td class="text-end fw-bold">90,000.00</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection

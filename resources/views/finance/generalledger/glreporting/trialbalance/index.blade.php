@extends('layouts.app')
@section('title', 'Trial Balance Report')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📊 Trial Balance Report</h4>

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

        <!-- Trial Balance Table -->
        <table class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>1000</td>
                <td>Cash & Bank</td>
                <td>150,000.00</td>
                <td>0.00</td>
            </tr>
            <tr>
                <td>2000</td>
                <td>Accounts Payable</td>
                <td>0.00</td>
                <td>60,000.00</td>
            </tr>
            <tr>
                <td>5000</td>
                <td>Rent Expense</td>
                <td>50,000.00</td>
                <td>0.00</td>
            </tr>
            <tr class="fw-bold">
                <td colspan="2">Total</td>
                <td>200,000.00</td>
                <td>60,000.00</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection

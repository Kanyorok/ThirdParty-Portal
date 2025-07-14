@extends('layouts.app')
@section('title', 'General Ledger Report')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📘 General Ledger Report</h4>

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
                <label class="form-label">GL Account</label>
                <select name="GLAccount" class="form-select">
                    <option value="1000">1000 - Cash & Bank</option>
                    <option value="2000">2000 - Accounts Payable</option>
                    <option value="5000">5000 - Rent Expense</option>
                </select>
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

        <!-- Ledger Table -->
        <table class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>Ref No.</th>
                <th>Description</th>
                <th>Branch</th>
                <th>Department</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>2025-06-01</td>
                <td>JV20240601</td>
                <td>Cash received from customer</td>
                <td>001</td>
                <td>100</td>
                <td>50,000.00</td>
                <td>0.00</td>
                <td>50,000.00</td>
            </tr>
            <tr>
                <td>2025-06-05</td>
                <td>JV20240605</td>
                <td>Payment to supplier</td>
                <td>001</td>
                <td>100</td>
                <td>0.00</td>
                <td>20,000.00</td>
                <td>30,000.00</td>
            </tr>
            <tr>
                <td>2025-06-15</td>
                <td>JV20240615</td>
                <td>Bank charges</td>
                <td>001</td>
                <td>100</td>
                <td>0.00</td>
                <td>1,000.00</td>
                <td>29,000.00</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection

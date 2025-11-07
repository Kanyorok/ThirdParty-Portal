@extends('layouts.app')
@section('title', 'Balance Sheet')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📈 Balance Sheet</h4>

        <!-- Filter Form -->
        <form method="GET" action="#" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">As at Date</label>
                <input type="date" name="AsAtDate" class="form-control" value="2025-06-30">
            </div>
            <div class="col-md-4">
                <label class="form-label">Branch</label>
                <select name="Branch" class="form-select">
                    <option value="">All</option>
                    <option value="001">001 - HQ</option>
                    <option value="002">002 - Nairobi</option>
                </select>
            </div>
            <div class="col-md-4">
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

        <!-- Balance Sheet Layout -->
        <div class="row">
            <div class="col-md-6">
                <h5>Assets</h5>
                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <td>Cash & Bank</td>
                        <td class="text-end">150,000.00</td>
                    </tr>
                    <tr>
                        <td>Accounts Receivable</td>
                        <td class="text-end">40,000.00</td>
                    </tr>
                    <tr>
                        <td><strong>Total Assets</strong></td>
                        <td class="text-end fw-bold">190,000.00</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <h5>Liabilities & Equity</h5>
                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <td>Accounts Payable</td>
                        <td class="text-end">60,000.00</td>
                    </tr>
                    <tr>
                        <td>Capital</td>
                        <td class="text-end">130,000.00</td>
                    </tr>
                    <tr>
                        <td><strong>Total Liabilities & Equity</strong></td>
                        <td class="text-end fw-bold">190,000.00</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

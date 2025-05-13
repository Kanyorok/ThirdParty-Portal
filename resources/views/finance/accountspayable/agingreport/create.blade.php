@extends('layouts.app')
@section('title', 'Aging Report- Accounts Payable')
@section('content')
<div class="container mt-5">
    <h2>Aging Report - Outstanding Liabilities</h2>

    <form method="post" action="#">
        <!-- Filter Options -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="asOfDate" class="form-label">As of Date</label>
                <input type="date" id="asOfDate" name="asOfDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-4">
                <label for="vendor" class="form-label">Vendor (Optional)</label>
                <input type="text" id="vendor" name="vendor" class="form-control" placeholder="Search by vendor name">
            </div>
            <div class="col-md-4">
                <label for="currency" class="form-label">Currency</label>
                <select id="currency" name="currency" class="form-control">
                    <option value="KES">KES - Kenyan Shilling</option>
                    <option value="USD">USD - US Dollar</option>
                    <option value="EUR">EUR - Euro</option>
                </select>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Generate Report</button>
    </form>

    <!-- Placeholder for Report Output -->
    <div class="mt-5">
        <h4>Sample Report Preview</h4>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Vendor</th>
                    <th>Total Due (Ksh)</th>
                    <th>0–30 Days</th>
                    <th>31–60 Days</th>
                    <th>61–90 Days</th>
                    <th>90+ Days</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ABC Supplies</td>
                    <td>120,000</td>
                    <td>40,000</td>
                    <td>30,000</td>
                    <td>25,000</td>
                    <td>25,000</td>
                </tr>
                <tr>
                    <td>XYZ Ltd</td>
                    <td>65,000</td>
                    <td>20,000</td>
                    <td>10,000</td>
                    <td>15,000</td>
                    <td>20,000</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

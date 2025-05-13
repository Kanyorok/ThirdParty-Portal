@extends('layouts.app')
@section('title', 'Aging Report- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <h2>Generate Aging Report</h2>
    <form method="post" action="">
        <div class="mb-3">
            <label for="customer" class="form-label">Select Customer</label>
            <select class="form-select" id="customer" name="customer">
                <option selected disabled>-- Select Customer --</option>
                <option>ABC Distributors</option>
                <option>XYZ Enterprises</option>
                <option>Global Traders</option>
            </select>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="startDate" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="startDate" name="startDate">
            </div>
            <div class="col-md-6">
                <label for="endDate" class="form-label">End Date</label>
                <input type="date" class="form-control" id="endDate" name="endDate">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Aging Buckets</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="buckets[]" value="0-30" id="bucket1" checked>
                <label class="form-check-label" for="bucket1">0-30 Days</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="buckets[]" value="31-60" id="bucket2">
                <label class="form-check-label" for="bucket2">31-60 Days</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="buckets[]" value="61-90" id="bucket3">
                <label class="form-check-label" for="bucket3">61-90 Days</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="buckets[]" value="90+" id="bucket4">
                <label class="form-check-label" for="bucket4">90+ Days</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Generate Report</button>
        <a href="index_aging_report_ar.php" class="btn btn-secondary">Back to Report List</a>
    </form>
</div>
@endsection

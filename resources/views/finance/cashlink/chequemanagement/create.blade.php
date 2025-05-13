@extends('layouts.app')
@section('title', 'Cheque Management')
@section('content')

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Add Cheque Entry</h2>
        <a href="index.php" class="btn btn-outline-secondary">← Back to List</a>
    </div>

    <form action="#" method="post">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="date" class="form-label">Date Issued</label>
                <input type="date" id="date" name="date" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="payee" class="form-label">Payee</label>
                <input type="text" id="payee" name="payee" class="form-control" placeholder="Enter payee name" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="amount" class="form-label">Amount (KSh)</label>
                <input type="number" id="amount" name="amount" class="form-control" placeholder="Enter amount" required>
            </div>
            <div class="col-md-6">
                <label for="bank" class="form-label">Bank</label>
                <input type="text" id="bank" name="bank" class="form-control" placeholder="e.g. KCB, Equity" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="cheque_no" class="form-label">Cheque Number</label>
                <input type="text" id="cheque_no" name="cheque_no" class="form-control" placeholder="Enter cheque number" required>
            </div>
            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="">Select status</option>
                    <option value="Cleared">Cleared</option>
                    <option value="Pending">Pending</option>
                    <option value="Bounced">Bounced</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea id="remarks" name="remarks" class="form-control" rows="3" placeholder="Any additional information..."></textarea>
        </div>

        <div class="d-flex justify-content-start gap-2">
            <button type="submit" class="btn btn-success">Save Cheque</button>
            <button type="reset" class="btn btn-outline-danger">Reset</button>
        </div>
    </form>
</div>

@endsection
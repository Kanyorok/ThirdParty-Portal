@extends('layouts.app')
@section('title', 'Credit Management- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <div class="container mt-5">
        <h2>Create Credit Profile</h2>
        <form method="post" action="">
            <div class="mb-3">
                <label for="customerName" class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="customerName" name="customerName" placeholder="Enter customer name">
            </div>

            <div class="mb-3">
                <label for="creditLimit" class="form-label">Credit Limit (Ksh)</label>
                <input type="number" class="form-control" id="creditLimit" name="creditLimit" placeholder="Enter credit limit">
            </div>

            <div class="mb-3">
                <label for="creditTerms" class="form-label">Credit Terms (in days)</label>
                <input type="number" class="form-control" id="creditTerms" name="creditTerms" placeholder="e.g., 30">
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Credit Status</label>
                <select class="form-select" id="status" name="status">
                    <option selected disabled>-- Select Status --</option>
                    <option value="active">Active</option>
                    <option value="on_hold">On Hold</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Additional notes"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Save Credit Profile</button>
            <a href="index_credit_management.php" class="btn btn-secondary">Back to List</a>
        </form>
    </div>
    @endsection

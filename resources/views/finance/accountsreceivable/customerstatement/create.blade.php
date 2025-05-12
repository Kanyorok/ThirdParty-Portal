@extends('layouts.app')
@section('title', 'Customer Statement- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <h2>Create Customer Statement</h2>

    <form action="#" method="post">
        <div class="mb-3">
            <label for="customerName" class="form-label">Customer</label>
            <select class="form-select" id="customerName" name="customerName">
                <option selected disabled>Select Customer</option>
                <option>ABC Distributors</option>
                <option>XYZ Enterprises</option>
                <option>Global Traders</option>
            </select>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fromDate" class="form-label">From Date</label>
                <input type="date" class="form-control" id="fromDate" name="fromDate">
            </div>
            <div class="col-md-6">
                <label for="toDate" class="form-label">To Date</label>
                <input type="date" class="form-control" id="toDate" name="toDate">
            </div>
        </div>

        <div class="mb-3">
            <label for="statementType" class="form-label">Statement Type</label>
            <select class="form-select" id="statementType" name="statementType">
                <option selected>Summary</option>
                <option>Detailed</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Generate Statement</button>
    </form>
</div>
@endsection

@extends('layouts.app')
@section('title', 'premiumpaymentmanagement')
@section('content')
<div class="container mt-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h4>Premium Payment Tracker</h4>
<a href="{{ route('premiumpaymentmanagement.create') }}" class="btn btn-sm btn-success">+ Add Premium Payment</a>
</div>

<div class="container">
    <h2 class="mb-4">Add Premium Payment</h2>

    <form class="row g-3">
        <div class="col-md-6">
            <label for="policyId" class="form-label">Policy</label>
            <input type="text" class="form-control" id="policyId" placeholder="Enter Policy ID or Name">
        </div>
        <div class="col-md-6">
            <label for="amountPaid" class="form-label">Amount Paid</label>
            <input type="number" class="form-control" id="amountPaid" placeholder="Enter amount">
        </div>
        <div class="col-md-6">
            <label for="paymentDate" class="form-label">Payment Date</label>
            <input type="date" class="form-control" id="paymentDate">
        </div>
        <div class="col-md-6">
            <label for="paymentMethod" class="form-label">Payment Method</label>
            <select class="form-select" id="paymentMethod">
                <option selected disabled>Choose a method</option>
                <option>Bank Transfer</option>
                <option>Credit Card</option>
                <option>Cash</option>
                <option>Cheque</option>
                <option>Mobile Money</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="receiptNo" class="form-label">Receipt No.</label>
            <input type="text" class="form-control" id="receiptNo" placeholder="Enter receipt number">
        </div>
        <div class="col-md-6">
            <label for="paymentStatus" class="form-label">Status</label>
            <select class="form-select" id="paymentStatus">
                <option selected disabled>Choose status</option>
                <option>Paid</option>
                <option>Pending</option>
            </select>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Add Payment</button>
        </div>
    </form>

    <hr class="my-5">

    <h3 class="mb-4">Payment Schedule</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Policy</th>
                    <th>Amount Paid</th>
                    <th>Payment Date</th>
                    <th>Method</th>
                    <th>Receipt No.</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example Data -->
                <tr>
                    <td>POL12345</td>
                    <td>$200.00</td>
                    <td>2025-05-01</td>
                    <td>Credit Card</td>
                    <td>RCPT001</td>
                    <td><span class="badge bg-success">Paid</span></td>
                </tr>
                <tr>
                    <td>POL67890</td>
                    <td>$150.00</td>
                    <td>2025-05-05</td>
                    <td>Bank Transfer</td>
                    <td>RCPT002</td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                </tr>
                <!-- Add dynamic rows here -->
            </tbody>
        </table>
    </div>
</div>

@endsection
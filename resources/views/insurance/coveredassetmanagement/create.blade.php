@extends('layouts.app')
@section('title', 'coveredassetmanagement')
@section('content')
<div class="container">
  <h2 class="mb-4">New Premium Payment Entry</h2>

  <form>
    <div class="mb-3">
      <label for="policyId" class="form-label">Policy</label>
      <input type="text" class="form-control" id="policyId" placeholder="Enter policy number or name" required>
    </div>

    <div class="mb-3">
      <label for="amountPaid" class="form-label">Amount Paid</label>
      <input type="number" class="form-control" id="amountPaid" placeholder="Enter amount" required>
    </div>

    <div class="mb-3">
      <label for="paymentDate" class="form-label">Payment Date</label>
      <input type="date" class="form-control" id="paymentDate" required>
    </div>

    <div class="mb-3">
      <label for="paymentMethod" class="form-label">Payment Method</label>
      <select class="form-select" id="paymentMethod" required>
        <option selected disabled>Choose a method</option>
        <option>Bank Transfer</option>
        <option>Credit Card</option>
        <option>Cash</option>
        <option>Cheque</option>
        <option>Mobile Money</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="receiptNo" class="form-label">Receipt Number</label>
      <input type="text" class="form-control" id="receiptNo" placeholder="Enter receipt number" required>
    </div>

    <div class="mb-3">
      <label for="paymentStatus" class="form-label">Status</label>
      <select class="form-select" id="paymentStatus" required>
        <option selected disabled>Choose status</option>
        <option>Paid</option>
        <option>Pending</option>
      </select>
    </div>

    <button type="submit" class="btn btn-success">Submit Payment</button>
    <a href="index.html" class="btn btn-outline-secondary ms-2">View Payment Schedule</a>
  </form>
</div>
@endsection
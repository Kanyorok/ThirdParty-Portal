@extends('layouts.app')
@section('title', 'premiumpaymentmanagement')
@section('content')
<div class="container mt-4">
  <h4 class="mb-4">Add Premium Payment</h4>
  <form>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label for="policy" class="form-label">Policy</label>
        <select class="form-select" id="policy">
          <option selected disabled>Select Policy</option>
          <option>POL-00123 - Vehicle Insurance</option>
          <option>POL-00456 - Health Cover</option>
          <!-- Dynamically populate options -->
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label for="amountPaid" class="form-label">Amount Paid</label>
        <input type="number" class="form-control" id="amountPaid" placeholder="Enter Amount">
      </div>
      <div class="col-md-4 mb-3">
        <label for="paymentDate" class="form-label">Payment Date</label>
        <input type="date" class="form-control" id="paymentDate">
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label for="paymentMethod" class="form-label">Payment Method</label>
        <select class="form-select" id="paymentMethod">
          <option selected disabled>Select Method</option>
          <option>Bank Transfer</option>
          <option>Mobile Money</option>
          <option>Credit Card</option>
          <option>Cash</option>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label for="receiptNo" class="form-label">Receipt No.</label>
        <input type="text" class="form-control" id="receiptNo" placeholder="Enter Receipt Number">
      </div>
      <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status">
          <option>Paid</option>
          <option>Pending</option>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label for="scheduleLink" class="form-label">View Payment Schedule</label>
      <input type="url" class="form-control" id="scheduleLink" placeholder="Enter URL or reference link">
    </div>

    <button type="submit" class="btn btn-primary">Save Payment</button>
    <button type="reset" class="btn btn-secondary">Cancel</button>
  </form>
</div>
@endsection
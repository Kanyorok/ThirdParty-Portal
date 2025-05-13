@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4" id="receiptArea">
  <div class="border p-4 shadow-sm" style="max-width: 800px; margin: auto; background-color: #fff;">
    <div class="text-center mb-4">
      <h3 class="fw-bold">🏢 Property Management System</h3>
      <p class="text-muted">Official Tenant Payment Receipt</p>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <strong>Receipt No:</strong> RCPT-2025-0031<br>
        <strong>Date:</strong> 2025-05-03
      </div>
      <div class="col-md-6 text-end">
        <strong>Received From:</strong><br>
        Moses K. <br>
        ID: ID12345678
      </div>
    </div>

    <hr>

    <div class="row mb-3">
      <div class="col-md-6">
        <strong>Invoice No:</strong> INV-2025-0001<br>
        <strong>Billing Period:</strong> May 2025<br>
        <strong>Unit:</strong> Unit 101 – Sunset Plaza
      </div>
      <div class="col-md-6 text-end">
        <strong>Payment Method:</strong> MPESA<br>
        <strong>Transaction Ref:</strong> MPESA12345
      </div>
    </div>

    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>Description</th>
          <th class="text-end">Amount (KES)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Rent</td>
          <td class="text-end">25,000</td>
        </tr>
        <tr>
          <td>Service Charge</td>
          <td class="text-end">1,500</td>
        </tr>
        <tr>
          <th>Total Due</th>
          <th class="text-end">26,500</th>
        </tr>
        <tr>
          <td><strong>Amount Paid</strong></td>
          <td class="text-end"><strong>10,000</strong></td>
        </tr>
        <tr>
          <td>Balance Remaining</td>
          <td class="text-end text-danger fw-bold">16,500</td>
        </tr>
      </tbody>
    </table>

    <p class="fst-italic small">Note: This receipt acknowledges a partial payment toward the stated invoice. Kindly clear the remaining balance by the due date.</p>

    <div class="text-end mt-4">
      <button class="btn btn-secondary" onclick="window.print()">🖨️ Print Receipt</button>
    </div>
  </div>
</div>
@endsection
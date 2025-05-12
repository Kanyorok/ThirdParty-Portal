@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
<a href="{{ route('rentinvoice.create') }}" class="btn btn-primary mb-3">New Invoice</a>
  <h4 class="fw-bold mb-3">📋 Rent Invoices</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Invoice No</th>
        <th>Tenant</th>
        <th>Unit</th>
        <th>Billing Period</th>
        <th>Total Amount</th>
        <th>Paid So Far</th>
        <th>Outstanding</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <!-- Fully Paid -->
      <tr>
        <td>1</td>
        <td>INV-2025-0001</td>
        <td>Moses K.</td>
        <td>Unit 101 - Sunset Plaza</td>
        <td>May 2025</td>
        <td>KES 26,500</td>
        <td>KES 26,500</td>
        <td>KES 0</td>
        <td><span class="badge bg-success">Paid</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-secondary">🧾 Receipt</button>
        </td>
      </tr>

      <!-- Partially Paid -->
      <tr>
        <td>2</td>
        <td>INV-2025-0002</td>
        <td>Acme Ltd.</td>
        <td>Unit B204 - Mountain View</td>
        <td>Apr–Jun 2025</td>
        <td>KES 78,000</td>
        <td>KES 30,000</td>
        <td>KES 48,000</td>
        <td><span class="badge bg-info">Partially Paid</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-success">💳 Pay</button>
        </td>
      </tr>

      <!-- Unpaid -->
      <tr>
        <td>3</td>
        <td>INV-2025-0003</td>
        <td>Jane W.</td>
        <td>Unit 203 - Green Court</td>
        <td>May 2025</td>
        <td>KES 18,000</td>
        <td>KES 0</td>
        <td>KES 18,000</td>
        <td><span class="badge bg-danger">Unpaid</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-success">💳 Pay</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection
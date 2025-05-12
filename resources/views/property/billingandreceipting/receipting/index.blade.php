@extends('layouts.app')
@section('title', 'Tenant Payments')
@section('content')
<div class="container mt-4">
<a href="{{ route('rentreceipt.create') }}" class="btn btn-primary mb-3">New Reciept</a>
  <h4 class="fw-bold mb-3">📋 Tenant Payments</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Invoice</th>
        <th>Tenant</th>
        <th>Date Paid</th>
        <th>Amount</th>
        <th>Method</th>
        <th>Reference</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>INV-2025-0001</td>
        <td>Moses K.</td>
        <td>2025-05-02</td>
        <td>26,500</td>
        <td>MPESA</td>
        <td>MPESA12345</td>
        <td>
        <a href="{{ route('receiptprint.index') }}" class="btn btn-sm btn-outline-secondary">🧾 Print Receipt</a>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>INV-2025-0002</td>
        <td>Acme Ltd.</td>
        <td>2025-04-30</td>
        <td>78,000</td>
        <td>Bank Transfer</td>
        <td>TXB9021</td>
        <td>
        <a href="{{ route('receiptprint.index') }}" class="btn btn-sm btn-outline-secondary">🧾 Print Receipt</a>
        </td>
      </tr>
    </tbody>
  </table>
</div>

@endsection
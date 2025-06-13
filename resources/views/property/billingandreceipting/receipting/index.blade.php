@extends('layouts.app')
@section('title', 'Tenant Payments')
@section('content')
<div class="container mt-4">
<a href="{{ route('rentreceipt.create') }}" class="btn btn-primary mb-3">New Reciept</a>
  <h4 class="fw-bold mb-3">📋 Tenant Payments</h4>

    @if($receipts->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Invoice</th>
          <th>Billing Month</th>
          <th>Invoice Date</th>
          <th>Rent Amount</th>
          <th>Total Due</th>
          <th>Amount Paid So Far</th>
          <th>Balance</th>
          <th>Payment Date</th>
          <th>Amount Paid Now</th>
          <th>Payment Method</th>
        <th>Reference</th>
          <th>Remarks</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($receipts as $receipt)
      <tr>
          <td>{{ $loop->iteration ??'_' }}</td>
          <td>{{ $receipt->InvoiceID ?? '_' }}</td>
          <td>{{ $receipt->BillingMonth ?? '_' }}</td>
          <td>{{ $receipt->InvoiceDate ?? '_' }}</td>
          <td>{{ $receipt->RentAmount ?? '_' }}</td>
          <td>{{ $receipt->TotalDue ?? '_' }}</td>
          <td>{{ $receipt->AmountPaid ?? '_' }}</td>
          <td>{{ $receipt->Balance ?? '_' }}</td>
          <td>{{ $receipt->PaymentDate ?? '_' }}</td>
          <td>{{ $receipt->Amount ?? '_' }}</td>
          <td>{{ $receipt->PaymentMethod ?? '_' }}</td>
          <td>{{ $receipt->ReferenceNo ?? '_' }}</td>
          <td>{{ $receipt->Remarks ?? '_' }}</td>
        <td>
            <a href="{{ route('rentreceipt.index') }}" class="btn btn-sm btn-outline-secondary">🧾 Print Receipt</a>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No property invoices registered yet.</p>
    @endif
</div>
@endsection

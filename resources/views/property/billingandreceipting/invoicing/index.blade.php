@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
<a href="{{ route('rentinvoice.create') }}" class="btn btn-primary mb-3">New Invoice</a>
  <h4 class="fw-bold mb-3">📋 Rent Invoices</h4>

@if($invoices->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Lease</th>
        <th>Billing Period</th>
        <th>Invoice Date</th>
        <th>Rent Amount</th>
        <th>Service Charge</th>
        <th>Other Charges</th>
        <th>Invoice Notes</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoices as $invoice)
      <!-- Fully Paid -->
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $invoice->Lease }}</td>
        <td>{{ $invoice->BillingMonth }}</td>
        <td>{{ $invoice->InvoiceDate }}</td>
        <td>{{ $invoice->RentAmount }}</td>
        <td>{{ $invoice->ServicesCharge }}</td>
        <td>{{ $invoice->OtherCharges }}</td>
        <td>{{ $invoice->InvoiceNotes }}</td>
        <td><span class="badge bg-success">Paid</span></td>
        <td>
          <a href="{{ route('rentinvoice.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary">👁 View</a>
          <button class="btn btn-sm btn-outline-secondary">🧾 Receipt</button>
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
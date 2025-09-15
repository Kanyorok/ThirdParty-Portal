@extends('layouts.app')
@section('title', 'Tenant Payments')
@section('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
  <div class="container mt-4">
    <a href="{{ route('rentreceipt.create') }}" class="btn btn-primary mb-3">New Reciept</a>

    <p><small>This is a list of tenant payments/receipts</small></p>
    @if ($receipts->count())
      <table class="table table-bordered table-striped align-middle" id='Rentreceipt'>
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Invoice</th>
            <th>TenantID</th>
            <th>Billing Month</th>
            <th>Payment Date</th>
            <th>Total Due</th>
            <th>Amount Paid Now</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($receipts as $receipt)
            <tr>
              <td>{{ $loop->iteration ?? '_' }}</td>
              <td>{{ $receipt->invoice->InvoiceNumber ?? '_' }}</td>
              <th>{{ $receipt->invoice->lease->tenant->TenantName ?? '_' }}</td>
              <td>{{ $receipt->BillingMonth ?? '_' }}</td>
              <td>{{ $receipt->PaymentDate ? \Carbon\Carbon::parse($receipt->PaymentDate)->format('d/m/Y') : '-' }}</td>
              <td>{{ $receipt->Balance ?? '_' }}</td>
              <td>{{ $receipt->AmountPaidNow ?? '_' }}</td>
              <td>
                <a href="{{ route('rentreceipt.pdf', $receipt->Id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Print Receipt</a>
                <a href="{{ route('rentreceipt.show', $receipt->Id) }}" class="btn btn-sm btn-info">View</a>
                <a href="{{ route('rentreceipt.edit', $receipt->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('rentreceipt.destroy', $receipt->Id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger"
                    onclick="return confirm('Are you sure you want to delete this lease schedule?');">Delete
                  </button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p>No property invoices registered yet.</p>
    @endif
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#Rentreceipt').DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        lengthChange: true
      });
    });
  </script>

@endsection

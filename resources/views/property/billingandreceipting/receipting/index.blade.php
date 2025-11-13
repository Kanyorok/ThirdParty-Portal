@extends('layouts.app')

@section('title', 'Tenant Payments')

@section('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <style>
    .action-buttons {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.4rem;
        align-items: center;
    }
  </style>
@endsection

@section('content')
<div class="container mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-end align-items-center mb-3">
    <a href="{{ route('rentreceipt.create') }}" class="btn btn-primary">
      <i class="bi bi-receipt me-1"></i> New Receipt
    </a>
  </div>

  <p class="text-muted">
    <small>This table lists all tenant payments and receipts recorded in the system.</small>
  </p>

  @if ($receipts->count())
    <div class="card shadow-sm">
      <div class="card-body">
        <table id="RentReceipt" class="table table-bordered table-striped table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 5%">#</th>
              <th>Invoice No.</th>
              <th>Tenant</th>
              <th>Billing Month</th>
              <th>Payment Date</th>
              <th class="text-end">Total Due</th>
              <th class="text-end">Amount Paid</th>
              <th style="width: 20%">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($receipts as $receipt)
              <tr>
                <td>{{ $loop->iteration ?? '-' }}</td>
                <td>{{ $receipt->invoice->InvoiceNumber ?? '-' }}</td>
                <td>{{ $receipt->invoice->lease->tenant->thirdParty->ThirdPartyName ?? '-' }}</td>
                <td>{{ $receipt->BillingMonth ?? '-' }}</td>
                <td>{{ $receipt->PaymentDate ? \Carbon\Carbon::parse($receipt->PaymentDate)->format('d/m/Y') : '-' }}</td>
                <td class="text-end">{{ number_format($receipt->Balance, 2) ?? '-' }}</td>
                <td class="text-end">{{ number_format($receipt->AmountPaidNow, 2) ?? '-' }}</td>
                <td>
                  <div class="action-buttons">
                    <a href="{{ route('rentreceipt.pdf', $receipt->Id) }}" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-secondary" 
                       title="Print Receipt">
                      <i class="bi bi-printer"></i>
                    </a>

                    <a href="{{ route('rentreceipt.show', $receipt->Id) }}" 
                       class="btn btn-sm btn-info text-white" 
                       title="View Receipt">
                      <i class="bi bi-eye"></i>
                    </a>

                    <form action="{{ route('rentreceipt.destroy', $receipt->Id) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('Are you sure you want to return this payment?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" title="Reverse Payment">
                        <i class="bi bi-arrow-counterclockwise"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @else
    <div class="alert alert-info mt-3">
      <i class="bi bi-info-circle me-2"></i> No tenant payments have been recorded yet.
    </div>
  @endif

</div>
@endsection

@section('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#RentReceipt').DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        lengthChange: true
      });
    });
  </script>
@endsection

@extends('layouts.app')
@section('title', 'Voucher Details')

@section('content')
<div class="container mt-3">
    <div class="card shadow p-4 rounded-4">
        <div class="card-header bg-light py-2 px-3">
            <h5 class="mb-0 text-muted">
                <i class="fas fa-file-invoice-dollar text-info"></i> Voucher Details
            </h5>
        </div>

        <div class="card-body">
            {{-- Basic Info --}}
            <div class="mb-3">
                <strong>Voucher No:</strong>
                <span class="text-primary">{{ $voucher->VoucherNo }}</span>
            </div>

            <div class="mb-3">
                <strong>Invoice Ref No:</strong>
                <span class="text-muted">{{ $voucher->invoice->InvoiceNumber ?? 'N/A' }}</span>
            </div>

            <div class="mb-3">
                <strong>Total Amount:</strong>
                <span class="text-success">{{ number_format($voucher->invoice->InvoiceAmount ?? 0, 2) }}</span>
            </div>

            <div class="mb-4">
                <strong>Payment Type:</strong>
                <span class="badge bg-info text-dark">{{ ucfirst($voucher->PaymentType ?? 'N/A') }}</span>
            </div>

            {{-- Show Amounts in Tabular Format --}}
            @if($voucher->PaymentType === 'Partial')
                <h6 class="text-muted">Partial Payment</h6>
                <table class="table table-bordered w-50">
                    <tr>
                        <th>Invoice Total</th>
                        <td class="text-end">{{ number_format($voucher->invoice->InvoiceAmount ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Amount Paid</th>
                        <td class="text-end">{{ number_format($voucher->TotAmnt ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Balance</th>
                        <td class="text-end">
                            {{ number_format(($voucher->invoice->InvoiceAmount ?? 0) - ($voucher->TotAmnt ?? 0), 2) }}
                        </td>
                    </tr>
                </table>
            @elseif($voucher->PaymentType === 'Full')
                <h6 class="text-muted">Full Payment</h6>
                <table class="table table-bordered w-50">
                    <tr>
                        <th>Amount Paid</th>
                        <td class="text-end">{{ number_format($voucher->TotAmnt ?? 0, 2) }}</td>
                    </tr>
                </table>
            @elseif($voucher->PaymentType === 'Scheduled')
                <h6 class="text-muted">Scheduled Payment</h6>
                <table class="table table-bordered w-50">
                    <tr>
                        <th>Amount to be Paid</th>
                        <td class="text-end">{{ number_format($voucher->TotAmnt ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Scheduled Date</th>
                        <td>{{ \Carbon\Carbon::parse($voucher->ScheduledDate)->format('d M Y') ?? 'N/A' }}</td>
                    </tr>
                </table>
            @endif

            {{-- Approve/Reject Buttons --}}
            <div class="mt-4 d-flex gap-3">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="fas fa-check-circle me-1"></i> Approve
                </button>

                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times-circle me-1"></i> Reject
                </button>

            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('paymentvoucher.approve', $voucher->Id) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Approval</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for="approveReason" class="form-label">Reason for approval</label>
          <textarea class="form-control" name="Reasons" rows="3" placeholder="Optional reason..."></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Confirm Approve</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('paymentvoucher.reject', $voucher->Id) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Rejection</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for="rejectReason" class="form-label">Reason for rejection</label>
          <textarea class="form-control" name="Reasons" rows="3" required placeholder="Required reason..."></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Confirm Reject</button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

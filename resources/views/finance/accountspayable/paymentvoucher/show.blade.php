@extends('layouts.app')
@section('title', 'Voucher Details')

@section('content')
    <div class="container mt-3">
        <div class="card shadow-sm p-4 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-muted">
                    <i class="fas fa-file-invoice-dollar text-info"></i> Voucher Details
                </h5>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>

            {{-- Voucher Info --}}
            <div class="row border-bottom pb-2 mb-3">
                <div class="col-md-6">
                    <strong>Voucher No:</strong>
                    <span class="text-primary">{{ $voucher->VoucherNo }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Invoice Ref No:</strong>
                    <span class="text-muted">{{ $voucher->invoice->InvoiceNumber ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Payment Method:</strong>
                    <span>{{ $voucher->PaymentMethod ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Payment Type:</strong>
                    <span class="badge bg-info text-dark">{{ ucfirst($voucher->PaymentType ?? 'N/A') }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Total Amount:</strong>
                    <span class="text-success">{{ number_format($voucher->TotalAmount ?? 0, 2) }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong>
                    <span class="badge {{ $voucher->ApprovalStatus === 'posted' ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ ucfirst($voucher->ApprovalStatus) }}
                </span>
                </div>
            </div>

            {{-- Supplier / Payee Info --}}
            <h6 class="text-muted mb-2">Payee Information</h6>
            <div class="row border-bottom pb-2 mb-3">
                <div class="col-md-6">
                    <strong>Supplier Name:</strong>
                    <span>{{ $voucher->invoice->supplier->SupplierName ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Email:</strong>
                    <span>{{ $voucher->invoice->supplier->ContactEmail ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Phone:</strong>
                    <span>{{ $voucher->invoice->supplier->ContactPhone ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Address:</strong>
                    <span>{{ $voucher->invoice->supplier->Address ?? 'N/A' }}</span>
                </div>
            </div>

            {{-- Payment Details Table --}}
            <h6 class="text-muted mb-2">Payment Details</h6>
            <table class="table table-sm table-bordered align-middle w-75">
                <thead class="table-light">
                <tr>
                    <th class="text-start">Description</th>
                    <th class="text-end">Amount ({{ $voucher->invoice->currency->Code ?? '' }})</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Invoice Total</td>
                    <td class="text-end">{{ number_format($voucher->invoice->InvoiceAmount ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Amount Paid</td>
                    <td class="text-end">{{ number_format($amtPaidOnInvoice ?? 0, 2) }}</td>
                </tr>
                @if($voucher->PaymentType === 'Scheduled')
                    <tr>
                        <td>Scheduled Date</td>
                        <td class="text-end">
                            {{ $voucher->StartDate ? \Carbon\Carbon::parse($voucher->StartDate)->format('d M Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Frequency</td>
                        <td class="text-end">{{ $voucher->Frequency ?? 'N/A' }}</td>
                    </tr>
                @endif
                <tr class="fw-bold table-light">
                    <td>Balance</td>
                    <td class="text-end">
                        {{ number_format(($voucher->invoice->InvoiceAmount ?? 0) - ($amtPaidOnInvoice ?? 0), 2) }}
                    </td>
                </tr>
                </tbody>
            </table>

            {{-- Remarks Section --}}
            @if(!empty($voucher->Description))
                <div class="mt-4">
                    <h6 class="text-muted mb-2">Remarks:</h6>
                       <i> {{ $voucher->Description }}</i>
                </div>
            @endif

            {{-- Approve / Reject Buttons --}}
            @if($voucher->ApprovalStatus === 'draft')
                <div class="mt-4 d-flex gap-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="fas fa-check-circle me-1"></i> Approve
                    </button>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times-circle me-1"></i> Reject
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('paymentvoucher.approve', $voucher->Id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Approval</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('paymentvoucher.reject', $voucher->Id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Rejection</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

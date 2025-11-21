@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'View Rent Invoice')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            {{-- <a href="{{ route('rentinvoice.edit', $invoice->Id) }}" class="btn btn-sm btn-dark">
                <i class="bi bi-pencil-square"></i> Edit
            </a> --}}
            <a href="{{ route('rentinvoice.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Invoice Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light fw-bold py-3">
            <i class="bi bi-receipt"></i> Invoice Details
        </div>

        <div class="card-body">

            <!-- Invoice & Tenant Info -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">Invoice Number</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ $invoice->InvoiceNumber ?? '-' }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Tenant</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ $invoice->lease->tenant->thirdParty->ThirdPartyName ?? '-' }}" readonly>
                </div>
            </div>

            <!-- Lease Info -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">Lease Number</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ $invoice->lease->LeaseNumber ?? '-' }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">Billing Month</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ $invoice->BillingMonth ?? '-' }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">Invoice Date</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ $invoice->InvoiceDate ? Carbon::parse($invoice->InvoiceDate)->format('d M Y') : '-' }}" readonly>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Line Item</th>
                            <th>Description</th>
                            <th>Amount ({{ $invoice->currency->Code ?? '—' }})</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <tr>
                            <td>Rent</td>
                            <td>{{ $invoice->DescriptionRent ?? 'Rent Payment' }}</td>
                            <td>{{ number_format($invoice->RentAmount ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Service Charge</td>
                            <td>{{ $invoice->DescriptionService ?? 'Monthly Service Charge' }}</td>
                            <td>{{ number_format($invoice->ServicesCharge ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Parking Fee</td>
                            <td>{{ $invoice->DescriptionParking ?? 'Parking Space' }}</td>
                            <td>{{ number_format($invoice->ParkingFee ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Other Charges</td>
                            <td>{{ $invoice->DescriptionOther ?? 'Miscellaneous' }}</td>
                            <td>{{ number_format($invoice->OtherCharges ?? 0, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold text-center">
                            <td colspan="2">Total Amount</td>
                            <td>
                                {{ number_format(
                                    ($invoice->RentAmount ?? 0) +
                                    ($invoice->ServicesCharge ?? 0) +
                                    ($invoice->ParkingFee ?? 0) +
                                    ($invoice->OtherCharges ?? 0), 2
                                ) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notes -->
            <div class="mb-3">
                <label class="form-label text-muted">Invoice Notes</label>
                <textarea class="form-control bg-light" rows="3" readonly>{{ $invoice->InvoiceNotes ?? '—' }}</textarea>
            </div>

        </div>

        <!-- Footer Metadata -->
        <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-muted">
            <div>
                <i class="bi bi-person-circle"></i>
                Created by <strong>{{ $invoice->createdByUser->Name ?? '-' }}</strong>
                on <strong>{{ $invoice->CreatedOn ? Carbon::parse($invoice->CreatedOn)->format('d M Y') : '-' }}</strong>
                |
                Modified by <strong>{{ $invoice->modifiedByUser->Name ?? '-' }}</strong>
                on <strong>{{ $invoice->ModifiedOn ? Carbon::parse($invoice->ModifiedOn)->format('d M Y') : '-' }}</strong>
            </div>
            <div>
                @php
                    $statusText = $invoice->Status ? $invoice->Status->value : 'Unknown';
                    $statusColor = match($statusText) {
                        'Approved' => 'success',
                        'Pending' => 'warning',
                        'Rejected' => 'danger',
                        default => 'secondary',
                    };
                @endphp
                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($statusText) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

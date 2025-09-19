@extends('layouts.app')
@section('title', 'Invoice • '.$invoice->InvoiceNumber)

@section('content')

    <div class="card shadow rounded-4 border-0 invoice-page">
        <!-- Header -->
        <div class="p-3 p-md-4 border-bottom bg-light rounded-top-4"
             style="background: linear-gradient(135deg, #f8fafc, #eef2ff);">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <img src="{{asset('assets/img/icons/craft-logo.png')}}" alt="Logo" style="height:38px" class="rounded bg-white p-1">
                    <div>
                        <div class="fw-bold">Craft Silicon Limited</div>
                        <div class="small text-muted">Financial Technology • Core Banking • Digital Channels</div>
                    </div>
                </div>
                <div class="text-md-end">
                    <span class="badge rounded-pill text-bg-info px-3 py-2">{{ $invoice->source->Name ?? '—' }}</span>
                    <span class="badge rounded-pill text-bg-secondary px-3 py-2">{{ $invoice->currency->Code ?? '' }}</span>
                    @php
                        $statusClasses = [
                            'draft'    => 'text-bg-secondary',
                            'rejected' => 'text-bg-danger',
                            'posted'   => 'text-bg-success',
                        ];
                        $status = strtolower($invoice->ApprovalStatus);
                    @endphp

                    <span class="badge rounded-pill {{ $statusClasses[$status] ?? 'text-bg-secondary' }} px-3 py-2">
                        {{ ucfirst($status) }}
                    </span>
                    <div class="mt-1 h5 mb-0 fw-semibold">Invoice: <span class="text-nowrap">{{ $invoice->InvoiceNumber }}</span></div>
                    <div class="small text-muted">
                        Issue: <strong>{{ \Carbon\Carbon::parse($invoice->InvoiceDate)->format('Y-m-d') }}</strong> •
                        Due: <strong>{{ \Carbon\Carbon::parse($invoice->DueDate)->format('Y-m-d') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-3 p-md-4">
            <div class="row g-3">
                <!-- Bill To -->
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body py-3">
                            <h6 class="text-uppercase text-muted mb-0">Bill To</h6>
                            <div class="fs-6 fw-semibold mt-2">{{ $invoice->customer->ThirdPartyName ?? '—' }}</div>
                            <div class="small">{{ $invoice->customer->PhysicalAddress ?? '—' }}</div>
                            <div class="small text-muted mt-2">Email: <span class="text-dark">{{ $invoice->customer->Email ?? '—' }}</span></div>
                            <div class="small text-muted">Phone: <span class="text-dark">{{ $invoice->customer->Phone ?? '—' }}</span></div>
                            <div class="small text-muted">Reg No.: <span class="text-dark">{{ $invoice->customer->RegistrationNumber ?? '—' }}</span></div>
                            <div class="small text-muted">Country: <span class="text-dark">{{ $invoice->customer->country?->Name ?? '—' }}</span></div>
                            <div class="mt-2 small text-muted">Request: {{ $invoice->RequestID }}</div>
                        </div>
                    </div>
                </div>

                <!-- From -->
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body py-3">
                            <h6 class="text-uppercase text-muted mb-0">From</h6>
                            <div class="fw-semibold mt-2">Craft Silicon Limited</div>
                            <div class="small">Craft Silicon Campus, Musa Gitau Road,<br>off Waiyaki Way, Nairobi, Kenya</div>
                            <div class="small text-muted mt-2">Email: info.kenya@craftsilicon.com</div>
                            <div class="small text-muted">Phone: +254 709 044 000</div>
                            <div class="small text-muted">Sales: sales@craftsilicon.com / +254 709 044 333</div>
                            <div class="small text-muted">Support: support@craftsilicon.com / +254 709 044 444</div>
                            <div class="mt-2 small">Payment due within 30 days from invoice date.</div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body py-3">
                            <h6 class="text-uppercase text-muted mb-0">Summary</h6>
                            <table class="table align-middle mb-0 mt-2">
                                <tbody>
                                <tr>
                                    <td class="text-muted">Subtotal</td>
                                    <td class="text-end"><strong>{{ $invoice->currency->Symbol }} {{ number_format($invoice->TotalAmount, 2) }}</strong></td>
                                </tr>
                                <tr class="table-light">
                                    <td class="fw-semibold">Total</td>
                                    <td class="text-end fw-semibold">{{ $invoice->currency->Symbol }} {{ number_format($invoice->TotalAmount, 2) }}</td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="mt-3 d-grid gap-2">
                                @if($invoice->ApprovalStatus==='draft')
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                                        <i class="fas fa-thumbs-up me-1"></i> Generate / Approve
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                        <i class="fas fa-thumbs-down me-1"></i> Reject
                                    </button>
                                @endif
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card mt-3 border-0 shadow-sm rounded-4">
                <div class="card-body py-3">
                    <h6 class="mb-0">Items</h6>
                    <table class="table table-hover align-middle mt-2">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $totalTax = 0; @endphp
                        @forelse($invoice->lines as $line)
                            @php $totalTax += $line->TaxAmount; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $line->InvoiceLineName }}</td>
                                <td class="small text-muted">{{ $line->Description }}</td>
                                <td class="text-end">{{ number_format($line->Quantity, 2) }}</td>
                                <td class="text-end">{{ $invoice->currency->Symbol }} {{ number_format($line->UnitCost, 2) }}</td>
                                <td class="text-end">{{ $line->Tax ? $line->Tax.'%' : '0.00' }}</td>
                                <td class="text-end">{{ number_format($line->Discount, 2) }}</td>
                                <td class="text-end">{{ $invoice->currency->Symbol }} {{ number_format($line->Total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle me-1"></i> No invoice items found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot class="table-light">
                        <tr>
                            <th colspan="7" class="text-end">Total Tax</th>
                            <th class="text-end">{{ $invoice->currency->Symbol }} {{ number_format($totalTax, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">Grand Total</th>
                            <th class="text-end">{{ $invoice->currency->Symbol }} {{ number_format($invoice->TotalAmount, 2) }}</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Remarks -->
            <div class="card mt-3 border-0 shadow-sm rounded-4">
                <div class="card-body py-3">
                    <h6 class="mb-2">Invoice Remarks</h6>
                    <div>{{ $invoice->InvoiceRemarks ?? '—' }}</div>
                </div>
            </div>

            <!-- Footer meta -->
            <div class="row g-3 mt-2 small">
                <div class="col-md-6">
                    <div class="border rounded-3 p-2">
                        <div class="text-muted">Prepared On</div>
                        <div class="fw-medium">{{ \Carbon\Carbon::parse($invoice->CreatedOn)->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-2">
                        <div class="text-muted">Modified On</div>
                        <div class="fw-medium">{{ \Carbon\Carbon::parse($invoice->ModifiedOn)->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Tear-off Remittance -->
            <hr class="my-3 print-only">
            <div class="border rounded-3 p-2 print-only small">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fw-bold">Remittance Advice</div>
                        <div class="text-muted">Attach with payment</div>
                    </div>
                    <div>
                        <div><strong>Invoice:</strong> {{ $invoice->InvoiceNumber }}</div>
                        <div><strong>Amount:</strong> {{ $invoice->currency->Symbol }} {{ number_format($invoice->TotalAmount,2) }}</div>
                        <div><strong>Due:</strong> {{ \Carbon\Carbon::parse($invoice->DueDate)->format('Y-m-d') }}</div>
                    </div>
                </div>
                <div class="mt-1">Pay to: Craft Silicon Limited • A/C 0012345678900</div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">Generate/Approve {{ $invoice->InvoiceNumber }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('ar.invoice.approve', $invoice->Id ?? $invoice->id) }}" method="POST" id="approveForm">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="alert alert-info small">
                            You’re about to approve this invoice. Amount: {{ $invoice->currency->Symbol }} {{ number_format($invoice->TotalAmount,2) }}
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Approval Note <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="Reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button>
                        <button type="submit" class="btn btn-success"
                        onclick="if(this.form.checkValidity()){
                            this.disabled = true;
                            this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                            this.form.submit();
                        }">
                    <i class="fas fa-check-circle me-1"></i> Approve
                </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">Reject {{ $invoice->InvoiceNumber }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('ar.invoice.reject', $invoice->Id ?? $invoice->id) }}" method="POST" id="approveForm">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="alert alert-warning small">
                            You’re about to reject this invoice. Amount: {{ $invoice->currency->Symbol }} {{ number_format($invoice->TotalAmount,2) }}
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button>
                        <button type="submit" class="btn btn-danger"
                        onclick="if(this.form.checkValidity()){
                            this.disabled = true;
                            this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                            this.form.submit();
                        }">
                    <i class="fas fa-times-circle me-1"></i> Reject
                </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('styles')
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        @media print {
            body * { visibility: hidden; }
            .invoice-page, .invoice-page * { visibility: visible; }
            .invoice-page { position: relative; font-size: 12px; }
            .navbar, .btn, .modal, .pagination { display: none !important; }
            .card, .shadow { box-shadow: none !important; border: none !important; }
            .table { font-size: 12px; }
            .print-only { display: block !important; }
            .card, .table, .table * { page-break-inside: avoid; }
        }
        .print-only { display: none; }
    </style>
@endsection

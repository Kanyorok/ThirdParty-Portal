@php
    use App\Services\DMS\DocumentService;
@endphp
@extends('layouts.app')
@section('title','Receipt Details')

@section('content')
    <div class="container my-3">
        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-2">
            <a href="{{ route('receiptsposting.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Receipts
            </a>
            <div class="d-flex gap-2">
                @if($receipt->Status === 'Draft')
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#postReceiptModal">
                        <i class="fas fa-check-circle me-1"></i> Post Receipt
                    </button>
                @endif
                <button class="btn btn-sm btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>

        <div id="printRoot" class="card shadow-sm rounded-4 border-0 p-3 p-md-4">
            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start">
                <div>
                    <div class="h5 mb-0">Receipt — <span class="fw-semibold">{{ $receipt->ReceiptNumber }}</span></div>
                    <div class="small text-muted">{{ $receipt->customer->ThirdPartyName ?? '-' }}</div>
                    <div class="small text-muted">{{ $receipt->customer->RegistrationNumber ?? '-' }}
                        • {{ $receipt->customer->Email ?? '-' }}</div>
                </div>
                <div class="text-md-end mt-2 mt-md-0">
                    <div class="small text-muted">Status</div>
                    @php
                        $status = strtolower($receipt->Status ?? 'draft');
                        $statusClass = match($status) {
                            'posted' => 'bg-success',
                            'draft' => 'bg-warning text-dark',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <div><span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span></div>
                    <div class="small text-muted mt-2">Receipt Date: {{ $receipt->ReceiptDate->format('M d, Y') }}</div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="row g-3 mt-3">
                @php
                    // Determine wallet usage/refund tied to this receipt
                    $walletTxns = \App\Models\Finance\CustomerWalletTransaction::where('CustomerID', $receipt->CustomerID)
                        ->where('ReferenceType', 'receipt')
                        ->where('ReferenceID', $receipt->Id)
                        ->get(['TransactionType','Amount']);
                    $walletUsed = (float) $walletTxns->where('TransactionType','withdrawal')->sum('Amount');
                    $walletRefund = (float) $walletTxns->where('TransactionType','deposit')->sum('Amount');
                    $cashApplied = max(0, (float)$receipt->total_allocated - $walletUsed);
                    // Build display label for payment method
                    $baseMethod = $receipt->paymentMethod->Description ?? $receipt->PaymentMethod;
                    if ($walletUsed > 0 && $cashApplied > 0) {
                        $displayMethod = $baseMethod . ' + Wallet';
                    } elseif ($walletUsed > 0 && $cashApplied == 0) {
                        $displayMethod = 'Wallet';
                    } else {
                        $displayMethod = $baseMethod;
                    }
                @endphp
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Amount Received</div>
                        <div class="fs-5 fw-semibold text-success">
                            KSh {{ number_format($receipt->AmountReceived, 2) }}</div>
                        <div class="small text-muted">{{ $displayMethod }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Applied to Invoices</div>
                        <div class="fs-5 fw-semibold text-primary">
                            KSh {{ number_format($receipt->total_allocated, 2) }}</div>
                        <div class="small text-muted">{{ $receipt->allocations->count() }} invoice(s)</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Unapplied Amount</div>
                        <div
                            class="fs-5 fw-semibold {{ $receipt->UnappliedAmount > 0 ? 'text-warning' : 'text-muted' }}">
                            KSh {{ number_format($receipt->UnappliedAmount, 2) }}
                        </div>
                        @if($receipt->UnappliedAmount > 0)
                            <div class="small text-warning">Added to wallet</div>
                        @else
                            <div class="small text-muted">Fully allocated</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Reference</div>
                        <div class="fw-semibold">{{ $receipt->ReferenceNumber ?: 'No reference' }}</div>
                        <div class="small text-muted">Value: {{ $receipt->ValueDate->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Funding Breakdown -->
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small text-uppercase mb-2">Funding Breakdown</div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>From Wallet</span>
                            <span class="fw-semibold">KSh {{ number_format($walletUsed, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>From {{ $baseMethod }}</span>
                            <span class="fw-semibold">KSh {{ number_format($cashApplied, 2) }}</span>
                        </div>
                        @if($walletRefund > 0)
                            <div class="d-flex justify-content-between small text-muted">
                                <span>Returned to Wallet (unapplied)</span>
                                <span class="fw-semibold">KSh {{ number_format($walletRefund, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoice Allocations -->
            @if($receipt->allocations->count() > 0)
                <div class="mt-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-file-invoice text-info me-2"></i> Invoice Allocations
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Invoice No.</th>
                                <th>Issue Date</th>
                                <th class="text-end">Invoice Total</th>
                                <th class="text-end">Amount Paid</th>
                                <th class="text-end">This Payment</th>
                                <th class="text-end">Remaining Balance</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($receipt->allocations as $allocation)
                                @php
                                    $invoice = $allocation->invoice;
                                    $remaining = $invoice->TotalAmount - $invoice->AmountPaid;
                                @endphp
                                <tr>
                                    <td class="fw-medium">{{ $invoice->InvoiceNumber }}</td>
                                    <td>{{ $invoice->InvoiceDate->format('Y-m-d') }}</td>
                                    <td class="text-end">KSh {{ number_format($invoice->TotalAmount, 2) }}</td>
                                    <td class="text-end">KSh {{ number_format($invoice->AmountPaid, 2) }}</td>
                                    <td class="text-end fw-semibold text-success">
                                        KSh {{ number_format($allocation->AmountAllocated, 2) }}</td>
                                    <td class="text-end {{ $remaining <= 0 ? 'text-success' : 'text-warning' }}">
                                        KSh {{ number_format($remaining, 2) }}
                                        @if($remaining <= 0)
                                            <i class="fas fa-check-circle ms-1" title="Fully Paid"></i>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Additional Details -->
            <div class="row g-3 mt-3">
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small text-uppercase mb-2">Payment Details</div>
                        <div class="small">
                            <div class="row mb-2">
                                <div class="col-5"><strong>Payment Method:</strong></div>
                                <div
                                    class="col-7">{{ $receipt->paymentMethod->Description ?? $receipt->PaymentMethod }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5"><strong>Reference No:</strong></div>
                                <div class="col-7">{{ $receipt->ReferenceNumber ?: '—' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5"><strong>Value Date:</strong></div>
                                <div class="col-7">{{ $receipt->ValueDate->format('M d, Y') }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5"><strong>Posting Date:</strong></div>
                                <div class="col-7">{{ $receipt->PostingDate->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small text-uppercase mb-2">Remarks & Attachments</div>
                        <div class="small">
                            @if($receipt->Remarks)
                                <div class="mb-3">
                                    <strong>Remarks:</strong><br>
                                    {{ $receipt->Remarks }}
                                </div>
                            @endif
                            <strong>Attachments:</strong>
                            @php
                                $documents = $receipt->documents()->get(['t_Documents.Id','t_Documents.DocumentId','MimeType','Name']);
                            @endphp
                            @if($documents->count() > 0)
                                <div id="receiptAttachments">
                                    @foreach($documents as $document)
                                        @php $document->setRelations([]); @endphp
                                        {!! (new DocumentService($document))->summaryList() !!}
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted">No attachments</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail -->
            <div class="mt-4">
                <h6 class="text-muted mb-3">
                    <i class="fas fa-history text-info me-2"></i> Audit Trail
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                        <tr>
                            <td width="120" class="text-muted small">Created:</td>
                            <td>{{ $receipt->CreatedOn->format('M d, Y H:i') }}
                                by {{ $receipt->creator->name ?? 'System' }}</td>
                        </tr>
                        @if($receipt->ModifiedOn && $receipt->ModifiedOn != $receipt->CreatedOn)
                            <tr>
                                <td class="text-muted small">Modified:</td>
                                <td>{{ $receipt->ModifiedOn->format('M d, Y H:i') }}
                                    by {{ $receipt->modifier->name ?? 'System' }}</td>
                            </tr>
                        @endif
                        @if($receipt->Status === 'Posted')
                            <tr>
                                <td class="text-muted small">Posted:</td>
                                <td>{{ $receipt->ModifiedOn->format('M d, Y H:i') }}
                                    - {{ $receipt->ApprovalReason ?: 'Receipt posted to GL' }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Post Receipt Modal --}}
        @if($receipt->Status === 'Draft')
            <div class="modal fade" id="postReceiptModal" tabindex="-1" aria-labelledby="postReceiptModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('receiptsposting.approve', $receipt->Id) }}">
                        @csrf
                        <input type="hidden" name="action_type" value="approve">
                        <div class="modal-content rounded-4 shadow">
                            <div class="modal-header bg-light border-0">
                                <h5 class="modal-title text-success" id="postReceiptModalLabel">Post Receipt</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-3">Are you sure you want to post receipt
                                    <strong>{{ $receipt->ReceiptNumber }}</strong>?</p>
                                <p class="small text-muted mb-3">This will create GL entries and the receipt cannot be
                                    modified afterward.</p>
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Posting Reason</label>
                                    <textarea class="form-control" name="Reason" id="reason" rows="3" required
                                              placeholder="Enter reason for posting...">Receipt verified and ready for GL posting</textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-success" type="submit" id="postBtn"
                                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin me-1\'></i>Posting...'; this.form.submit();}">
                                    <i class="fas fa-check-circle me-1"></i> Post Receipt
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('styles')
    <style>
        :root {
            --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
        }
        body, .card, .table { font-family: var(--font-sans); }

        .card {
            border: none;
        }

        /* Attachment preview chip tweaks */
        #receiptAttachments .modal-preview-document {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            padding: .25rem .6rem;
            font-size: .85rem;
            line-height: 1.2;
            border-radius: 9999px;
            margin: .125rem .25rem .125rem 0;
        }

        #receiptAttachments .modal-preview-document:hover {
            filter: brightness(0.97);
        }
        @media print {
            body * { visibility: hidden; }

            #printRoot, #printRoot * {
                visibility: visible;
            }

            #printRoot {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            @page { size: A4 portrait; margin: 14mm; }

            .btn, .navbar {
                display: none !important;
            }

            .shadow-sm {
                box-shadow: none !important;
            }
        }
    </style>
@endsection

@section('scripts')
    @includeIf('snippets.actions.preview-files')
@endsection

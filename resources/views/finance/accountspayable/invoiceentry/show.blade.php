@extends('layouts.app')
@section('title', 'Invoice • ' . ($invoice->InvoiceNumber ?? 'View'))

@section('content')
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow rounded-4 border-0">
        <!-- Header -->
        <div class="p-4 p-md-5 border-bottom bg-light rounded-top-4"
             style="background: linear-gradient(135deg, #f8fafc, #eef2ff);">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge rounded-pill text-bg-info px-3 py-2">Payables Invoice</span>
                        @if($currencyCode)
                            <span class="badge rounded-pill text-bg-secondary px-3 py-2">{{ $currencyCode }}</span>
                        @endif
                        @if(!empty($invoice->ApprovalStatus))
                            <span
                                class="badge rounded-pill text-bg-{{ $invoice->ApprovalStatus === 'posted' ? 'success' : ($invoice->ApprovalStatus === 'rejected' ? 'danger' : 'warning') }} px-3 py-2">
                            {{ ucfirst($invoice->ApprovalStatus) }}
                        </span>
                        @endif
                    </div>
                    <h1 class="h3 mt-3 mb-1 d-flex align-items-center gap-2">
                        <i class="fas fa-file-invoice"></i> Invoice: <span class="fw-bold">{{ $invNo }}</span>
                        <button class="btn btn-sm btn-outline-secondary" id="copyInvBtn" title="Copy Invoice Number">
                            <i data-feather="copy"></i>
                        </button>
                    </h1>
                    <div class="text-muted">Dated <strong>{{ $invDate }}</strong></div>
                </div>

                <div class="text-md-end">
                    <div class="display-6 fw-semibold mb-1">
                        <span class="opacity-75">{{ $currencySymbol }} </span>{{ $amount }}
                    </div>
                    <div class="small text-muted">
                        Exchange Rate: <strong>{{ number_format($exRate, 4) }}</strong>
                    </div>
                    <div class="mt-3 d-flex gap-2 justify-content-md-end">
                        <a href="{{ route('invoiceentry.index') }}" class="btn btn-outline-secondary">
                            <i data-feather="arrow-left"></i> Back
                        </a>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#poItemsModal" @disabled(!$invoice->order)>
                            <i data-feather="file-text"></i> View PO Items
                        </button>
                        <button type="button" class="btn btn-success" onclick="window.print()">
                            <i data-feather="printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-4 p-md-5">
            <!-- Top info row -->
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="text-uppercase text-muted mb-0">Vendor</h6>
                                <i data-feather="user" class="opacity-50"></i>
                            </div>
                            <div class="fs-5 fw-semibold mt-2">{{ $vendorName }}</div>
                            <div class="mt-3 small">
                                <div class="text-muted">PO Reference</div>
                                <div class="fw-medium">{{ $poNo }}</div>
                            </div>
                            <div class="mt-2 small">
                                <div class="text-muted">GRN Reference</div>
                                <div class="fw-medium">{{ $grnNo }}</div>
                            </div>
                            @if(!empty($invoice->Description))
                                <div class="mt-3 small">
                                    <div class="text-muted mb-1">Description</div>
                                    <div class="border rounded-3 p-2 bg-light">{{ $invoice->Description }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="text-uppercase text-muted mb-0">Amounts</h6>
                                <i data-feather="pie-chart" class="opacity-50"></i>
                            </div>
                            <div class="table-responsive mt-3">
                                <table class="table align-middle mb-0">
                                    <tbody>
                                    <tr>
                                        <td class="text-muted">Invoice Amount</td>
                                        <td class="text-end">
                                            <strong>{{ $currencySymbol }} {{ number_format((float)($invoice->InvoiceAmount ?? 0), 2) }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">PO Subtotal (Items)</td>
                                        <td class="text-end">
                                            <span>{{ $currencySymbol }} {{ number_format($poSub, 2) }}</span>
                                        </td>
                                    </tr>
                                    @if(property_exists($invoice, 'TaxAmount') || isset($invoice->TaxAmount))
                                        <tr>
                                            <td class="text-muted">Tax Amount</td>
                                            <td class="text-end">
                                                <span>{{ $currencySymbol }} {{ number_format((float)($invoice->TaxAmount ?? 0), 2) }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    @if(property_exists($invoice, 'WithholdingTax') || isset($invoice->WithholdingTax))
                                        <tr>
                                            <td class="text-muted">Withholding</td>
                                            <td class="text-end">
                                                <span>- {{ $currencySymbol }} {{ number_format((float)($invoice->WithholdingTax ?? 0), 2) }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    @php
                                        $netPayable = (float)($invoice->InvoiceAmount ?? 0)
                                                      - (float)($invoice->WithholdingTax ?? 0);
                                    @endphp
                                    <tr class="table-light">
                                        <td class="fw-semibold">Net Payable</td>
                                        <td class="text-end fw-semibold">
                                            {{ $currencySymbol }} {{ number_format($netPayable, 2) }}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            @if(!empty($invoice->CurrencyID) && (float)$exRate !== 1.0)
                                <div class="small text-muted mt-2">
                                    *Converted using rate {{ number_format($exRate, 4) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 lifecycle">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="text-uppercase text-muted mb-0">Lifecycle</h6>
                                <i data-feather="activity" class="opacity-50"></i>
                            </div>

                            <ul class="list-unstyled mt-3 small">
                                <li class="d-flex gap-2 align-items-start">
                                    <i data-feather="clock" class="mt-1 opacity-50" width="16" height="16"></i>
                                    <div>
                                        <div class="text-muted">Created</div>
                                        <div class="fw-medium">
                                            {{ optional($invoice->CreatedOn)->format('d M Y, H:i') ?? '—' }}
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex gap-2 align-items-start mt-2">
                                    <i data-feather="edit-3" class="mt-1 opacity-50" width="16" height="16"></i>
                                    <div>
                                        <div class="text-muted">Last Updated</div>
                                        <div class="fw-medium">
                                            {{ optional($invoice->ModifiedOn)->format('d M Y, H:i') ?? '—' }}
                                        </div>
                                    </div>
                                </li>
                                @if(!empty($invoice->ApprovedOn))
                                    <li class="d-flex gap-2 align-items-start mt-2">
                                        <i data-feather="check-circle" class="mt-1 opacity-50" width="16"
                                           height="16"></i>
                                        <div>
                                            <div class="text-muted">Approved</div>
                                            <div class="fw-medium">
                                                {{ \Carbon\Carbon::parse($invoice->ApprovedOn)->format('d M Y, H:i') }}
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            </ul>

                            @if(!empty($invoice->file_path))
                                <a href="{{ \Storage::url($invoice->file_path) }}" target="_blank"
                                   class="btn btn-sm w-100 btn-outline-dark mt-2">
                                    <i data-feather="paperclip"></i> View Attachment
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items preview (inline table) -->
            <div class="card mt-4 border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Items Breakdown</h5>
                        <div class="text-muted small">From PO: <strong>{{ $poNo }}</strong></div>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 40%">Item</th>
                                <th class="text-end" style="width: 12%">Qty</th>
                                <th class="text-end" style="width: 18%">Unit Cost</th>
                                <th class="text-end" style="width: 18%">Line Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($poItems as $row)
                                @php
                                    $lineTotal = ((float)$row->UnitCost) * ((float)$row->Quantity);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $row->ItemName }}</div>
                                        @if(!empty($row->Description))
                                            <div class="text-muted small">{{ $row->Description }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format((float)$row->Quantity, 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol }} {{ number_format((float)$row->UnitCost, 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol }} {{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No PO items found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                            @if(count($poItems) > 0)
                                <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">PO Subtotal</th>
                                    <th class="text-end">{{ $currencySymbol }} {{ number_format($poSub, 2) }}</th>
                                </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <!-- Meta chips -->
            <div class="mt-4 d-flex flex-wrap gap-2">
            <span class="badge rounded-pill text-bg-light border">
                <i data-feather="hash"></i> Invoice ID: {{ $invoice->Id ?? $invoice->id ?? '—' }}
            </span>
                @if(!empty($invoice->CreatedBy))
                    <span class="badge rounded-pill text-bg-light border">
                <i data-feather="user-check"></i> Created By: <b> {{ $invoice->createdBy->Name }}</b>
            </span>
                @endif
            </div>
        </div>
    </div>

    @if($invoice->ApprovalStatus==='draft')
        <div class="mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#approveModal" @disabled(!empty($invoice->Status) && $invoice->Status === 'Approved')>
                <i data-feather="thumbs-up"></i> Approve
            </button>

            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"
                    @disabled(!empty($invoice->Status) && $invoice->Status === 'Rejected')}>
                <i data-feather="thumbs-down"></i> Reject
            </button>
        </div>
    @endif

    <!-- PO Items Modal (detailed view) -->
    <div class="modal fade" id="poItemsModal" tabindex="-1" aria-labelledby="poItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="poItemsModalLabel">Purchase Order {{ $poNo }} — Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php $i=1; @endphp
                            @forelse($poItems as $row)
                                @php $lt = ((float)$row->UnitCost) * ((float)$row->Quantity); @endphp
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $row->ItemName }}</td>
                                    <td class="small text-muted">{{ $row->Description ?? '—' }}</td>
                                    <td class="text-end">{{ number_format((float)$row->Quantity, 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol }}{{ number_format((float)$row->UnitCost, 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol }}{{ number_format($lt, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No items available.</td>
                                </tr>
                            @endforelse
                            </tbody>
                            @if(count($poItems) > 0)
                                <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">PO Subtotal</th>
                                    <th class="text-end">{{ $currencySymbol }}{{ number_format($poSub, 2) }}</th>
                                </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @if($invoice->ApprovalStatus==='draft')
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Approve
                            Invoice {{ $invoice->InvoiceNumber ?? ($invoice->Id ?? $invoice->id) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('ap.invoice.approve', $invoice->Id ?? $invoice->id) }}" method="POST"
                          id="approveForm">
                        @csrf
                        @method('POST')
                        <div class="modal-body">
                            <div class="alert alert-info small">
                                You’re about to approve this invoice.
                                <div class="mt-1">
                                    <strong>Amount:</strong> {{ ($invoice->currency->Symbol ?? '') . number_format((float)($invoice->InvoiceAmount ?? 0), 2) }}
                                </div>
                                @if(!empty($invoice->order?->OrderNo))
                                    <div><strong>PO:</strong> {{ $invoice->order->OrderNo }}</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reason / Comment <span class="text-danger">*</span></label>
                                <textarea name="Reason" class="form-control" rows="3"
                                          placeholder="Add an approval note for audit trail" required></textarea>
                                <div class="form-text">This will be stored in the approval history.</div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel
                            </button>
                            <button class="btn btn-success" id="approveProceedBtn" type="submit"
                                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                                <span class="default-label"><i
                                        class="fas fa-check-circle"></i> Proceed to Approve</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject
                            Invoice {{ $invoice->InvoiceNumber ?? ($invoice->Id ?? $invoice->id) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('ap.invoice.reject', $invoice->Id ?? $invoice->id) }}" method="POST"
                          id="rejectForm">
                        @csrf
                        @method('POST')
                        <div class="modal-body">
                            <div class="alert alert-warning small">
                                You’re about to reject this invoice.
                                <div class="mt-1">
                                    <strong>Amount:</strong> {{ ($invoice->currency->Symbol ?? '') . number_format((float)($invoice->InvoiceAmount ?? 0), 2) }}
                                </div>
                                @if(!empty($invoice->order?->OrderNo))
                                    <div><strong>PO:</strong> {{ $invoice->order->OrderNo }}</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reason for Rejection <span
                                        class="text-danger">*</span></label>
                                <textarea name="Reason" class="form-control" rows="3"
                                          placeholder="Provide a clear reason for rejection" required></textarea>
                                <div class="form-text">This will be shared with the originator and stored in the
                                    history.
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel
                            </button>
                            <button class="btn btn-danger" id="rejectProceedBtn" type="submit"
                                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                                <span class="default-label"><i class="fas fa-check-circle"></i> Proceed to Reject</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endif

@endsection

@section('scripts')
    <script>
        // Feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Copy invoice number
        document.getElementById('copyInvBtn')?.addEventListener('click', function () {
            const txt = @json($invNo);
            navigator.clipboard.writeText(txt).then(() => {
                const btn = this;
                const original = btn.innerHTML;
                btn.innerHTML = '<i data-feather="check"></i>';
                feather.replace();
                setTimeout(() => {
                    btn.innerHTML = original;
                    feather.replace();
                }, 1200);
            });
        });

        // Nice print styles (hide nav/buttons on print)
        const printCSS = `
        @media print {
            .navbar, .btn,.lifecycle, .modal { display: none !important; }
            .card { box-shadow: none !important; border: none !important; }
            a[href]:after { content: ""; }
        }
    `;
        const style = document.createElement('style');
        style.innerHTML = printCSS;
        document.head.appendChild(style);
    </script>
@endsection

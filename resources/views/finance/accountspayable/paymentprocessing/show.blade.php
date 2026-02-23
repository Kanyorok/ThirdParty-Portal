@extends('layouts.app')
@section('title', 'Voucher Details')

@section('content')
    <div class="container mt-3">
        <div class="card shadow-sm p-4 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-muted">
                    <i class="fas fa-file-invoice-dollar text-info"></i> Voucher Details
                </h5>
                {{--                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">--}}
                {{--                    <i class="fas fa-print"></i> Print--}}
                {{--                </button>--}}
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
                    <strong>Invoice Mode:</strong>
                    <span class="badge {{ strtoupper(($voucher->invoice?->InvoiceSourceType ?? 'PO')) === 'CONTRACT' ? 'bg-info text-dark' : 'bg-secondary' }}">
                        {{ strtoupper($voucher->invoice?->InvoiceSourceType ?? 'PO') }}
                    </span>
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
                    <span class="badge {{ $voucher->IsProcessed ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ ucfirst($voucher->IsProcessed?'Processed':'Pending Processing') }}
                </span>
                </div>
                @if(($voucher->invoice?->IsOnHold ?? false))
                    <div class="col-md-12 mt-2">
                        <div class="alert alert-danger py-2 mb-0">
                            <strong>Exception:</strong> {{ $voucher->invoice?->HoldReason ?? 'Contract milestone hold' }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Supplier / Payee Info --}}
            <h6 class="text-muted mb-2">Payee Information</h6>
            <div class="row border-bottom pb-2 mb-3">
                <div class="col-md-6">
                    <strong>Supplier Name:</strong>
                    <span>{{ ($voucher->invoice->thirdParty->TradingName ?? $voucher->invoice->thirdParty->ThirdPartyName) ?? 'N/A' }}</span>
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
                    <td class="text-end">{{ number_format($invoiceReferenceAmount ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Cumulative Amount Paid</td>
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
                        {{ number_format($invoiceBalance ?? 0, 2) }}
                    </td>
                </tr>
                </tbody>
            </table>

            @php
                $previewInvoice = $invoicePayload['invoice'] ?? [];
                $previewSourceType = strtoupper((string)($previewInvoice['source_type'] ?? 'PO'));
                $previewAttachments = $invoicePayload['attachments'] ?? [];
                $previewContract = $invoicePayload['contract'] ?? null;
                $previewPo = $invoicePayload['po'] ?? null;
                $previewCurrency = $previewInvoice['currency_symbol'] ?? ($voucher->invoice->currency->Code ?? 'KES');
            @endphp

            @if(!empty($previewInvoice))
                <h6 class="text-muted mt-4 mb-2">Invoice Context for Processing</h6>

                <div class="card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <strong>[{{ $previewSourceType }}] {{ $previewInvoice['invoice_number'] ?? 'N/A' }}</strong>
                        @if(!empty($previewInvoice['view_url']))
                            <a class="small" href="{{ $previewInvoice['view_url'] }}" target="_blank" rel="noopener">Open invoice</a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="small text-muted">Before Tax</div>
                                <div class="fw-semibold">{{ $previewCurrency }} {{ number_format((float)($previewInvoice['before_tax'] ?? 0), 2) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Tax ({{ number_format((float)($previewInvoice['tax_percentage'] ?? 0), 2) }}%)</div>
                                <div class="fw-semibold">{{ $previewCurrency }} {{ number_format((float)($previewInvoice['tax_amount'] ?? 0), 2) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Invoice Total</div>
                                <div class="fw-semibold">{{ $previewCurrency }} {{ number_format((float)($previewInvoice['total_amount'] ?? 0), 2) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Remaining Balance</div>
                                <div class="fw-semibold">{{ $previewCurrency }} {{ number_format((float)($previewInvoice['balance'] ?? 0), 2) }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">Attachments</div>
                            @if(!empty($previewAttachments))
                                <ul class="mb-0 ps-3">
                                    @foreach($previewAttachments as $attachment)
                                        <li class="small">
                                            {{ $attachment['name'] ?? ($attachment['document_id'] ?? 'Attachment') }}
                                            @if(!empty($attachment['mime_type']))
                                                <span class="text-muted">({{ $attachment['mime_type'] }})</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted small">No attachments on this invoice.</span>
                            @endif
                        </div>

                        @if($previewSourceType === 'CONTRACT')
                            <div class="small text-muted mb-2">Milestones and Checklist</div>
                            <div class="small mb-2">
                                <span class="text-muted">Contract Ref:</span>
                                <strong>{{ $previewContract['reference'] ?? 'N/A' }}</strong>
                            </div>
                            @if(!empty($previewContract['milestones']))
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Milestone</th>
                                            <th>Status</th>
                                            <th>Due Date</th>
                                            <th>Checklist</th>
                                            <th class="text-end">Billed</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($previewContract['milestones'] as $milestone)
                                            <tr>
                                                <td><strong>M{{ $milestone['milestone_no'] ?? 0 }} - {{ $milestone['title'] ?? 'Milestone' }}</strong></td>
                                                <td>{{ $milestone['status'] ?? 'N/A' }}</td>
                                                <td>{{ $milestone['due_date'] ?? 'N/A' }}</td>
                                                <td>{{ $milestone['required_checklist_fulfilled'] ?? 0 }}/{{ $milestone['required_checklist_total'] ?? 0 }}</td>
                                                <td class="text-end">{{ $previewCurrency }} {{ number_format((float)($milestone['billed_amount'] ?? 0), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="bg-light-subtle">
                                                    @if(!empty($milestone['checklist_items']))
                                                        <ul class="mb-0 ps-3">
                                                            @foreach($milestone['checklist_items'] as $item)
                                                                <li class="small mb-1">
                                                                    {{ $item['description'] ?? 'Checklist item' }}
                                                                    <span class="badge {{ !empty($item['fulfilled']) ? 'bg-success' : 'bg-warning text-dark' }}">
                                                                        {{ !empty($item['fulfilled']) ? 'Fulfilled' : 'Pending' }}
                                                                    </span>
                                                                    <span class="text-muted">[{{ !empty($item['required']) ? 'Required' : 'Optional' }}]</span>
                                                                    @if(!empty($item['notes']))
                                                                        <span class="text-muted">({{ $item['notes'] }})</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted small">No checklist items.</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <span class="text-muted small">No milestone allocations found for this contract invoice.</span>
                            @endif
                        @else
                            <div class="small text-muted mb-2">Matched PO and GRN</div>
                            <div class="row g-3 mb-2">
                                <div class="col-md-6">
                                    <div class="border rounded p-2 h-100">
                                        <div class="small mb-1"><span class="text-muted">PO Number:</span> <strong>{{ $previewPo['order']['order_no'] ?? 'N/A' }}</strong></div>
                                        <div class="small mb-1"><span class="text-muted">PO Date:</span> {{ $previewPo['order']['order_date'] ?? 'N/A' }}</div>
                                        <div class="small mb-1"><span class="text-muted">Before Tax:</span> {{ $previewCurrency }} {{ number_format((float)($previewPo['order']['before_tax'] ?? 0), 2) }}</div>
                                        <div class="small mb-1"><span class="text-muted">Tax %:</span> {{ number_format((float)($previewPo['order']['tax_percentage'] ?? 0), 2) }}%</div>
                                        <div class="small"><span class="text-muted">After Tax:</span> {{ $previewCurrency }} {{ number_format((float)($previewPo['order']['after_tax'] ?? 0), 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-2 h-100">
                                        <div class="small mb-1"><span class="text-muted">GRN:</span> <strong>{{ $previewPo['grn']['grn_id'] ?? 'N/A' }}</strong></div>
                                        <div class="small mb-1"><span class="text-muted">Received Date:</span> {{ $previewPo['grn']['received_date'] ?? 'N/A' }}</div>
                                        <div class="small mb-1"><span class="text-muted">Ordered Qty:</span> {{ number_format((float)($previewPo['grn']['ordered_qty_total'] ?? 0), 2) }}</div>
                                        <div class="small"><span class="text-muted">Received Qty:</span> {{ number_format((float)($previewPo['grn']['received_qty_total'] ?? 0), 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            @if(!empty($previewPo['grn']['items']))
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-end">PO Qty</th>
                                            <th class="text-end">Received Qty</th>
                                            <th class="text-center">Match</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($previewPo['grn']['items'] as $grnItem)
                                            @php
                                                $poQty = (float) ($grnItem['po_qty'] ?? 0);
                                                $receivedQty = (float) ($grnItem['received_qty'] ?? 0);
                                            @endphp
                                            <tr>
                                                <td>{{ $grnItem['item_name'] ?? 'Item' }}</td>
                                                <td class="text-end">{{ number_format($poQty, 2) }}</td>
                                                <td class="text-end">{{ number_format($receivedQty, 2) }}</td>
                                                <td class="text-center">
                                                    <span class="badge {{ abs($poQty - $receivedQty) <= 0.0001 ? 'bg-success' : 'bg-warning text-dark' }}">
                                                        {{ abs($poQty - $receivedQty) <= 0.0001 ? 'Matched' : 'Variance' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            {{-- Remarks Section --}}
            @if(!empty($voucher->Description))
                <div class="mt-4">
                    <h6 class="text-muted mb-2">Remarks:</h6>
                    <i> {{ $voucher->Description }}</i>
                </div>
            @endif

            @if(($voucher->invoice?->contractPenaltyEvents ?? collect())->count() > 0)
                <div class="mt-4">
                    <h6 class="text-muted mb-2">Penalty Events</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th class="text-end">Computed</th>
                                <th class="text-end">Applied</th>
                                <th>Reason</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($voucher->invoice?->contractPenaltyEvents ?? [] as $event)
                                <tr>
                                    <td>{{ $event->Status }}</td>
                                    <td class="text-end">{{ number_format($event->ComputedAmount ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($event->AppliedAmount ?? 0, 2) }}</td>
                                    <td>{{ $event->Reason ?? '—' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Approve / Reject Buttons --}}
            @if(!$voucher->IsProcessed)
                @php
                    $contractHoldPending = strtoupper(($voucher->invoice?->InvoiceSourceType ?? 'PO')) === 'CONTRACT'
                        && ($voucher->invoice?->IsOnHold ?? false);
                @endphp
                <div class="mt-4 d-flex gap-3">
                    <button class="btn btn-success"
                            data-bs-toggle="modal"
                            data-bs-target="#approveModal"
                            @if($contractHoldPending) disabled @endif>
                        <i class="fas fa-check-circle me-1"></i> Process Voucher
                    </button>
                </div>

                @if(($voucher->invoice?->IsOnHold ?? false))
                    <div class="alert alert-warning mt-3 mb-2">
                        Milestone checklist is pending for this contract invoice. Apply penalty or waive hold first, then process the voucher.
                    </div>
                    @if(strtoupper(($voucher->invoice?->InvoiceSourceType ?? 'PO')) === 'CONTRACT')
                        <div class="card border-warning">
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <form action="{{ route('paymentvoucher.contracts.apply-penalty', $voucher->invoice->Id) }}" method="POST" class="d-flex gap-2 align-items-end">
                                            @csrf
                                            <div class="flex-grow-1">
                                                <label class="form-label form-label-sm">Penalty Amount</label>
                                                <input type="number"
                                                       name="penalty_amount"
                                                       class="form-control form-control-sm"
                                                       min="0"
                                                       step="0.01"
                                                       value="{{ (float)($voucher->invoice->PenaltySuggestedAmount ?? 0) }}">
                                            </div>
                                            <div class="flex-grow-1">
                                                <label class="form-label form-label-sm">Reason (optional)</label>
                                                <input type="text"
                                                       name="reason"
                                                       class="form-control form-control-sm"
                                                       maxlength="500"
                                                       placeholder="Penalty application reason">
                                            </div>
                                            <button class="btn btn-sm btn-outline-danger">Apply Penalty + Release</button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <form action="{{ route('paymentvoucher.contracts.waive-hold', $voucher->invoice->Id) }}" method="POST" class="d-flex gap-2 align-items-end">
                                            @csrf
                                            <div class="flex-grow-1">
                                                <label class="form-label form-label-sm">Waiver Reason</label>
                                                <input type="text"
                                                       name="reason"
                                                       class="form-control form-control-sm"
                                                       maxlength="500"
                                                       required
                                                       placeholder="Reason for waiving hold">
                                            </div>
                                            <button class="btn btn-sm btn-outline-secondary">Waive Hold</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('voucher.post', $voucher->Id) }}" method="POST">
                @csrf
                @method('POST')
                <input type="hidden" name="InvoiceID" value="{{$voucher->invoice->Id}}">
                <input type="hidden" name="VoucherID" value="{{$voucher->Id}}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Processing</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="approveReason" class="form-label">Remarks</label>
                        <textarea class="form-control" name="Reason" rows="3" placeholder="Remarks..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success" id="approveProceedBtn" type="submit"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                            <span class="default-label"><i class="fas fa-check-circle"></i> Proceed</span>
                        </button>
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
                        <textarea class="form-control" name="Reasons" rows="3" required
                                  placeholder="Required reason..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" id="rejectProceedBtn" type="submit"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                            <span class="default-label"><i class="fas fa-check-circle"></i> Proceed to Reject</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

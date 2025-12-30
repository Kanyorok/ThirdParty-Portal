@extends('layouts.app')
@section('title', 'Invoice • '.$invoice->InvoiceNumber)

@section('content')

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 mb-3" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow rounded-4 border-0 invoice-page" id="invoice-print-section">
        <!-- Header -->
        <div class="p-3 p-md-4 border-bottom bg-light rounded-top-4 avoid-break invoice-header"
             style="background: linear-gradient(135deg, #f8fafc, #eef2ff);">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <img src="{{asset('assets/img/icons/craft-logo.png')}}" alt="Logo" style="height:28px" class="rounded bg-white p-1">
                    <div>
                        <div class="fw-bold">Craft Silicon Limited</div>
                        <div class="small text-muted">Financial Technology • Core Banking • Digital Channels</div>
                    </div>
                </div>
                <div class="text-md-end invoice-meta">
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
            <div class="row g-3 print-row">
                <!-- Bill To -->
                <div class="col-lg-4 print-col">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body py-3">
                            <h6 class="text-uppercase text-muted mb-0">Bill To</h6>
                            <div class="fs-6 fw-semibold mt-2">{{ $invoice->customer->ThirdPartyName ?? '—' }}</div>
                            <div class="small">{{ $invoice->customer->PhysicalAddress ?? '—' }}</div>
                            <div class="small text-muted mt-2">Email: <span
                                    class="text-dark">{{ $invoice->customer->Email ?? '—' }}</span></div>
                            <div class="small text-muted">Phone: <span
                                    class="text-dark">{{ $invoice->customer->Phone ?? '—' }}</span></div>
                            <div class="small text-muted">Reg No.: <span
                                    class="text-dark">{{ $invoice->customer->RegistrationNumber ?? '—' }}</span></div>
                            <div class="small text-muted">Country: <span
                                    class="text-dark">{{ $invoice->customer->country?->Name ?? '—' }}</span></div>
                            <div class="mt-2 small text-muted">Request: {{ $invoice->RequestID }}</div>
                        </div>
                    </div>
                </div>

                <!-- From -->
                <div class="col-lg-4 print-col">
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
                <div class="col-lg-4 print-col">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body py-3">
                            <h6 class="text-uppercase text-muted mb-0">Summary</h6>
                            @php
                                $subtotal =(float) ($invoice->InvoiceAmount ?? ($subtotal + $taxAmount));
                                $taxAmount = (float) ($invoice->TaxAmount ?? 0);
                                $taxRate = $invoice->TaxPercentage;
                                $grandTotal = (float) ($invoice->TotalAmount ?? 0);
                            @endphp
                            <table class="table align-middle mb-0 mt-2 summary-table">
                                <tbody>
                                <tr>
                                    <td class="text-muted">Subtotal</td>
                                    <td class="text-end"><strong>{{ $invoice->currency->Symbol }} {{ number_format($subtotal, 2) }}</strong></td>
                                </tr>
                                @if($taxAmount !== 0)
                                    <tr>
                                        <td class="text-muted">
                                            Tax
                                            @if(!is_null($taxRate))
                                                <span class="small text-uppercase">({{ rtrim(rtrim(number_format($taxRate, 4), '0'), '.') }}%)</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            {{ $invoice->currency->Symbol }} {{ number_format($taxAmount, 2) }}
                                        </td>
                                    </tr>
                                @endif
                                <tr class="table-light">
                                    <td class="fw-semibold">Grand Total</td>
                                    <td class="text-end fw-semibold">{{ $invoice->currency->Symbol }} {{ number_format($grandTotal, 2) }}</td>
                                </tr>
                                @if($invoice->UseCredit ?? false)
                                    <tr class="table-success">
                                        <td><i class="fas fa-credit-card me-1"></i> Credit Applied</td>
                                        <td class="text-end text-success">
                                            <strong>{{ $invoice->currency->Symbol }} {{ number_format($subtotal, 2) }}</strong>
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>

                            {{-- Credit Information --}}
                            @if($creditInfo && $creditInfo['hasCredit'])
                                <div class="mt-3 p-2 bg-light rounded-3">
                                    <h6 class="small text-muted mb-2">Customer Credit Info</h6>
                                    <div class="row g-1 small">
                                        <div class="col-6">Credit Limit:</div>
                                        <div
                                            class="col-6 text-end fw-semibold">{{ $invoice->currency->Symbol }} {{ number_format($creditInfo['creditLimit'], 2) }}</div>
                                        <div class="col-6">Available:</div>
                                        <div
                                            class="col-6 text-end fw-semibold text-success">{{ $invoice->currency->Symbol }} {{ number_format($creditInfo['available'], 2) }}</div>
                                        <div class="col-6">Utilization:</div>
                                        <div class="col-6 text-end">
                                            <span
                                                class="badge {{ $creditInfo['utilization'] > 80 ? 'bg-danger' : ($creditInfo['utilization'] > 60 ? 'bg-warning' : 'bg-success') }}">
                                                {{ number_format($creditInfo['utilization'], 1) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @elseif($creditInfo && !$creditInfo['hasCredit'])
                                <div class="mt-3 p-2 bg-light rounded-3">
                                    <div class="small text-muted">
                                        <i class="fas fa-info-circle me-1"></i>{{ $creditInfo['message'] }}
                                    </div>
                                </div>
                            @endif

                            <div class="mt-3 d-grid gap-2">
                                @if($invoice->ApprovalStatus==='draft')
                                    {{-- Credit Management Buttons --}}
                                    @if($creditInfo && $creditInfo['hasCredit'])
                                        @if(!($invoice->UseCredit ?? false))
                                            @if($creditInfo['canApplyCredit'])
                                                <button type="button" class="btn btn-outline-info btn-sm"
                                                        data-bs-toggle="modal" data-bs-target="#applyCreditModal">
                                                    <i class="fas fa-credit-card me-1"></i> Apply Customer Credit
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-secondary btn-sm" disabled
                                                        title="Insufficient credit available">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> Insufficient Credit
                                                </button>
                                            @endif
                                        @else
                                            <div
                                                class="alert alert-success py-2 px-3 mb-3 rounded-3 border-0 shadow-sm">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <strong>Credit Applied!</strong> Customer credit has been applied to
                                                this invoice.
                                                <button type="button" class="btn btn-outline-warning btn-sm ms-2"
                                                        data-bs-toggle="modal" data-bs-target="#removeCreditModal">
                                                    <i class="fas fa-times me-1"></i> Remove Credit
                                                </button>
                                            </div>
                                        @endif
                                    @endif

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
            <div class="card mt-3 border-0 shadow-sm rounded-4 avoid-break">
                <div class="card-body py-3">
                            <h6 class="mb-0">Items</h6>
                    <table class="table table-hover align-middle mt-2 invoice-items-table">
                        <thead class="table-light">
                        <tr>
                            <th></th>
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
                                <td></td>
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
                        @php
                            $taxName = optional($invoice->taxRule?->taxType)->TaxTypeName;
                            $displayTax = !is_null($invoice->TaxAmount) ? (float) $invoice->TaxAmount : $totalTax;
                        @endphp
                        <tr>
                            <th colspan="6"></th>
                            <th class="text-end">
                                <div>Tax</div>
                                @if($taxName)
                                    <div class="small text-muted">{{ $taxName }}</div>
                                @endif
                            </th>
                            <th class="text-end">{{ $invoice->currency->Symbol }} {{ number_format($displayTax, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">Grand Total</th>
                            <th class="text-end">{{ $invoice->currency->Symbol }} {{ number_format($invoice->TotalAmount, 2) }}</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Remarks + Footer meta in columns -->
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body py-3">
                            <h6 class="mb-2">Invoice Remarks</h6>
                            <div>{{ $invoice->InvoiceRemarks ?? '—' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body py-3 small">
                            <h6 class="mb-2">Document Meta</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="border rounded-3 p-2">
                                        <div class="text-muted">Prepared On</div>
                                        <div class="fw-medium">{{ \Carbon\Carbon::parse($invoice->CreatedOn)->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-2">
                                        <div class="text-muted">Modified On</div>
                                        <div class="fw-medium">{{ \Carbon\Carbon::parse($invoice->ModifiedOn)->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tear-off Remittance -->
            <hr class="my-3 print-only">
            <div class="border rounded-3 p-2 print-only small break-before">
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
            <div class="sys-gen-note print-only">
                This document is system-generated by BR_ERP on {{ now()->format('Y-m-d H:i') }} and does not require a physical signature.
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

    <!-- Apply Credit Modal -->
    @if($creditInfo && $creditInfo['hasCredit'] && $creditInfo['canApplyCredit'])
        <div class="modal fade" id="applyCreditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title">Apply Customer Credit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('invoicegeneration.apply-credit', $invoice->Id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="apply_credit" value="1">
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Credit Application Summary</h6>
                                <div class="row g-2 small">
                                    <div class="col-6"><strong>Invoice Amount:</strong></div>
                                    <div
                                        class="col-6 text-end">{{ $invoice->currency->Symbol }} {{ number_format($invoice->TotalAmount, 2) }}</div>
                                    <div class="col-6"><strong>Available Credit:</strong></div>
                                    <div
                                        class="col-6 text-end text-success">{{ $invoice->currency->Symbol }} {{ number_format($creditInfo['available'], 2) }}</div>
                                    <div class="col-6"><strong>Remaining After:</strong></div>
                                    <div
                                        class="col-6 text-end">{{ $invoice->currency->Symbol }} {{ number_format($creditInfo['available'] - $invoice->TotalAmount, 2) }}</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason for Applying Credit <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" rows="3" required
                                          placeholder="Enter reason for applying customer credit to this invoice..."></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="proceed_to_approval" value="1"
                                           id="proceedToApproval">
                                    <label class="form-check-label" for="proceedToApproval">
                                        <strong>Apply credit and proceed to approval</strong>
                                        <div class="small text-muted">Check this to apply credit and immediately
                                            approve/post the invoice
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="small text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                This will apply the customer's credit against this invoice amount and update their
                                available credit balance.
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
                                <i class="fas fa-credit-card me-1"></i>
                                <span id="applyCreditBtnText">Apply Credit</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Remove Credit Modal -->
    @if($invoice->UseCredit ?? false)
        <div class="modal fade" id="removeCreditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title">Remove Credit Application</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('invoicegeneration.apply-credit', $invoice->Id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="apply_credit" value="0">
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">Remove Credit Application</h6>
                                <p class="mb-2">You are about to remove the credit application from this invoice.</p>
                                <div class="row g-2 small">
                                    <div class="col-6"><strong>Invoice Amount:</strong></div>
                                    <div
                                        class="col-6 text-end">{{ $invoice->currency->Symbol }} {{ number_format($invoice->TotalAmount, 2) }}</div>
                                    <div class="col-6"><strong>Will be restored to available credit</strong></div>
                                    <div
                                        class="col-6 text-end text-success">{{ $invoice->currency->Symbol }} {{ number_format($invoice->TotalAmount, 2) }}</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason for Removing Credit <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" rows="3" required
                                          placeholder="Enter reason for removing credit application..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-times me-1"></i> Remove Credit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('styles')
    <style>
        @page { size: A4 portrait; margin: 2mm 8mm 8mm 8mm; }
        @media print {
            html, body { margin: 0 !important; padding: 0 !important; }
            body * { visibility: hidden; }
            #invoice-print-section, #invoice-print-section * { visibility: visible; }
            #invoice-print-section { position: relative; width: 194mm; margin: 0 auto; font-size: 11px; }
            .navbar, .btn, .modal, .pagination { display: none !important; }
            .card, .shadow { box-shadow: none !important; }
            .invoice-page { border: 1px solid #000 !important; }
            .table { font-size: 11px; }
            .print-only { display: block !important; }
            .avoid-break { page-break-inside: avoid; }
            .break-before { page-break-before: always; }
            .invoice-header { padding: 2mm 6mm !important; }
            .invoice-meta .badge { padding: 2px 6px !important; font-size: 10px; }
            .print-row { display: flex; gap: 6mm; }
            .print-col { flex: 1 1 0; }
            .summary-table td, .summary-table th { padding: 2px 6px !important; }
            .sys-gen-note { position: fixed; bottom: 4mm; left: 8mm; right: 8mm; text-align: center; font-size: 9.5px; color: #666; }
        }
        .print-only { display: none; }
        .invoice-items-table { table-layout: fixed; }
        .invoice-items-table th:nth-child(2),
        .invoice-items-table td:nth-child(2) { width: 18%; }
        .invoice-items-table th:nth-child(3),
        .invoice-items-table td:nth-child(3) { width: 32%; }
        .invoice-items-table th:nth-child(4),
        .invoice-items-table td:nth-child(4) { width: 7%; }
        .invoice-items-table th:nth-child(5),
        .invoice-items-table td:nth-child(5) { width: 12%; }
        .invoice-items-table th:nth-child(6),
        .invoice-items-table td:nth-child(6) { width: 7%; }
        .invoice-items-table th:nth-child(7),
        .invoice-items-table td:nth-child(7) { width: 10%; }
        .invoice-items-table th:nth-child(8),
        .invoice-items-table td:nth-child(8) { width: 14%; }
    </style>
@endsection

@section('scripts')
    <script>
        // Dynamic button text for apply credit modal
        document.getElementById('proceedToApproval').addEventListener('change', function () {
            const btnText = document.getElementById('applyCreditBtnText');
            const btn = document.getElementById('applyCreditBtn');

            if (this.checked) {
                btnText.textContent = 'Apply Credit & Approve';
                btn.className = 'btn btn-success';
                btn.querySelector('i').className = 'fas fa-check-double me-1';
            } else {
                btnText.textContent = 'Apply Credit';
                btn.className = 'btn btn-info';
                btn.querySelector('i').className = 'fas fa-credit-card me-1';
            }
        });

        // Handle apply credit form submission with button protection
        document.getElementById('applyCreditBtn').addEventListener('click', function (e) {
            // Disable button to prevent double submission
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

            // Re-enable after 10 seconds as failsafe
            setTimeout(() => {
                this.disabled = false;
                const isApproval = document.getElementById('proceedToApproval').checked;
                if (isApproval) {
                    this.innerHTML = '<i class="fas fa-check-double me-1"></i> Apply Credit & Approve';
                } else {
                    this.innerHTML = '<i class="fas fa-credit-card me-1"></i> Apply Credit';
                }
            }, 10000);
        });

        // Similar protection for approve modal button (if it exists)
        document.addEventListener('DOMContentLoaded', function () {
            const approveBtn = document.querySelector('#approveModal button[type="submit"]');
            if (approveBtn) {
                approveBtn.addEventListener('click', function () {
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

                    // Re-enable after 5 seconds as failsafe
                    setTimeout(() => {
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-thumbs-up me-1"></i> Approve';
                    }, 5000);
                });
            }
        });
    </script>
@endsection

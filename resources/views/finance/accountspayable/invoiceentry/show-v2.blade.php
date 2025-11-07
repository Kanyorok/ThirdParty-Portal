@extends('layouts.app')
@section('title','Invoice Details')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-file-invoice text-info me-2"></i> Invoice Details
                </h6>
                <div class="d-flex gap-2">
                    @if($invoice->Status === 'Draft')
                        <a href="{{ route('invoiceentry.edit', $invoice->Id) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('invoiceentry.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Status Badge -->
                <div class="text-center mb-4">
                    @if($invoice->Status === 'Draft')
                        <span class="badge bg-secondary fs-6 px-3 py-2">Draft</span>
                    @elseif($invoice->Status === 'Approved')
                        <span class="badge bg-success fs-6 px-3 py-2">Approved</span>
                    @elseif($invoice->Status === 'Rejected')
                        <span class="badge bg-danger fs-6 px-3 py-2">Rejected</span>
                    @else
                        <span class="badge bg-warning fs-6 px-3 py-2">{{ $invoice->Status }}</span>
                    @endif
                </div>

                <div class="row g-4">
                    <!-- Left Column: Invoice Information -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small text-uppercase mb-2">Invoice Information</div>

                            <div class="row mb-2">
                                <div class="col-5"><strong>Invoice Number:</strong></div>
                                <div class="col-7">{{ $invoice->InvoiceNumber }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-5"><strong>Invoice Date:</strong></div>
                                <div
                                    class="col-7">{{ $invoice->InvoiceDate ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('d M Y') : '—' }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-5"><strong>Due Date:</strong></div>
                                <div
                                    class="col-7">{{ $invoice->DueDate ? \Carbon\Carbon::parse($invoice->DueDate)->format('d M Y') : '—' }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-5"><strong>Amount:</strong></div>
                                <div class="col-7">
                                    <span class="h5 text-primary">KSh {{ number_format($invoice->Amount, 2) }}</span>
                                </div>
                            </div>

                            @if($invoice->Description)
                                <div class="row mb-2">
                                    <div class="col-5"><strong>Description:</strong></div>
                                    <div class="col-7">{{ $invoice->Description }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Column: Supplier Information -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small text-uppercase mb-2">Supplier Information</div>

                            <div class="row mb-2">
                                <div class="col-4"><strong>Name:</strong></div>
                                <div
                                    class="col-8">{{ $invoice->thirdParty->TradingName ?? optional($item->thirdParty)->ThirdPartyName ?? '—' }}</div>
                            </div>

                            @if($invoice->thirdParty->RegistrationNumber)
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Reg No:</strong></div>
                                    <div class="col-8">{{ $invoice->thirdParty->RegistrationNumber }}</div>
                                </div>
                            @endif

                            @if($invoice->thirdParty->Email)
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Email:</strong></div>
                                    <div class="col-8">{{ $invoice->thirdParty->Email }}</div>
                                </div>
                            @endif

                            @if($invoice->thirdParty->Phone)
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Phone:</strong></div>
                                    <div class="col-8">{{ $invoice->thirdParty->Phone }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- References Section -->
                <div class="row g-4 mt-3">
                    <div class="col-12">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small text-uppercase mb-2">Reference Information</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>PO Reference:</strong></div>
                                        <div class="col-8">{{ $invoice->POReference }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>GRN Reference:</strong></div>
                                        <div class="col-8">{{ $invoice->GRNReference }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Section -->
                @php
                    try {
                        $documents = \DB::table('t_Documents')
                            ->join('t_DocumentRelations', 't_Documents.Id', '=', 't_DocumentRelations.DocumentID')
                            ->where('t_DocumentRelations.RelatedEntityType', 'FinanceInvoiceEntry')
                            ->where('t_DocumentRelations.RelatedEntityID', $invoice->Id)
                            ->select('t_Documents.Id', 't_Documents.DocumentId', 't_Documents.MimeType', 't_Documents.Name')
                            ->get();
                    } catch (\Exception $e) {
                        $documents = collect([]);
                    }
                @endphp
                @if($documents->count() > 0)
                    <div class="row g-4 mt-3">
                        <div class="col-12">
                            <div class="border rounded-3 p-3">
                                <div class="text-muted small text-uppercase mb-2">Attachments</div>
                                @foreach($documents as $document)
                                    <div
                                        class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-alt text-muted me-2"></i>
                                            <span>{{ $document->Name }}</span>
                                        </div>
                                        <div>
                                            <a href="{{ route('legal.documents.show', $document->Id) }}"
                                               class="btn btn-sm btn-outline-primary me-2"
                                               target="_blank" title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('legal.documents.download', $document->Id) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row g-4 mt-3">
                        <div class="col-12">
                            <div class="border rounded-3 p-3">
                                <div class="text-muted small text-uppercase mb-2">Attachments</div>
                                <div class="text-muted">No attachments uploaded</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Audit Information -->
                <div class="row g-4 mt-3">
                    <div class="col-12">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small text-uppercase mb-2">Audit Information</div>
                            <div class="row small text-muted">
                                <div class="col-md-6">
                                    <div class="row mb-1">
                                        <div class="col-4">Created:</div>
                                        <div
                                            class="col-8">{{ $invoice->CreatedOn ? \Carbon\Carbon::parse($invoice->CreatedOn)->format('d M Y H:i') : '—' }}</div>
                                    </div>
                                    @if($invoice->creator)
                                        <div class="row mb-1">
                                            <div class="col-4">Created By:</div>
                                            <div
                                                class="col-8">{{ $invoice->creator->FirstName }} {{ $invoice->creator->LastName }}</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    @if($invoice->ModifiedOn)
                                        <div class="row mb-1">
                                            <div class="col-4">Modified:</div>
                                            <div
                                                class="col-8">{{ \Carbon\Carbon::parse($invoice->ModifiedOn)->format('d M Y H:i') }}</div>
                                        </div>
                                    @endif
                                    @if($invoice->modifier)
                                        <div class="row mb-1">
                                            <div class="col-4">Modified By:</div>
                                            <div
                                                class="col-8">{{ $invoice->modifier->FirstName }} {{ $invoice->modifier->LastName }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                @if($invoice->Status === 'Draft')
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-success btn-lg me-3" onclick="approveInvoice()">
                            <i class="fas fa-check me-2"></i> Approve Invoice
                        </button>
                        <button type="button" class="btn btn-danger btn-lg" onclick="rejectInvoice()">
                            <i class="fas fa-times me-2"></i> Reject Invoice
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function approveInvoice() {
            if (confirm('Are you sure you want to approve this invoice?')) {
                // Implement approval logic here
                // This would typically make an AJAX call to an approval endpoint
                alert('Invoice approval functionality would be implemented here');
            }
        }

        function rejectInvoice() {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason) {
                // Implement rejection logic here
                // This would typically make an AJAX call to a rejection endpoint
                alert('Invoice rejection functionality would be implemented here');
            }
        }
    </script>
@endsection

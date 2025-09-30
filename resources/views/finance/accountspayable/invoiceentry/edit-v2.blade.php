@extends('layouts.app')
@section('title','Edit Invoice Entry')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container my-3">
        <form action="{{ route('invoiceentry.update', $invoice->Id) }}" method="post" enctype="multipart/form-data" id="invoiceForm">
            @csrf
            @method('PUT')

            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-edit text-info me-2"></i> Edit Invoice Entry
                    </h6>
                    <a href="{{ route('invoiceentry.show', $invoice->Id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Invoice
                    </a>
                </div>

                <div class="card-body p-3">
                    <!-- Hidden fields -->
                    <input type="hidden" name="ThirdPartyID" value="{{ $invoice->ThirdPartyID }}">
                    <input type="hidden" name="SupplierID" value="{{ $invoice->SupplierID ?? '' }}">
                    <input type="hidden" name="POReference" value="{{ $invoice->POReference }}">
                    <input type="hidden" name="GRNReference" value="{{ $invoice->GRNReference }}">

                    <!-- Supplier Information (Read-only) -->
                    <div class="border rounded-3 p-3 mb-3 bg-light">
                        <h6 class="mb-3 text-muted">
                            <i class="fas fa-building text-info me-2"></i> Supplier Information (Read-only)
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>Supplier:</strong> {{ $invoice->thirdParty->TradingName ?? $invoice->thirdParty->ThirdPartyName ?? '—' }}
                                </div>
                                <div class="mb-2">
                                    <strong>PO Reference:</strong> {{ $invoice->POReference }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>GRN Reference:</strong> {{ $invoice->GRNReference }}
                                </div>
                                <div class="mb-2">
                                    <strong>Email:</strong> {{ $invoice->thirdParty->Email ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="border rounded-3 p-3 mb-3">
                        <h6 class="mb-3 text-muted">
                            <i class="fas fa-file-alt text-info me-2"></i> Invoice Details
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="InvoiceNumber" value="{{ old('InvoiceNumber', $invoice->InvoiceNumber) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="InvoiceDate" value="{{ old('InvoiceDate', $invoice->InvoiceDate ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('Y-m-d') : '') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="DueDate" value="{{ old('DueDate', $invoice->DueDate ? \Carbon\Carbon::parse($invoice->DueDate)->format('Y-m-d') : '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KSh</span>
                                    <input type="number" step="0.01" class="form-control" name="Amount" value="{{ old('Amount', $invoice->Amount) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Attachment</label>
                                <input type="file" class="form-control" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="form-text">PDF, DOC, DOCX, JPG, JPEG, PNG (max 10MB). Leave empty to keep existing attachment.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="Description" rows="3" placeholder="Optional invoice description...">{{ old('Description', $invoice->Description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Current Attachments -->
                    @if($invoice->documents && $invoice->documents->count() > 0)
                        <div class="border rounded-3 p-3 mb-3">
                            <h6 class="mb-3 text-muted">
                                <i class="fas fa-paperclip text-info me-2"></i> Current Attachments
                            </h6>
                            @foreach($invoice->documents as $document)
                                <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-alt text-muted me-2"></i>
                                        <span>{{ $document->DocumentName }}</span>
                                    </div>
                                    <div>
                                        <a href="{{ route('documents.preview', $document->Id) }}" 
                                           class="btn btn-sm btn-outline-primary me-2" 
                                           target="_blank" title="Preview">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('documents.download', $document->Id) }}" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Submit Section -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="btnSubmit">
                            <i class="fas fa-save me-2"></i> Update Invoice
                        </button>
                        <a href="{{ route('invoiceentry.show', $invoice->Id) }}" class="btn btn-secondary btn-lg px-5 ms-3">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const invoiceForm = document.getElementById('invoiceForm');

            // Form submission handling
            invoiceForm.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('btnSubmit');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Updating...';
            });
        });
    </script>
@endsection

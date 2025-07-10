@extends('layouts.app')
@section('title', 'Approve Invoice')
@section('content')

    <div class="container mt-4">
        <h4 class="mb-3">✅ Review Invoice: {{ $invoice->InvoiceNumber }}</h4>

        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Supplier:</strong> {{ $invoice->SupplierID }}</p>
                <p><strong>Invoice Date:</strong> {{ $invoice->InvoiceDate }}</p>
                <p><strong>Amount:</strong> {{ number_format($invoice->Amount, 2) }}</p>
                <p><strong>Source:</strong> {{ ucfirst($invoice->InvoiceSource) }}</p>
                <p>
                    <strong>Reference:</strong>
                    @if($invoice->InvoiceSource === 'goods')
                        GRN ID: {{ $invoice->GRNID }}
                    @elseif($invoice->InvoiceSource === 'service')
                        Service Cert ID: {{ $invoice->ServiceCertID }}
                    @else
                        Exception – Justification: <br>
                        <span class="text-danger">{{ $invoice->ExceptionJustification }}</span>
                    @endif
                </p>
                <p>
                    @if($invoice->InvoiceFilePath)
                        <strong>Attached Invoice:</strong>
                        <a href="{{ asset('storage/' . $invoice->InvoiceFilePath) }}" target="_blank"
                           class="btn btn-outline-secondary btn-sm">📎 View Invoice</a>
                    @endif
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('invoiceapproval.approve', $invoice->id) }}">
            @csrf

            <div class="mb-3">
                <label>Approval Action</label>
                <select name="approval_action" class="form-select" required>
                    <option value="">-- Choose --</option>
                    <option value="approved">✅ Approve</option>
                    <option value="rejected">❌ Reject</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Approval Notes (Optional)</label>
                <textarea name="approval_notes" class="form-control" rows="3"></textarea>
            </div>

            <button class="btn btn-success">Submit Decision</button>
        </form>
    </div>
@endsection

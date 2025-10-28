@extends('layouts.app')
@section('title','Invoice Entries')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-file-invoice text-info me-2"></i> Invoice Entries
                </h6>
                <a href="{{ route('invoiceentry.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> New Invoice
                </a>
            </div>

            <div class="card-body p-0">
                @if($invoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Invoice No.</th>
                                <th>Supplier</th>
                                <th>PO Reference</th>
                                <th>GRN Reference</th>
                                <th>Invoice Date</th>
                                <th>Due Date</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        <strong>{{ $invoice->InvoiceNumber }}</strong>
                                    </td>
                                    <td>
                                        {{ $invoice->thirdParty->TradingName ?? $invoice->thirdParty->ThirdPartyName ?? '—' }}
                                    </td>
                                    <td>{{ $invoice->POReference }}</td>
                                    <td>{{ $invoice->GRNReference }}</td>
                                    <td>{{ $invoice->InvoiceDate ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('d M Y') : '—' }}</td>
                                    <td>{{ $invoice->DueDate ? \Carbon\Carbon::parse($invoice->DueDate)->format('d M Y') : '—' }}</td>
                                    <td class="text-end">
                                        <strong>KSh {{ number_format($invoice->Amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($invoice->Status === 'Draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @elseif($invoice->Status === 'Approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($invoice->Status === 'Rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">{{ $invoice->Status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('invoiceentry.show', $invoice->Id) }}"
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($invoice->Status === 'Draft')
                                                <a href="{{ route('invoiceentry.edit', $invoice->Id) }}"
                                                   class="btn btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($invoices->hasPages())
                        <div class="p-3">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-file-invoice text-muted mb-3" style="font-size: 3rem;"></i>
                        <h5 class="text-muted">No invoices found</h5>
                        <p class="text-muted">Create your first invoice entry to get started.</p>
                        <a href="{{ route('invoiceentry.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Create Invoice
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

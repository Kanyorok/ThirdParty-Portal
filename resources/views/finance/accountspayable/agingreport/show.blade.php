@extends('layouts.app')
@section('title', 'Supplier Aging Detail')

@section('content')
    <div class="container my-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h5 class="mb-1">
                    <i class="fas fa-user-tie text-info me-2"></i> {{ $supplier->ThirdPartyName }}
                </h5>
                <div class="small text-muted">
                    Due dates between <strong>{{ $filters['from_date'] }}</strong> and <strong>{{ $filters['to_date'] }}</strong>
                </div>
                <div class="small text-muted">
                    {{ $supplier->Email ?? 'No email' }}
                    @if($supplier->Phone)
                        • {{ $supplier->Phone }}
                    @endif
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('agingreport.index', ['from_date' => $filters['from_date'], 'to_date' => $filters['to_date'], 'supplier_id' => $supplier->Id]) }}"
                   class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Report
                </a>
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Invoices</div>
                        <div class="h4 mb-0">{{ $invoices->count() }}</div>
                        <div class="small text-muted">Records in range</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Total Amount</div>
                        <div class="h4 mb-0">KSh {{ number_format($grandTotal, 2) }}</div>
                        <div class="small text-muted">All buckets</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">31–60 Days</div>
                        <div class="h4 mb-0">KSh {{ number_format($bucketTotals['31–60 Days'] ?? 0, 2) }}</div>
                        <div class="small text-muted">Overdue tier 1</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">90+ Days</div>
                        <div class="h4 mb-0 text-danger">KSh {{ number_format($bucketTotals['90+ Days'] ?? 0, 2) }}</div>
                        <div class="small text-muted">Critical exposure</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invoice #</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Bucket</th>
                            <th class="text-center">Days Past Due</th>
                            <th>Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $invoice->InvoiceNumber }}</td>
                                <td>{{ $invoice->invoice_date_formatted ?? '—' }}</td>
                                <td>{{ $invoice->due_date_formatted ?? '—' }}</td>
                                <td class="text-end">KSh {{ number_format($invoice->InvoiceAmount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $invoice->bucket === '90+ Days' ? 'bg-danger' : 'bg-secondary' }}">
                                        {{ $invoice->bucket }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $invoice->daysPastDue ?? '—' }}</td>
                                <td>{{ $invoice->Description ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No invoices fall within the selected parameters.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


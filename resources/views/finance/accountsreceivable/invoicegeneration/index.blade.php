@extends('layouts.app')
@section('title','Billing Requests')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-file-invoice text-info me-2"></i> Incoming Billing Requests
                </h6>
            </div>

            <div class="card-body p-3">
                <form action="{{ route('invoicegeneration.index') }}" method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Request No</label>
                        <input type="text" class="form-control form-control-sm" name="request_id" value="{{ request('request_id') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Invoice #</label>
                        <input type="text" class="form-control form-control-sm" name="invoice_number" value="{{ request('invoice_number') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Customer</label>
                        <input type="text" class="form-control form-control-sm" name="customer" value="{{ request('customer') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="all" {{ request('status')=='all' ? 'selected' : '' }}>All</option>
                            @foreach(['draft','pending','posted','rejected'] as $s)
                                <option value="{{ $s }}" {{ request('status')==$s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Due From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Due To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12 d-flex justify-content-end mt-2">
                        <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fas fa-filter me-1"></i> Filter</button>
                        <a href="{{ route('invoicegeneration.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle text-center">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Request No.</th>
                            <th>Title</th>
                            <th>Customer</th>
                            <th>Source</th>
                            <th>REF No.</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>
                                <td>{{ $invoice->RequestID }}</td>
                                <td>{{ $invoice->InvoiceTitle }}</td>
                                <td>{{ $invoice->customer->ThirdPartyName ?? '—' }}</td>
                                <td>{{ $invoice->source->Name ?? '—' }}</td>
                                <td>{{ $invoice->InvoiceNumber }}</td>
                                <td>
                                    {{ number_format($invoice->TotalAmount, 2) }}
                                    ({{ $invoice->currency->Code ?? '' }})
                                </td>
                                <td>{{ \Carbon\Carbon::parse($invoice->DueDate)->format('d-m-Y') }}</td>
                                <td>
                                    @php
                                        $statusClass = match($invoice->ApprovalStatus) {
                                            'posted' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'draft' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucfirst($invoice->ApprovalStatus) ?? 'Pending' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('invoicegeneration.show',$invoice->Id) }}"
                                       class="btn btn-sm btn-outline-info" title="View Billing">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>

                                            <i>No billing requests found.</i>
                                        </p>
                                        <a href="{{ route('invoicegeneration.index') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-refresh me-2"></i> Refresh Page
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $invoices->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

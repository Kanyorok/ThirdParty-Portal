@extends('layouts.app')

@section('title', 'Rent Invoices')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .action-buttons {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.4rem;
            align-items: center;
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('rentinvoice.create') }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-plus me-1"></i> New Invoice
        </a>
    </div>

    <p class="text-muted">
        <small>This table lists all rent invoices that have been raised, with their status and billing details.</small>
    </p>

    @if($invoices->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="InvoicesTable" class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th>Invoice No.</th>
                            <th>Tenant</th>
                            <th>Lease</th>
                            <th>Billing Period</th>
                            <th>Invoice Date</th>
                            <th class="text-end">Rent Amount</th>
                            <th>Status</th>
                            <th style="width: 20%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $invoice->InvoiceNumber ?? '-' }}</td>
                                <td>{{ $invoice->lease->tenant->thirdParty->ThirdPartyName ?? '-' }}</td>
                                <td>{{ $invoice->lease->LeaseNumber ?? '-' }}</td>
                                <td>{{ $invoice->BillingMonth ? \Carbon\Carbon::parse($invoice->BillingMonth)->format('m/Y') : '-' }}</td>
                                <td>{{ $invoice->InvoiceDate ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('d/m/Y') : '-' }}</td>
                                <td class="text-end">{{ number_format($invoice->RentAmount, 2) ?? '-' }}</td>
                                <td>
                                    @if($invoice->Status instanceof \App\Enums\Property\PropertyInvoiceEnum)
                                        <span class="badge bg-{{ $invoice->Status->badgeColor() }}">
                                            {{ $invoice->Status->label() }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ $invoice->Status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('rentinvoice.show', $invoice->Id) }}" 
                                           class="btn btn-sm btn-info text-white" 
                                           title="View Invoice">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($invoice->receipts()->exists())
                                            <button class="btn btn-sm btn-secondary" title="Invoice In Use">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        @else
                                            {{-- <a href="{{ route('rentinvoice.edit', $invoice->Id) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Edit Invoice">
                                                <i class="bi bi-pencil-square"></i>
                                            </a> --}}

                                            <form action="{{ route('rentinvoice.destroy', $invoice->Id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to delete this invoice?');" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" title="Delete Invoice">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i> No rent invoices have been registered yet.
        </div>
    @endif
</div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#InvoicesTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection

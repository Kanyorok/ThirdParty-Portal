@extends('layouts.app')
@section('title', 'Rent Invoices')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <a href="{{ route('rentinvoice.create') }}" class="btn btn-primary mb-3">New Invoice</a>
    <h4 class="fw-bold mb-3">Rent Invoices</h4>

    @if($invoices->count())
        <table class="table table-bordered table-striped align-middle" id="invoicesTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Invoice Number</th>
                    <th>Tenant</th>
                    <th>Lease</th>
                    <th>Billing Period</th>
                    <th>Invoice Date</th>
                    <th>Rent Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $invoice->InvoiceNumber ?? '-' }}</td>
                    <td>{{ $invoice->lease->tenant->TenantName ?? '-' }}</td>
                    <td>{{ $invoice->lease->LeaseNumber ?? '-' }}</td>

                    <td>
                        {{ $invoice->BillingMonth ? \Carbon\Carbon::parse($invoice->BillingMonth)->format('m/Y') : '-' }}
                    </td>

                    <td>
                        {{ $invoice->InvoiceDate ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('d/m/Y') : '-' }}
                    </td>

                    <td>{{ number_format($invoice->RentAmount, 2) ?? '-' }}</td>

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
                        <a href="{{ route('rentinvoice.show', $invoice->Id) }}" class="btn btn-sm btn-outline-primary">👁 View</a>
                        <a href="{{ route('rentinvoice.edit', $invoice->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('rentinvoice.destroy', $invoice->Id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this lease schedule?');">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p>No property invoices registered yet.</p>
    @endif
</div>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#invoicesTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
@endsection

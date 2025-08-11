@extends('layouts.app')
@section('title', 'Invoice Entry - Accounts Payable')

@section('content')
    <div class="container mt-2">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                <h6 class="mb-0 text-info" id="noteTypeTitle">
                    <i class="fab fa-wpforms me-2"></i>Invoice Entries List
                </h6>
                <a href="{{ route('invoiceentry.create') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-plus me-1"></i>Add Invoice
                </a>
            </div>

            <div class="card-body pt-3">
                <p class="text-muted mb-3">Below is the list of all saved invoices with their details.</p>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Vendor</th>
                            <th>Invoice Number</th>
                            <th>Invoice Date</th>
                            <th class="text-end">Amount (Ksh)</th>
                            <th>Description</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($invoices->count())
                            @foreach($invoices as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->suppliers->SupplierName ?? '-' }}</td>
                                    <td>{{ $item->InvoiceNumber ?? '-' }}</td>
                                    <td>{{ $item->InvoiceDate ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($item->InvoiceAmount, 2) }}</td>
                                    <td>{{ $item->Description ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="#" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger custom-delete-btn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            <i> No Invoice Entries found.</i>
                                        </p>
                                        <a href="{{ route('invoiceentry.create') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Add Invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')
@section('title', 'Invoice Entry - Accounts Payable')
@section('content')
<div class="container mt-3">
    <div class="mb-2">
        <a href="{{ route('invoiceentry.create') }}" class="btn btn-primary">➕Add Invoice</a>
    </div>
        <div class="card">
            <div class="card-body">
                <p class="text-muted mt-0">Below is the list of all saved invoices with their details.</p>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Vendor</th>
                                <th>Invoice Number</th>
                                <th>Invoice Date</th>
                                <th>Amount (Ksh)</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($invoices->count())
                            @forEach($invoices as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->suppliers->SupplierName ?? '-' }}</td>
                                <td>{{ $item->InvoiceNumber ?? '-' }}</td>
                                <td>{{ $item->InvoiceDate ?? '-'}}</td>
                                <td class="text-end">{{ number_format($item->InvoiceAmount, 2) ?? '-'}}</td>
                                <td>{{ $item->Description ?? '-'}}</td>
                                {{-- <td>{{ $item-> ?? '-'}}</td>
                                <td>{{ $item-> ?? '-'}}</td> --}}
                                <td style="white-space: nowrap;">
                                    <a href="{{ route('taxruleconfig.edit', $item->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        {{-- data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{$item->taxType->TaxTypeName}}"    {{-- Pass item name --}}
                                        {{-- data-route="{{ route('taxruleconfig.destroy', $item->Id) }}"> Pass delete route --}} >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <div class="text-center">
                                            No Invoice Entries found
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

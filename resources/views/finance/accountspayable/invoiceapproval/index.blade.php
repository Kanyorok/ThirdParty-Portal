@extends('layouts.app')
@section('title', 'Pending Invoice Approvals')
@section('content')

    <div class="container mt-4">
        <h4 class="mb-3">🧾 Pending Invoice Approvals</h4>

        <table class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Invoice No</th>
                <th>Supplier</th>
                <th>Source</th>
                <th>Amount</th>
                <th>Exception?</th>
                <th>File</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($invoices as $inv)
                <tr>
                    <td>{{ $inv->id }}</td>
                    <td>{{ $inv->InvoiceNumber }}</td>
                    <td>{{ $inv->SupplierID }}</td>
                    <td>{{ ucfirst($inv->InvoiceSource) }}</td>
                    <td>{{ number_format($inv->Amount, 2) }}</td>
                    <td>
                        @if($inv->InvoiceSource === 'exception')
                            ⚠️ Yes
                        @else
                            No
                        @endif
                    </td>
                    <td>
                        @if($inv->InvoiceFilePath)
                            <a href="{{ asset('storage/' . $inv->InvoiceFilePath) }}" target="_blank">📎</a>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('invoiceapproval.create', $inv->id) }}"
                           class="btn btn-sm btn-primary">Review</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

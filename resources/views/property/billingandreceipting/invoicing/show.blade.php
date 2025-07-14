@extends('layouts.app')
@section('title', 'Rent Invoice')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Property Invoice Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">

                <dt class="col-sm-4">Invoice Number</dt>
                    <dd class="col-sm-8">{{ $invoice->InvoiceNumber ?? '-' }}</dd>

                    <dt class="col-sm-4">Tenant ID</dt>
                    <dd class="col-sm-8">{{ $invoice->lease->tenant->TenantName ?? '-' }}</dd>

                    <dt class="col-sm-4">Lease</dt>
                    <dd class="col-sm-8">{{ $invoice->lease->LeaseNumber  ?? '-' }}</dd>

                    <dt class="col-sm-4">Billing Period</dt>
                    <dd class="col-sm-8">{{ $invoice->BillingMonth ?? '-' }}</dd>

                    <dt class="col-sm-4">Invoice date</dt>
                    <dd class="col-sm-8">{{ $invoice->InvoiceDate ?? '-' }}</dd>

                    <dt class="col-sm-4">Service charge</dt>
                    <dd class="col-sm-8">{{ $invoice->ServicesCharge ?? '-' }}</dd>

                    <dt class="col-sm-4">Invoice Notes</dt>
                    <dd class="col-sm-8">{{ $invoice->InvoiceNotes ?? '-' }}</dd>

                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('rentinvoice.edit', $invoice->Id) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('rentinvoice.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection

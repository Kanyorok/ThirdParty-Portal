@extends('layouts.app')
@section('title', 'Lease Renewal Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Lease Renewal Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Lease Number</dt>
                    <dd class="col-sm-8">{{ $leaserenewal->lease->LeaseNumber ?? '-' }}</dd>

                    <dt class="col-sm-4">Tenant Name</dt>
                    <dd class="col-sm-8">{{ $leaserenewal->tenant->TenantName ?? '-' }}</dd>

                    <dt class="col-sm-4">Property Leased</dt>
                    <dd class="col-sm-8">{{ $leaserenewal->property->PropertyName ?? '-' }}</dd>

                    <dt class="col-sm-4">Payment Frequency</dt>
                    <dd class="col-sm-8">{{ $leaserenewal->paymentFrequency->Description ?? '-' }}</dd>

                    <dt class="col-sm-4">End Date of Current Lease</dt>
                    <dd class="col-sm-8">{{ $leaserenewal->EndDateCurrentLease ? \Carbon\Carbon::parse($leaserenewal->EndDateCurrentLease)->format('d m Y') : '-' }}</dd>

                    <dt class="col-sm-4">New Start Date</dt>
                    <dd class="col-sm-8">{{ $leaserenewal->NewStartDate ? \Carbon\Carbon::parse($leaserenewal->NewStartDate)->format('d m Y') : '-' }}</dd>

                    <dt class="col-sm-4">New End Date</dt>
                    <dd class="col-sm-8">{{ $leaserenewal->NewEndDate ? \Carbon\Carbon::parse($leaserenewal->NewEndDate)->format('d m Y') : '-' }}</dd>

                    <dt class="col-sm-4">New Monthly Rent</dt>
                    <dd class="col-sm-8">{{ $leaserenewal->NewMonthlyRent ?? '-' }}</dd>

                    <dt class="col-sm-4">Payment Frequency</dt>
                    <dd class="col-sm-8">{{ $leaserenewal->PaymentFrequency ?? '-' }}</dd>

                    <dt class="col-sm-4">Remarks</dt>
                    <dd class="col-sm-8">{{ $leaserenewal->Remarks ?? '-' }}</dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-primary">Edit</a>
                <a href="{{ route('renewlease.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
